<?php

namespace App\Support;

class ThaiBaht
{
    public static function text(float $amount): string
    {
        $amount = round($amount, 2);
        $baht = (int) floor($amount);
        $satang = (int) round(($amount - $baht) * 100);

        $bahtText = self::readNumber($baht) . 'บาท';
        $satangText = $satang > 0 ? self::readNumber($satang) . 'สตางค์' : 'ถ้วน';

        return $bahtText . $satangText;
    }

    private static function readNumber(int $number): string
    {
        if ($number === 0) return 'ศูนย์';

        $positions = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
        $digits = ['ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า'];

        $result = '';
        $n = $number;

        if ($n >= 1000000) {
            $million = intdiv($n, 1000000);
            $result .= self::readNumber($million) . 'ล้าน';
            $n = $n % 1000000;
            if ($n === 0) return $result;
        }

        $str = (string)$n;
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $str[$i];
            $pos = $len - $i - 1;

            if ($digit === 0) continue;

            if ($pos === 1) {
                if ($digit === 1) $result .= 'สิบ';
                elseif ($digit === 2) $result .= 'ยี่สิบ';
                else $result .= $digits[$digit] . 'สิบ';
                continue;
            }

            if ($pos === 0) {
                if ($digit === 1 && $len > 1) $result .= 'เอ็ด';
                else $result .= $digits[$digit];
                continue;
            }

            $result .= $digits[$digit] . $positions[$pos];
        }

        return $result;
    }
}
