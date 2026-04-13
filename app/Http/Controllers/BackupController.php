<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private const MAX_BACKUPS = 30;

    public function index()
    {
        $files = Storage::disk('local')->files('backups');

        $backups = collect($files)
            ->filter(fn ($file) => str_ends_with($file, '.sql.gz'))
            ->map(function ($file) {
                $name = basename($file);
                $size = Storage::disk('local')->size($file);
                $modified = Storage::disk('local')->lastModified($file);

                return [
                    'name' => $name,
                    'size' => $this->formatBytes($size),
                    'created_at' => date('d/m/Y H:i:s', $modified),
                    'timestamp' => $modified,
                ];
            })
            ->sortByDesc('timestamp')
            ->values();

        return view('admin.backup.index', compact('backups'));
    }

    public function store(): RedirectResponse
    {
        $config = config('database.connections.mysql');
        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sql.gz';

        $process = new Process([
            'mysqldump',
            '--host='.$config['host'],
            '--port='.(string) $config['port'],
            '--user='.$config['username'],
            $config['database'],
        ]);

        $process->setEnv(['MYSQL_PWD' => $config['password']]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', 'ไม่สามารถสร้าง backup ได้: '.$process->getErrorOutput());
        }

        Storage::disk('local')->makeDirectory('backups');
        Storage::disk('local')->put('backups/'.$filename, gzencode($process->getOutput(), 9));

        $this->pruneOldBackups();

        ActivityLogger::forCurrentUser('backup', "สร้าง backup ฐานข้อมูล: {$filename}", [
            'entity_type' => 'backup',
            'entity_id' => $filename,
            'after' => ['filename' => $filename, 'size' => Storage::disk('local')->size('backups/'.$filename)],
        ]);

        return redirect()->route('admin.backup.index')
            ->with('success', "สร้าง backup สำเร็จ: {$filename}");
    }

    private function pruneOldBackups(): void
    {
        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($f) => str_ends_with($f, '.sql.gz'))
            ->map(fn ($f) => ['path' => $f, 'modified' => Storage::disk('local')->lastModified($f)])
            ->sortByDesc('modified')
            ->values();

        $files->slice(self::MAX_BACKUPS)->each(
            fn ($f) => Storage::disk('local')->delete($f['path'])
        );
    }

    public function restore(\Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:gz', 'max:102400'],
        ], [
            'backup_file.required' => 'กรุณาเลือกไฟล์ backup',
            'backup_file.mimes' => 'รองรับเฉพาะไฟล์ .gz เท่านั้น',
            'backup_file.max' => 'ไฟล์ใหญ่เกิน 100 MB',
        ]);

        $file = $request->file('backup_file');
        $originalName = $file->getClientOriginalName();

        $compressed = file_get_contents($file->getRealPath());
        $sql = gzdecode($compressed);

        if ($sql === false) {
            return back()->withErrors('ไม่สามารถแตกไฟล์ได้ — ไฟล์อาจเสียหายหรือไม่ใช่ .sql.gz');
        }

        $config = config('database.connections.mysql');

        $process = new Process([
            'mysql',
            '--host='.$config['host'],
            '--port='.(string) $config['port'],
            '--user='.$config['username'],
            $config['database'],
        ]);

        $process->setEnv(['MYSQL_PWD' => $config['password']]);
        $process->setInput($sql);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->withErrors('Restore ไม่สำเร็จ: '.$process->getErrorOutput());
        }

        ActivityLogger::forCurrentUser('backup', "Restore ฐานข้อมูลจากไฟล์: {$originalName}", [
            'entity_type' => 'backup',
            'entity_id' => $originalName,
        ]);

        return redirect()->route('admin.backup.index')
            ->with('success', "Restore สำเร็จจากไฟล์: {$originalName}");
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = basename($filename);
        $path = 'backups/'.$filename;

        abort_unless(Storage::disk('local')->exists($path), 404);

        ActivityLogger::forCurrentUser('backup', "ดาวน์โหลด backup: {$filename}", [
            'entity_type' => 'backup',
            'entity_id' => $filename,
        ]);

        return Storage::disk('local')->download($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $path = 'backups/'.$filename;

        abort_unless(Storage::disk('local')->exists($path), 404);

        Storage::disk('local')->delete($path);

        ActivityLogger::forCurrentUser('backup', "ลบ backup: {$filename}", [
            'entity_type' => 'backup',
            'entity_id' => $filename,
        ]);

        return redirect()->route('admin.backup.index')
            ->with('success', "ลบ backup สำเร็จ: {$filename}");
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
