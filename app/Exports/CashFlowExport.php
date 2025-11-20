<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CashFlowExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $report;
    protected $branch;

    public function __construct($report, $branch = null)
    {
        $this->report = $report;
        $this->branch = $branch;
    }

    public function array(): array
    {
        $data = [];
        
        // Header
        $data[] = ['CASH FLOW STATEMENT'];
        $data[] = [];
        
        // Period and Branch Info
        $data[] = ['Period:', $this->report['period']['start_date'] . ' to ' . $this->report['period']['end_date']];
        if ($this->branch) {
            $data[] = ['Branch:', $this->branch->name];
        }
        $data[] = [];
        
        // Operating Activities
        $data[] = ['OPERATING ACTIVITIES'];
        $data[] = ['Cash Inflow', '₹' . number_format($this->report['operating_activities']['inflow'], 2)];
        $data[] = ['Cash Outflow', '₹' . number_format($this->report['operating_activities']['outflow'], 2)];
        $data[] = ['Net Operating Cash Flow', '₹' . number_format($this->report['operating_activities']['net'], 2)];
        $data[] = [];
        
        // Investing Activities
        $data[] = ['INVESTING ACTIVITIES'];
        $data[] = ['Cash Inflow', '₹' . number_format($this->report['investing_activities']['inflow'], 2)];
        $data[] = ['Cash Outflow', '₹' . number_format($this->report['investing_activities']['outflow'], 2)];
        $data[] = ['Net Investing Cash Flow', '₹' . number_format($this->report['investing_activities']['net'], 2)];
        $data[] = [];
        
        // Financing Activities
        $data[] = ['FINANCING ACTIVITIES'];
        $data[] = ['Cash Inflow', '₹' . number_format($this->report['financing_activities']['inflow'], 2)];
        $data[] = ['Cash Outflow', '₹' . number_format($this->report['financing_activities']['outflow'], 2)];
        $data[] = ['Net Financing Cash Flow', '₹' . number_format($this->report['financing_activities']['net'], 2)];
        $data[] = [];
        
        // Net Cash Flow
        $data[] = ['Net Cash Flow', '₹' . number_format($this->report['net_cash_flow'], 2)];
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Description',
            'Amount'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
        ]);

        // Style section headers
        $sectionHeaders = [5, 10, 15, 20];
        foreach ($sectionHeaders as $row) {
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5'],
                ],
            ]);
        }

        // Style total rows
        $totalRows = [8, 13, 18, 21];
        foreach ($totalRows as $row) {
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        // Style all cells
        $sheet->getStyle('A1:B21')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Right align amounts
        $sheet->getStyle('B2:B21')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
        ];
    }

    public function title(): string
    {
        return 'Cash Flow Statement';
    }
}

