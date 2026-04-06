<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;
use setasign\Fpdi\Tfpdf\Fpdi;

trait CreatesRegistrationDocumentUploads
{
    protected function fakeRegistrationPng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $this->tinyPngContent());
    }

    protected function fakeRegistrationPdf(string $name, string $label): UploadedFile
    {
        $pdf = new Fpdi;
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 14);
        $pdf->Text(20, 20, $label);

        return UploadedFile::fake()->createWithContent($name, $pdf->Output('S'));
    }

    private function tinyPngContent(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+X2lQAAAAASUVORK5CYII=',
            true
        ) ?: throw new \RuntimeException('ไม่สามารถสร้างไฟล์ PNG สำหรับทดสอบได้');
    }
}
