<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogger;
use App\Support\Backup\BackupStorage;
use App\Support\Backup\DatabaseDumper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    private const MAX_BACKUPS = 30;

    public function __construct(
        private readonly BackupStorage $backupStorage,
        private readonly DatabaseDumper $databaseDumper,
    ) {}

    public function index()
    {
        $backups = $this->backupStorage->list()
            ->sortByDesc('modified')
            ->map(fn ($backup) => [
                'name' => $backup['name'],
                'size' => $this->formatBytes((int) $backup['size']),
                'created_at' => date('d/m/Y H:i:s', (int) $backup['modified']),
                'timestamp' => (int) $backup['modified'],
            ])
            ->values();

        return view('admin.backup.index', compact('backups'));
    }

    public function store(): RedirectResponse
    {
        @set_time_limit(0);

        try {
            $sql = $this->databaseDumper->dump();
        } catch (\Throwable $e) {
            return back()->with('error', 'ไม่สามารถสร้าง backup ได้: '.$e->getMessage());
        }

        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sql.gz';

        try {
            $this->backupStorage->put($filename, gzencode($sql, 6));
        } catch (\Throwable $e) {
            return back()->with('error', 'ไม่สามารถบันทึกไฟล์ backup ได้: '.$e->getMessage());
        }

        $this->pruneOldBackups();

        ActivityLogger::forCurrentUser('backup', "สร้าง backup ฐานข้อมูล: {$filename}", [
            'entity_type' => 'backup',
            'entity_id' => $filename,
            'after' => ['filename' => $filename, 'size' => strlen($sql)],
        ]);

        return redirect()->route('admin.backup.index')
            ->with('success', "สร้าง backup สำเร็จ: {$filename}");
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:gz', 'max:102400'],
        ], [
            'backup_file.required' => 'กรุณาเลือกไฟล์ backup',
            'backup_file.mimes' => 'รองรับเฉพาะไฟล์ .gz เท่านั้น',
            'backup_file.max' => 'ไฟล์ใหญ่เกิน 100 MB',
        ]);

        @set_time_limit(0);

        $file = $request->file('backup_file');
        $originalName = $file->getClientOriginalName();

        $sql = gzdecode((string) file_get_contents($file->getRealPath()));

        if ($sql === false) {
            return back()->withErrors('ไม่สามารถแตกไฟล์ได้ — ไฟล์อาจเสียหายหรือไม่ใช่ .sql.gz');
        }

        try {
            $this->databaseDumper->restore($sql);
        } catch (\Throwable $e) {
            return back()->withErrors('Restore ไม่สำเร็จ: '.$e->getMessage());
        }

        ActivityLogger::forCurrentUser('backup', "Restore ฐานข้อมูลจากไฟล์: {$originalName}", [
            'entity_type' => 'backup',
            'entity_id' => $originalName,
        ]);

        return redirect()->route('admin.backup.index')
            ->with('success', "Restore สำเร็จจากไฟล์: {$originalName}");
    }

    public function download(string $filename): StreamedResponse
    {
        $filename = basename($filename);
        $backup = $this->backupStorage->findByName($filename);

        abort_unless($backup !== null, 404);

        ActivityLogger::forCurrentUser('backup', "ดาวน์โหลด backup: {$filename}", [
            'entity_type' => 'backup',
            'entity_id' => $filename,
        ]);

        return $this->backupStorage->download($backup['reference'], $filename);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $backup = $this->backupStorage->findByName($filename);

        abort_unless($backup !== null, 404);

        $this->backupStorage->delete($backup['reference']);

        ActivityLogger::forCurrentUser('backup', "ลบ backup: {$filename}", [
            'entity_type' => 'backup',
            'entity_id' => $filename,
        ]);

        return redirect()->route('admin.backup.index')
            ->with('success', "ลบ backup สำเร็จ: {$filename}");
    }

    private function pruneOldBackups(): void
    {
        $backups = $this->backupStorage->list()->sortByDesc('modified')->values();

        $backups->slice(self::MAX_BACKUPS)->each(
            fn ($backup) => $this->backupStorage->delete($backup['reference'])
        );
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }
}
