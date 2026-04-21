<?php

namespace App\Support\Transactions;

use App\Models\Transaction;
use App\Models\UserAccount;
use App\Support\ThaiBaht;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TransactionPdfViewDataFactory
{
    private const ORGANIZATION_NAME = 'กองทุนธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่';

    public function withdrawSlip(Transaction $transaction, ?UserAccount $printedBy = null): array
    {
        $household = $transaction->household;
        $amount = round((float) $transaction->total_amount, 2);

        return [
            'tx' => $transaction,
            'orgName' => self::ORGANIZATION_NAME,
            'title' => 'ใบคำขอถอนเงิน',
            'accountNo' => $household?->account_no ?? '-',
            'accountName' => $household?->contact_person ?? '-',
            'amount' => $amount,
            'amountText' => ThaiBaht::text($amount),
            'dateText' => $this->formatDate($transaction->transaction_date),
            'officerName' => $transaction->recordedByUser?->staff?->full_name
                ?? $transaction->recordedByUser?->username
                ?? '',
            'printedByText' => $this->printedByText($printedBy),
        ];
    }

    public function receipt(Transaction $transaction, int $rowsPerPage = 7, ?UserAccount $printedBy = null): array
    {
        $household = $transaction->household;

        return [
            'tx' => $transaction,
            'orgName' => self::ORGANIZATION_NAME,
            'title' => 'ใบนำฝาก',
            'dateText' => $this->formatDate($transaction->transaction_date),
            'houseNo' => $household?->house_no ?? '..........',
            'villageNo' => $household?->village_no ?? '..........',
            'community' => $household?->community_id ?? '..........',
            'accountNo' => $household?->account_no ?? '................................',
            'accountName' => $household?->contact_person ?? '................................',
            'rowsPerPage' => $rowsPerPage,
            'pages' => $this->buildReceiptPages($transaction, $rowsPerPage),
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

    private function buildReceiptPages(Transaction $transaction, int $rowsPerPage): Collection
    {
        $details = $transaction->details->values();

        if ($details->isEmpty()) {
            return collect([[
                'items' => collect(),
                'carryIn' => 0.0,
                'carryInText' => $this->formatMoney(0.0),
                'footerTotal' => round((float) $transaction->total_amount, 2),
                'footerTotalText' => $this->formatMoney((float) $transaction->total_amount),
                'footerLabel' => 'รวมเป็นเงิน',
                'showCarryIn' => false,
                'blankRows' => $rowsPerPage,
                'amountText' => ThaiBaht::text((float) $transaction->total_amount),
            ]]);
        }

        $pages = collect();
        $remaining = $details;
        $carryIn = 0.0;
        $isFirstPage = true;

        while ($remaining->isNotEmpty()) {
            $pageCapacity = $isFirstPage ? $rowsPerPage : max($rowsPerPage - 1, 1);
            $items = $remaining->take($pageCapacity)->values();
            $remaining = $remaining->slice($pageCapacity)->values();

            $pageItemsTotal = round((float) $items->sum('amount'), 2);
            $runningTotal = round($carryIn + $pageItemsTotal, 2);
            $isLastPage = $remaining->isEmpty();
            $footerTotal = $isLastPage
                ? round((float) $transaction->total_amount, 2)
                : $runningTotal;
            $showCarryIn = ! $isFirstPage;

            $pages->push([
                'items' => $this->receiptItems($items),
                'carryIn' => $carryIn,
                'carryInText' => $this->formatMoney($carryIn),
                'footerTotal' => $footerTotal,
                'footerTotalText' => $this->formatMoney($footerTotal),
                'footerLabel' => $isLastPage ? 'รวมเป็นเงิน' : 'ยอดยกไป',
                'showCarryIn' => $showCarryIn,
                'blankRows' => max($rowsPerPage - $items->count() - ($showCarryIn ? 1 : 0), 0),
                'amountText' => ThaiBaht::text($footerTotal),
            ]);

            $carryIn = $footerTotal;
            $isFirstPage = false;
        }

        return $pages;
    }

    private function receiptItems(Collection $items): Collection
    {
        return $items->map(fn ($item) => [
            'materialName' => $item->material?->material_name ?? '-',
            'weightText' => $this->formatMoney((float) $item->weight),
            'pricePerUnitText' => $this->formatMoney((float) $item->price_per_unit),
            'amountText' => $this->formatMoney((float) $item->amount),
        ]);
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('d/m/Y');
        }

        return Carbon::parse($date)->format('d/m/Y');
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2);
    }
}
