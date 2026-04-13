<?php

namespace App\Support\Households;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class RegistrationDocumentWatermarkService
{
    private const ORGANIZATION_NAME = 'ธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่';

    private const MAX_IMAGE_DIMENSION = 1920;

    public function buildWatermarkedPdf(UploadedFile $uploadedFile, array $context): array
    {
        $sourcePath = $uploadedFile->getRealPath();

        if (! is_string($sourcePath) || $sourcePath === '') {
            throw new RuntimeException('ไม่พบไฟล์ต้นฉบับสำหรับสร้างเอกสารที่คาดข้อความความปลอดภัย');
        }

        $pdf = new RegistrationWatermarkPdf;
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $this->registerWatermarkFont($pdf);

        if ($this->isPdfUpload($uploadedFile)) {
            $this->appendPdfPages($pdf, $sourcePath, $context);
        } else {
            $this->appendImagePage($pdf, $uploadedFile, $sourcePath, $context);
        }

        $pdfContent = $pdf->Output('S');

        if (! is_string($pdfContent) || $pdfContent === '') {
            throw new RuntimeException('ไม่สามารถสร้างไฟล์ PDF ที่คาดข้อความความปลอดภัยได้');
        }

        return [
            'content' => $pdfContent,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($pdfContent),
            'original_name' => $this->outputFileName($uploadedFile),
        ];
    }

    private function appendPdfPages(RegistrationWatermarkPdf $pdf, string $sourcePath, array $context): void
    {
        $pageCount = $pdf->setSourceFile($sourcePath);

        if ($pageCount < 1) {
            throw new RuntimeException('ไฟล์ PDF ที่อัปโหลดไม่มีหน้าที่สามารถประมวลผลได้');
        }

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $templateId = $pdf->importPage($pageNumber);
            $pageSize = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
            $pdf->useTemplate($templateId, 0, 0, $pageSize['width'], $pageSize['height']);

            $this->applyWatermark(
                $pdf,
                (float) $pageSize['width'],
                (float) $pageSize['height'],
                $context,
                $pageNumber
            );
        }
    }

    private function appendImagePage(
        RegistrationWatermarkPdf $pdf,
        UploadedFile $uploadedFile,
        string $sourcePath,
        array $context
    ): void {
        $imageSize = @getimagesize($sourcePath);

        if ($imageSize === false) {
            throw new RuntimeException('ไฟล์รูปภาพที่อัปโหลดไม่สามารถอ่านได้');
        }

        $preparedImage = $this->prepareImageForPdf($uploadedFile, $sourcePath, $imageSize);
        $imageWidth = $preparedImage['width'];
        $imageHeight = $preparedImage['height'];

        $orientation = $imageWidth > $imageHeight ? 'L' : 'P';
        $pdf->AddPage($orientation, 'A4');

        $pageWidth = $pdf->GetPageWidth();
        $pageHeight = $pdf->GetPageHeight();
        $margin = 12.0;
        $maxWidth = $pageWidth - ($margin * 2);
        $maxHeight = $pageHeight - ($margin * 2);
        $scale = min($maxWidth / $imageWidth, $maxHeight / $imageHeight);
        $renderWidth = max(1.0, $imageWidth * $scale);
        $renderHeight = max(1.0, $imageHeight * $scale);
        $offsetX = ($pageWidth - $renderWidth) / 2;
        $offsetY = ($pageHeight - $renderHeight) / 2;

        try {
            $pdf->Image(
                $preparedImage['path'],
                $offsetX,
                $offsetY,
                $renderWidth,
                $renderHeight,
                $preparedImage['type']
            );
        } finally {
            if ($preparedImage['cleanup']) {
                @unlink($preparedImage['path']);
            }
        }

        $this->applyWatermark($pdf, $pageWidth, $pageHeight, $context, 1);
    }

    private function applyWatermark(
        RegistrationWatermarkPdf $pdf,
        float $pageWidth,
        float $pageHeight,
        array $context,
        int $pageNumber
    ): void {
        $primaryMessage = 'ใช้สำหรับสมัครสมาชิก'.self::ORGANIZATION_NAME.'เท่านั้น';
        $secondaryMessage = 'ห้ามใช้เพื่อยืนยันตัวตน เปิดบัญชี ขอสินเชื่อ สมัครบริการ หรือทำธุรกรรมอื่น';
        $contextMessage = $this->watermarkContextLine($context, $pageNumber);
        $watermarkStep = max(60.0, min(88.0, $pageHeight / 3.2));

        for ($y = 44.0; $y <= $pageHeight + 26.0; $y += $watermarkStep) {
            $pdf->SetTextColor(196, 205, 214);
            $pdf->SetFont('thsarabunnew', '', 24);
            $pdf->rotatedText(10.0, $y, $primaryMessage, 28.0);

            $pdf->SetTextColor(208, 215, 223);
            $pdf->SetFont('thsarabunnew', '', 18);
            $pdf->rotatedText(18.0, $y + 12.0, $secondaryMessage, 28.0);

            $pdf->SetTextColor(216, 222, 228);
            $pdf->SetFont('thsarabunnew', '', 16);
            $pdf->rotatedText(26.0, $y + 23.0, $contextMessage, 28.0);
        }
    }

    private function watermarkContextLine(array $context, int $pageNumber): string
    {
        $accountNo = trim((string) ($context['account_no'] ?? ''));
        $memberName = trim((string) ($context['member_display_name'] ?? ''));
        $documentLabel = trim((string) ($context['document_label'] ?? ''));

        return collect([
            $accountNo !== '' ? 'เลขบัญชี '.$accountNo : null,
            $memberName !== '' ? 'สมาชิก '.$memberName : null,
            $documentLabel !== '' ? 'เอกสาร '.$documentLabel : null,
            'หน้า '.$pageNumber,
        ])->filter()->implode(' | ');
    }

    private function outputFileName(UploadedFile $uploadedFile): string
    {
        $baseName = trim(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));

        if ($baseName === '') {
            $baseName = 'registration-document';
        }

        return $baseName.'.pdf';
    }

    private function isPdfUpload(UploadedFile $uploadedFile): bool
    {
        $extension = strtolower((string) ($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension() ?: ''));
        $mimeType = strtolower((string) ($uploadedFile->getMimeType() ?: $uploadedFile->getClientMimeType() ?: ''));

        return $extension === 'pdf' || str_contains($mimeType, 'pdf');
    }

    private function imageType(UploadedFile $uploadedFile, mixed $mimeType): string
    {
        $resolvedMimeType = strtolower((string) $mimeType);

        if (str_contains($resolvedMimeType, 'png')) {
            return 'PNG';
        }

        if (str_contains($resolvedMimeType, 'jpeg') || str_contains($resolvedMimeType, 'jpg')) {
            return 'JPEG';
        }

        $extension = strtolower((string) ($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension() ?: ''));

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            return 'JPEG';
        }

        if ($extension === 'png') {
            return 'PNG';
        }

        return str_contains($resolvedMimeType, 'png') ? 'PNG' : 'JPEG';
    }

    private function prepareImageForPdf(UploadedFile $uploadedFile, string $sourcePath, array $imageSize): array
    {
        $normalizedImage = $this->normalizeImageForPdf($sourcePath);

        if ($normalizedImage !== null) {
            return $normalizedImage;
        }

        return [
            'path' => $sourcePath,
            'type' => $this->imageType($uploadedFile, $imageSize['mime'] ?? null),
            'width' => (int) ($imageSize[0] ?? 0),
            'height' => (int) ($imageSize[1] ?? 0),
            'cleanup' => false,
        ];
    }

    private function normalizeImageForPdf(string $sourcePath): ?array
    {
        if (class_exists(\Imagick::class)) {
            try {
                return $this->normalizeImageWithImagick($sourcePath);
            } catch (Throwable) {
                // Fall back to GD/original image handling when Imagick rejects a readable image.
            }
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            try {
                return $this->normalizeImageWithGd($sourcePath);
            } catch (Throwable) {
                // Fall back to the original image handling if runtime image libraries cannot normalize it.
            }
        }

        return null;
    }

    private function normalizeImageWithImagick(string $sourcePath): array
    {
        $image = new \Imagick;
        $image->readImage($sourcePath);

        if (method_exists($image, 'autoOrient')) {
            $image->autoOrient();
        }

        if ($image->getImageWidth() > self::MAX_IMAGE_DIMENSION || $image->getImageHeight() > self::MAX_IMAGE_DIMENSION) {
            $image->scaleImage(self::MAX_IMAGE_DIMENSION, self::MAX_IMAGE_DIMENSION, true);
        }

        $flattenedImage = new \Imagick;
        $flattenedImage->newImage($image->getImageWidth(), $image->getImageHeight(), 'white');
        $flattenedImage->setImageFormat('jpeg');
        $flattenedImage->compositeImage($image, \Imagick::COMPOSITE_OVER, 0, 0);
        $flattenedImage->stripImage();
        $flattenedImage->setImageCompressionQuality(85);

        $image->clear();
        $image->destroy();

        $temporaryPath = $this->writeTemporaryImage($flattenedImage->getImagesBlob(), 'jpg');
        [$imageWidth, $imageHeight] = $this->preparedImageSize($temporaryPath);

        $flattenedImage->clear();
        $flattenedImage->destroy();

        return [
            'path' => $temporaryPath,
            'type' => 'JPEG',
            'width' => $imageWidth,
            'height' => $imageHeight,
            'cleanup' => true,
        ];
    }

    private function normalizeImageWithGd(string $sourcePath): array
    {
        $sourceContent = @file_get_contents($sourcePath);

        if (! is_string($sourceContent) || $sourceContent === '') {
            throw new RuntimeException('ไม่สามารถอ่านไฟล์รูปภาพที่อัปโหลดเพื่อจัดรูปแบบก่อนคาดข้อความความปลอดภัยได้');
        }

        $sourceImage = @imagecreatefromstring($sourceContent);

        if ($sourceImage === false) {
            throw new RuntimeException('ไม่สามารถแปลงไฟล์รูปภาพที่อัปโหลดเพื่อจัดรูปแบบก่อนคาดข้อความความปลอดภัยได้');
        }

        $sourceImage = $this->applyExifOrientation($sourcePath, $sourceImage);

        $imageWidth = imagesx($sourceImage);
        $imageHeight = imagesy($sourceImage);

        if ($imageWidth > self::MAX_IMAGE_DIMENSION || $imageHeight > self::MAX_IMAGE_DIMENSION) {
            $scale = min(self::MAX_IMAGE_DIMENSION / $imageWidth, self::MAX_IMAGE_DIMENSION / $imageHeight);
            $newWidth = (int) round($imageWidth * $scale);
            $newHeight = (int) round($imageHeight * $scale);
            $resized = imagescale($sourceImage, $newWidth, $newHeight);
            if ($resized !== false) {
                imagedestroy($sourceImage);
                $sourceImage = $resized;
                $imageWidth = $newWidth;
                $imageHeight = $newHeight;
            }
        }

        $flattenedImage = imagecreatetruecolor($imageWidth, $imageHeight);

        if ($flattenedImage === false) {
            imagedestroy($sourceImage);

            throw new RuntimeException('ไม่สามารถสร้างพื้นหลังสำหรับจัดรูปแบบไฟล์รูปภาพก่อนคาดข้อความความปลอดภัยได้');
        }

        $white = imagecolorallocate($flattenedImage, 255, 255, 255);
        imagefill($flattenedImage, 0, 0, $white);
        imagecopy($flattenedImage, $sourceImage, 0, 0, 0, 0, $imageWidth, $imageHeight);

        ob_start();
        imagejpeg($flattenedImage, null, 85);
        $jpegContent = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($flattenedImage);

        if (! is_string($jpegContent) || $jpegContent === '') {
            throw new RuntimeException('ไม่สามารถจัดรูปแบบไฟล์รูปภาพเป็น JPEG ก่อนคาดข้อความความปลอดภัยได้');
        }

        $temporaryPath = $this->writeTemporaryImage($jpegContent, 'jpg');
        [$preparedWidth, $preparedHeight] = $this->preparedImageSize($temporaryPath);

        return [
            'path' => $temporaryPath,
            'type' => 'JPEG',
            'width' => $preparedWidth,
            'height' => $preparedHeight,
            'cleanup' => true,
        ];
    }

    private function applyExifOrientation(string $sourcePath, mixed $sourceImage): mixed
    {
        if (! function_exists('exif_read_data')) {
            return $sourceImage;
        }

        $exif = @exif_read_data($sourcePath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($sourceImage, 180, 0) ?: $sourceImage,
            6 => imagerotate($sourceImage, -90, 0) ?: $sourceImage,
            8 => imagerotate($sourceImage, 90, 0) ?: $sourceImage,
            default => $sourceImage,
        };
    }

    private function writeTemporaryImage(string $content, string $extension): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'registration-doc-');

        if (! is_string($temporaryPath) || $temporaryPath === '') {
            throw new RuntimeException('ไม่สามารถสร้างไฟล์ชั่วคราวสำหรับจัดรูปแบบเอกสารได้');
        }

        $targetPath = $temporaryPath.'.'.$extension;

        if (! @rename($temporaryPath, $targetPath)) {
            $targetPath = $temporaryPath;
        }

        if (@file_put_contents($targetPath, $content) === false) {
            @unlink($targetPath);

            throw new RuntimeException('ไม่สามารถเขียนไฟล์ชั่วคราวสำหรับจัดรูปแบบเอกสารได้');
        }

        return $targetPath;
    }

    private function preparedImageSize(string $path): array
    {
        $imageSize = @getimagesize($path);

        if ($imageSize === false) {
            throw new RuntimeException('ไม่สามารถอ่านขนาดของไฟล์รูปภาพที่จัดรูปแบบแล้วได้');
        }

        return [
            (int) ($imageSize[0] ?? 0),
            (int) ($imageSize[1] ?? 0),
        ];
    }

    private function registerWatermarkFont(RegistrationWatermarkPdf $pdf): void
    {
        $fontDirectory = storage_path('fonts');
        $fontPath = $fontDirectory.DIRECTORY_SEPARATOR.'THSarabunNew.ttf';

        if (! is_file($fontPath)) {
            throw new RuntimeException('ไม่พบฟอนต์ THSarabunNew สำหรับคาดข้อความความปลอดภัยบนเอกสาร');
        }

        $runtimeFontFile = $this->runtimeWatermarkFontFile($fontDirectory);
        $runtimeDirectory = is_writable($fontDirectory) ? $fontDirectory : sys_get_temp_dir();
        $runtimeFontPath = $runtimeDirectory.DIRECTORY_SEPARATOR.$runtimeFontFile;

        if (! is_file($runtimeFontPath) || @filesize($runtimeFontPath) !== @filesize($fontPath)) {
            if (! @copy($fontPath, $runtimeFontPath)) {
                throw new RuntimeException('ไม่สามารถเตรียมฟอนต์สำหรับคาดข้อความความปลอดภัยบนเอกสารได้');
            }
        }

        if (! defined('_SYSTEM_TTFONTS')) {
            define('_SYSTEM_TTFONTS', rtrim($runtimeDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
        }

        $pdf->AddFont('thsarabunnew', '', $runtimeFontFile, true);
    }

    private function runtimeWatermarkFontFile(string $fontDirectory): string
    {
        $environmentHash = substr(md5($fontDirectory), 0, 12);

        return 'THSarabunNew-'.$environmentHash.'.ttf';
    }
}
