<?php

namespace App\Http\Controllers;

use App\Exceptions\MissingMemberHouseholdException;
use App\Http\Requests\ReportFiltersRequest;
use App\Support\ActivityLogger;
use App\Support\Reports\ReportDataBuilder;
use App\Support\Reports\ReportSpreadsheetBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportDataBuilder $reportDataBuilder,
        private readonly ReportSpreadsheetBuilder $reportSpreadsheetBuilder,
    ) {}

    public function index(ReportFiltersRequest $request)
    {
        try {
            $data = $this->reportDataBuilder->build($request->user(), $request->filters());
        } catch (MissingMemberHouseholdException $exception) {
            return redirect()->route('main-menu')
                ->withErrors($exception->getMessage());
        }

        return view('reports.index', $data);
    }

    public function exportPdf(ReportFiltersRequest $request)
    {
        $data = $this->buildReportExportData($request);

        ActivityLogger::forCurrentUser('reports', 'ส่งออกรายงานสรุปเป็น PDF');

        $pdf = Pdf::loadView('pdf.report_summary', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->buildExportFilename('pdf'));
    }

    public function exportExcel(ReportFiltersRequest $request)
    {
        $data = $this->buildReportExportData($request);

        ActivityLogger::forCurrentUser('reports', 'ส่งออกรายงานสรุปเป็น Excel');

        $spreadsheet = $this->reportSpreadsheetBuilder->build($data);
        $tempFile = tempnam(sys_get_temp_dir(), 'recyclebank-report-');

        if ($tempFile === false) {
            abort(500, 'ไม่สามารถสร้างไฟล์รายงานชั่วคราวได้');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download(
            $tempFile,
            $this->buildExportFilename('xlsx'),
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function buildReportExportData(ReportFiltersRequest $request): array
    {
        try {
            return $this->reportDataBuilder->build($request->user(), $request->filters());
        } catch (MissingMemberHouseholdException $exception) {
            abort(404, $exception->getMessage());
        }
    }

    private function buildExportFilename(string $extension): string
    {
        return 'report_'.now()->format('Ymd_His').'.'.$extension;
    }
}
