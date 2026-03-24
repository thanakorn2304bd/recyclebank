<?php

namespace App\Support\Reports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportSpreadsheetBuilder
{
    public function build(array $data): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary');

        $summaryRows = [
            ['สรุปรายงานธนาคารวัสดุรีไซเคิล', ''],
            ['ช่วงข้อมูล', $data['periodLabel']],
            ['สิทธิ์ผู้ใช้', $data['isPrivileged'] ? 'staff/admin' : 'member'],
            ['ตัวกรองเพิ่มเติม', $data['filterSummary'] !== [] ? implode(' | ', $data['filterSummary']) : 'ไม่มี'],
            ['', ''],
        ];

        if ($data['isPrivileged']) {
            $summaryRows = array_merge($summaryRows, [
                ['ครัวเรือนทั้งหมด', $data['householdSummary']['totalHouseholds']],
                ['ครัวเรือนใช้งาน', $data['householdSummary']['activeHouseholds']],
                ['ครัวเรือนรออนุมัติ', $data['householdSummary']['pendingHouseholds']],
                ['ครัวเรือนปิดใช้งาน', $data['householdSummary']['inactiveHouseholds']],
                ['สมาชิกในครัวเรือน', $data['householdSummary']['memberCount']],
                ['ยอดคงเหลือรวม', $data['householdSummary']['totalBalance']],
                ['ยอดรับซื้อรวม', $data['transactionSummary']['depositAmount']],
                ['ยอดถอนรวม', $data['transactionSummary']['withdrawAmount']],
                ['น้ำหนักรับซื้อรวม', $data['transactionSummary']['depositWeight']],
                ['จำนวนรายการรวม', $data['transactionSummary']['transactionCount']],
            ]);
        } else {
            $summaryRows = array_merge($summaryRows, [
                ['เลขบัญชี', $data['focusHousehold']?->account_no ?? '-'],
                ['ผู้ติดต่อ', $data['focusHousehold']?->contact_person ?? '-'],
                ['ชุมชน', $data['focusHousehold']?->community?->community_name ?? '-'],
                ['ยอดคงเหลือปัจจุบัน', (float) ($data['focusHousehold']?->total_balance ?? 0)],
                ['ยอดรับซื้อสะสม', $data['transactionSummary']['depositAmount']],
                ['ยอดถอนสะสม', $data['transactionSummary']['withdrawAmount']],
                ['น้ำหนักวัสดุที่ขายได้', $data['transactionSummary']['depositWeight']],
                ['จำนวนสมาชิกในครัวเรือน', $data['householdSummary']['memberCount']],
            ]);
        }

        $summarySheet->fromArray($summaryRows, null, 'A1');
        $this->styleWorksheet($summarySheet, 'A1:B'.count($summaryRows));

        $materialSheet = $spreadsheet->createSheet();
        $materialSheet->setTitle('Top Materials');
        $materialRows = [
            ['วัสดุ', 'หมวด', 'จำนวนรายการ', 'น้ำหนัก', 'มูลค่า'],
        ];
        foreach ($data['topMaterials'] as $material) {
            $materialRows[] = [
                $material->material_name,
                $material->category_name ?? 'ไม่ระบุหมวด',
                (int) $material->transaction_count,
                (float) $material->total_weight,
                (float) $material->total_amount,
            ];
        }
        $materialSheet->fromArray($materialRows, null, 'A1');
        $this->styleWorksheet($materialSheet, 'A1:E'.max(1, count($materialRows)));

        $monthlySheet = $spreadsheet->createSheet();
        $monthlySheet->setTitle('Monthly');
        $monthlyRows = [
            ['เดือน', 'จำนวนรายการ', 'ยอดรับซื้อ', 'ยอดถอน', 'น้ำหนักรับซื้อ'],
        ];
        foreach ($data['monthlySummary'] as $month) {
            $monthlyRows[] = [
                $month->month_label,
                (int) $month->transaction_count,
                (float) $month->deposit_amount,
                (float) $month->withdraw_amount,
                (float) $month->deposit_weight,
            ];
        }
        $monthlySheet->fromArray($monthlyRows, null, 'A1');
        $this->styleWorksheet($monthlySheet, 'A1:E'.max(1, count($monthlyRows)));

        $detailSheet = $spreadsheet->createSheet();
        if ($data['isPrivileged']) {
            $detailSheet->setTitle('Communities');
            $detailRows = [
                ['ชุมชน', 'ครัวเรือน', 'สมาชิก', 'ยอดรับซื้อ', 'ยอดถอน', 'น้ำหนัก', 'ยอดคงเหลือ'],
            ];
            foreach ($data['communityStats'] as $community) {
                $detailRows[] = [
                    $community->community_id.' - '.$community->community_name,
                    (int) $community->household_count,
                    (int) $community->member_count,
                    (float) $community->deposit_amount,
                    (float) $community->withdraw_amount,
                    (float) $community->deposit_weight,
                    (float) $community->total_balance,
                ];
            }
            $detailSheet->fromArray($detailRows, null, 'A1');
            $this->styleWorksheet($detailSheet, 'A1:G'.max(1, count($detailRows)));
        } else {
            $detailSheet->setTitle('Recent Transactions');
            $detailRows = [
                ['วันที่', 'ประเภท', 'น้ำหนัก', 'จำนวนเงิน'],
            ];
            foreach ($data['recentTransactions'] as $transaction) {
                $detailRows[] = [
                    optional($transaction->transaction_date)->format('d/m/Y'),
                    $transaction->transaction_type === 'deposit' ? 'ฝาก' : 'ถอน',
                    (float) $transaction->total_weight,
                    (float) $transaction->total_amount,
                ];
            }
            $detailSheet->fromArray($detailRows, null, 'A1');
            $this->styleWorksheet($detailSheet, 'A1:D'.max(1, count($detailRows)));
        }

        return $spreadsheet;
    }

    private function styleWorksheet(Worksheet $sheet, string $range): void
    {
        [, $endCell] = explode(':', $range);
        preg_match('/([A-Z]+)(\d+)/', $endCell, $matches);
        $lastColumn = $matches[1] ?? 'A';
        $lastRow = (int) ($matches[2] ?? 1);

        $sheet->getStyle($range)->getFont()->setName('Arial');
        $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true);
        $sheet->getStyle('A1:'.$lastColumn.'1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('D7F0E3');

        for ($columnIndex = 1; $columnIndex <= Coordinate::columnIndexFromString($lastColumn); $columnIndex++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
        }

        if ($lastRow >= 2) {
            $sheet->getStyle('A2:'.$lastColumn.$lastRow)->getAlignment()->setWrapText(true);
        }
    }
}
