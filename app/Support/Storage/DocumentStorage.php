<?php

namespace App\Support\Storage;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorage
{
    private const VERCEL_BLOB_API = 'https://blob.vercel-storage.com';

    private const VERCEL_BLOB_API_VERSION = '11';

    public function __construct(
        private readonly string $driver,
        private readonly string $localDisk,
        private readonly ?string $blobToken,
    ) {}

    public function put(string $path, string $contents, ?string $mimeType = null): string
    {
        if ($this->driver === 'vercel_blob') {
            return $this->putToVercelBlob($path, $contents, $mimeType);
        }

        if (! Storage::disk($this->localDisk)->put($path, $contents)) {
            throw new \RuntimeException('ไม่สามารถบันทึกไฟล์เอกสารได้');
        }

        return $path;
    }

    public function exists(string $storedPath): bool
    {
        if ($this->driver === 'vercel_blob') {
            return $this->isVercelBlobUrl($storedPath);
        }

        return Storage::disk($this->localDisk)->exists($storedPath);
    }

    public function delete(string|array $storedPaths): void
    {
        $paths = is_array($storedPaths) ? $storedPaths : [$storedPaths];
        $paths = array_values(array_filter($paths, fn ($path) => is_string($path) && $path !== ''));

        if ($paths === []) {
            return;
        }

        if ($this->driver === 'vercel_blob') {
            $this->deleteFromVercelBlob($paths);

            return;
        }

        Storage::disk($this->localDisk)->delete($paths);
    }

    public function response(string $storedPath, ?string $downloadName = null, array $headers = [], bool $forceDownload = false): StreamedResponse
    {
        $disposition = $forceDownload ? 'attachment' : 'inline';
        $downloadName ??= basename($storedPath);

        if ($this->driver === 'vercel_blob') {
            return $this->streamFromVercelBlob($storedPath, $downloadName, $disposition, $headers);
        }

        return $forceDownload
            ? Storage::disk($this->localDisk)->download($storedPath, $downloadName, $headers)
            : Storage::disk($this->localDisk)->response($storedPath, $downloadName, $headers);
    }

    private function putToVercelBlob(string $path, string $contents, ?string $mimeType): string
    {
        $response = Http::withToken($this->token())
            ->withBody($contents, $mimeType ?: 'application/octet-stream')
            ->withHeaders([
                'x-api-version' => self::VERCEL_BLOB_API_VERSION,
                'x-content-type' => $mimeType ?: 'application/octet-stream',
                'x-add-random-suffix' => '0',
                'x-vercel-blob-access' => 'private',
            ])
            ->put(self::VERCEL_BLOB_API.'/'.ltrim($path, '/'));

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new \RuntimeException('อัปโหลดไฟล์ไปยัง Vercel Blob ไม่สำเร็จ: '.$response->body(), previous: $e);
        }

        $url = (string) ($response->json('url') ?? '');

        if ($url === '') {
            throw new \RuntimeException('Vercel Blob ไม่ได้คืนค่า URL ของไฟล์ที่อัปโหลด');
        }

        return $url;
    }

    private function deleteFromVercelBlob(array $urls): void
    {
        $urls = array_values(array_filter($urls, fn ($url) => $this->isVercelBlobUrl($url)));

        if ($urls === []) {
            return;
        }

        Http::withToken($this->token())
            ->withHeaders(['x-api-version' => self::VERCEL_BLOB_API_VERSION])
            ->post(self::VERCEL_BLOB_API.'/delete', ['urls' => $urls]);
    }

    private function streamFromVercelBlob(string $url, string $downloadName, string $disposition, array $headers): StreamedResponse
    {
        $response = Http::withToken($this->token())->get($url);

        if (! $response->successful()) {
            abort(404);
        }

        $contentType = $headers['Content-Type']
            ?? $response->header('Content-Type')
            ?: 'application/octet-stream';

        $encodedName = rawurlencode($downloadName);

        $headers = array_merge($headers, [
            'Content-Type' => $contentType,
            'Content-Disposition' => sprintf('%s; filename="%s"; filename*=UTF-8\'\'%s', $disposition, $downloadName, $encodedName),
        ]);

        return new StreamedResponse(function () use ($response) {
            echo $response->body();
        }, 200, $headers);
    }

    private function isVercelBlobUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    private function token(): string
    {
        if ($this->blobToken === null || $this->blobToken === '') {
            throw new \RuntimeException('BLOB_READ_WRITE_TOKEN ไม่ได้ตั้งค่าสำหรับ driver vercel_blob');
        }

        return $this->blobToken;
    }
}
