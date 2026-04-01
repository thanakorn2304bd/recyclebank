<?php

namespace App\Support\WithdrawRequests;

use App\Models\WithdrawRequest;
use App\Support\ThaiBaht;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class WithdrawRequestPdfViewDataFactory
{
    private const ORGANIZATION_NAME = 'กองทุนธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่';

    public function make(WithdrawRequest $withdrawRequest): array
    {
        $household = $withdrawRequest->household;
        $amount = round((float) $withdrawRequest->requested_amount, 2);

        return [
            'orgName' => self::ORGANIZATION_NAME,
            'title' => 'ใบถอนเงิน',
            'accountNo' => $household?->account_no ?? '-',
            'accountName' => $household?->contact_person ?? '-',
            'amount' => $amount,
            'amountText' => ThaiBaht::text($amount),
            'dateText' => $this->formatDate($withdrawRequest->requested_for_date),
            'officerName' => '',
            'footerNote' => '***เอกสารฉบับนี้เป็นคำขอรออนุมัติ ให้นำไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน',
        ];
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('d/m/Y');
        }

        return Carbon::parse($date)->format('d/m/Y');
    }
}
