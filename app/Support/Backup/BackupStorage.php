<?php

namespace App\Support\Backup;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupStorage
{
    private const VERCEL_BLOB_API = 'https://blob.vercel-storage.com';

    private const VERCEL_BLOB_API_VERSION = '11';

    private const PREFIX = 'backups/';

    public function __construct(
        private readonly string $driver,
        private readonly string $localDisk,
        private readonly ?string $blobToken,
    ) {}

    public function put(string $filename, string $contents): string
    {
        $pathname = self::PREFIX.$filename;

        if ($this->driver === 'vercel_blob') {
            return $this->putToVercelBlob($pathname, $contents);
        }

        Storage::disk($this->localDisk)->put($pathname, $contents);

        return $pathname;
    }

    public function list(): Collection
    {
        if ($this->driver === 'vercel_blob') {
            return $this->listFromVercelBlob();
        }

        return collect(Storage::disk($this->localDisk)->files(rtrim(self::PREFIX, '/')))
            ->filter(fn ($file) => str_ends_with($file, '.sql.gz'))
            ->map(fn ($file) => [
                'name' => basename($file),
                'reference' => $file,
                'size' => Storage::disk($this->localDisk)->size($file),
                'modified' => Storage::disk($this->localDisk)->lastModified($file),
            ])
            ->values();
    }

    public function get(string $reference): string
    {
        if ($this->driver === 'vercel_blob') {
            $response = Http::withToken($this->token())->get($reference);

            if (! $response->successful()) {
                abort(404);
            }

            return $response->body();
        }

        return (string) Storage::disk($this->localDisk)->get($reference);
    }

    public function download(string $reference, string $downloadName): StreamedResponse
    {
        if ($this->driver === 'vercel_blob') {
            $body = $this->get($reference);
            $encodedName = rawurlencode($downloadName);

            return new StreamedResponse(function () use ($body) {
                echo $body;
            }, 200, [
                'Content-Type' => 'application/gzip',
                'Content-Disposition' => sprintf('attachment; filename="%s"; filename*=UTF-8\'\'%s', $downloadName, $encodedName),
            ]);
        }

        return Storage::disk($this->localDisk)->download($reference, $downloadName);
    }

    public function delete(string $reference): void
    {
        if ($this->driver === 'vercel_blob') {
            Http::withToken($this->token())
                ->withHeaders(['x-api-version' => self::VERCEL_BLOB_API_VERSION])
                ->post(self::VERCEL_BLOB_API.'/delete', ['urls' => [$reference]]);

            return;
        }

        Storage::disk($this->localDisk)->delete($reference);
    }

    public function findByName(string $filename): ?array
    {
        return $this->list()->first(fn ($item) => $item['name'] === $filename);
    }

    private function putToVercelBlob(string $pathname, string $contents): string
    {
        $uploadUrl = self::VERCEL_BLOB_API.'/?'.http_build_query(['pathname' => ltrim($pathname, '/')]);

        $response = Http::withToken($this->token())
            ->withBody($contents, 'application/gzip')
            ->withHeaders([
                'x-api-version' => self::VERCEL_BLOB_API_VERSION,
                'x-content-type' => 'application/gzip',
                'x-add-random-suffix' => '0',
                'x-vercel-blob-access' => 'private',
            ])
            ->put($uploadUrl);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new \RuntimeException('อัปโหลด backup ไปยัง Vercel Blob ไม่สำเร็จ: '.$response->body(), previous: $e);
        }

        $url = (string) ($response->json('url') ?? '');

        if ($url === '') {
            throw new \RuntimeException('Vercel Blob ไม่ได้คืนค่า URL ของ backup ที่อัปโหลด');
        }

        return $url;
    }

    private function listFromVercelBlob(): Collection
    {
        $response = Http::withToken($this->token())
            ->withHeaders(['x-api-version' => self::VERCEL_BLOB_API_VERSION])
            ->get(self::VERCEL_BLOB_API, ['prefix' => self::PREFIX, 'limit' => 1000]);

        if (! $response->successful()) {
            return collect();
        }

        return collect($response->json('blobs') ?? [])
            ->filter(fn ($blob) => str_ends_with((string) ($blob['pathname'] ?? ''), '.sql.gz'))
            ->map(fn ($blob) => [
                'name' => basename((string) $blob['pathname']),
                'reference' => (string) $blob['url'],
                'size' => (int) ($blob['size'] ?? 0),
                'modified' => isset($blob['uploadedAt']) ? strtotime((string) $blob['uploadedAt']) : time(),
            ])
            ->values();
    }

    private function token(): string
    {
        if ($this->blobToken === null || $this->blobToken === '') {
            throw new \RuntimeException('BLOB_READ_WRITE_TOKEN ไม่ได้ตั้งค่าสำหรับ driver vercel_blob');
        }

        return $this->blobToken;
    }
}
