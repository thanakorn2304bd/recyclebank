<?php

namespace App\Support\Households;

use setasign\Fpdi\Tfpdf\Fpdi;

class RegistrationWatermarkPdf extends Fpdi
{
    private float $rotationAngle = 0.0;

    public function rotate(float $angle, float $x = -1, float $y = -1): void
    {
        if ($x < 0) {
            $x = $this->x;
        }

        if ($y < 0) {
            $y = $this->y;
        }

        if ($this->rotationAngle !== 0.0) {
            $this->_out('Q');
        }

        $this->rotationAngle = $angle;

        if ($angle === 0.0) {
            return;
        }

        $angleInRadians = deg2rad($angle);
        $cosine = cos($angleInRadians);
        $sine = sin($angleInRadians);
        $centerX = $x * $this->k;
        $centerY = ($this->h - $y) * $this->k;

        $this->_out(sprintf(
            'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
            $cosine,
            $sine,
            -$sine,
            $cosine,
            $centerX,
            $centerY,
            -$centerX,
            -$centerY
        ));
    }

    public function rotatedText(float $x, float $y, string $text, float $angle): void
    {
        $this->rotate($angle, $x, $y);
        $this->Text($x, $y, $text);
        $this->rotate(0.0);
    }

    protected function _endpage(): void
    {
        if ($this->rotationAngle !== 0.0) {
            $this->rotationAngle = 0.0;
            $this->_out('Q');
        }

        parent::_endpage();
    }
}
