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

class ProfitLossExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
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
        $data[] = ['PROFIT & LOSS STATEMENT'];
        $data[] = [];
        
        // Period and Branch Info
        $data[] = ['Period:', $this->report['period']['start_date'] . ' to ' . $this->report['period']['end_date']];
        if ($this->branch) {
            $data[] = ['Branch:', $this->branch->name];
        }
        $data[] = [];
        
        // Revenue Section
        $data[] = ['REVENUE'];
        $data[] = ['Retail Sales', '₹' . number_format($this->report['revenue']['by_channel']['retail'], 2)];
        $data[] = ['Wholesale Sales', '₹' . number_format($this->report['revenue']['by_channel']['wholesale'], 2)];
        $data[] = ['Online Sales', '₹' . number_format($this->report['revenue']['by_channel']['online'], 2)];
        $data[] = ['Total Revenue', '₹' . number_format($this->report['revenue']['total'], 2)];
        $data[] = [];
        
        // Cost of Goods Sold
        $data[] = ['COST OF GOODS SOLD'];
        $data[] = ['COGS', '₹' . number_format($this->report['cost_of_goods_sold'], 2)];
        $data[] = [];
        
        // Gross Profit
        $data[] = ['Gross Profit', '₹' . number_format($this->report['gross_profit'], 2)];
        $data[] = ['Gross Profit Margin', number_format($this->report['gross_profit_margin'], 2) . '%'];
        $data[] = [];
        
        // Operating Expenses
        $data[] = ['OPERATING EXPENSES'];
        $data[] = ['Total Operating Expenses', '₹' . number_format($this->report['operating_expenses'], 2)];
        $data[] = [];
        
        // Net Profit
        $data[] = ['Net Profit', '₹' . number_format($this->report['net_profit'], 2)];
        $data[] = ['Net Profit Margin', number_format($this->report['net_profit_margin'], 2) . '%'];
        $data[] = [];
        
        // Month-over-Month Comparison
        $data[] = ['MONTH-OVER-MONTH COMPARISON'];
        $data[] = ['Revenue Change', '₹' . number_format($this->report['comparison']['revenue_change'], 2)];
        $data[] = ['Revenue Change %', number_format($this->report['comparison']['revenue_change_percentage'], 2) . '%'];
        $data[] = ['Profit Change', '₹' . number_format($this->report['comparison']['profit_change'], 2)];
        $data[] = ['Profit Change %', number_format($this->report['comparison']['profit_change_percentage'], 2) . '%'];
        
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
        $sectionHeaders = [5, 12, 15, 19, 22, 27];
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
        $totalRows = [10, 14, 18, 21, 25, 26, 29, 30];
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
        $sheet->getStyle('A1:B31')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Right align amounts
        $sheet->getStyle('B2:B31')->applyFromArray([
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
        return 'Profit & Loss Statement';
    }
}
