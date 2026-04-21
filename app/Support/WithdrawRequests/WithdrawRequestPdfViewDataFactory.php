<?php

namespace App\Support\WithdrawRequests;

use App\Models\UserAccount;
use App\Models\WithdrawRequest;
use App\Support\ThaiBaht;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class WithdrawRequestPdfViewDataFactory
{
    private const ORGANIZATION_NAME = 'กองทุนธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่';

    public function make(WithdrawRequest $withdrawRequest, ?UserAccount $printedBy = null): array
    {
        $household = $withdrawRequest->household;
        $amount = round((float) $withdrawRequest->requested_amount, 2);

        return [
            'orgName' => self::ORGANIZATION_NAME,
            'title' => 'ใบคำขอถอนเงิน',
            'accountNo' => $household?->account_no ?? '-',
            'accountName' => $household?->contact_person ?? '-',
            'amount' => $amount,
            'amountText' => ThaiBaht::text($amount),
            'dateText' => $this->formatDate($withdrawRequest->requested_for_date),
            'officerName' => '',
            'footerNote' => '***เอกสารฉบับนี้เป็นคำขอรออนุมัติ ให้นำไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน',
            'printedByText' => $this->printedByText($printedBy),
        ];
    }

    private function printedByText(?UserAccount $printedBy): string
    {
        $name = $printedBy?->staff?->full_name
            ?? $printedBy?->household?->contact_person
            ?? $printedBy?->username;

        if (! $name) {
            return '';
        }

        return 'พิมพ์โดย '.$name.' เมื่อ '.CarbonImmutable::now()->format('d/m/Y H:i');
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('d/m/Y');
        }

        return Carbon::parse($date)->format('d/m/Y');
    }
}
