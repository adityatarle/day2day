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

class BalanceSheetExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
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
        $data[] = ['BALANCE SHEET'];
        $data[] = [];
        
        // Date and Branch Info
        $data[] = ['As of Date:', $this->report['as_of_date']];
        if ($this->branch) {
            $data[] = ['Branch:', $this->branch->name];
        }
        $data[] = [];
        
        // Assets Section
        $data[] = ['ASSETS'];
        $data[] = ['Current Assets', '₹' . number_format(array_sum($this->report['assets']['current_assets']), 2)];
        $data[] = ['  Cash and Cash Equivalents', '₹' . number_format($this->report['assets']['current_assets']['cash'], 2)];
        $data[] = ['  Accounts Receivable', '₹' . number_format($this->report['assets']['current_assets']['receivables'], 2)];
        $data[] = ['  Inventory', '₹' . number_format($this->report['assets']['current_assets']['inventory'], 2)];
        $data[] = [];
        $data[] = ['Fixed Assets', '₹' . number_format(array_sum($this->report['assets']['fixed_assets']), 2)];
        $data[] = ['  Equipment', '₹' . number_format($this->report['assets']['fixed_assets']['equipment'], 2)];
        $data[] = ['  Vehicles', '₹' . number_format($this->report['assets']['fixed_assets']['vehicles'], 2)];
        $data[] = [];
        $data[] = ['Total Assets', '₹' . number_format($this->report['assets']['total_assets'], 2)];
        $data[] = [];
        
        // Liabilities Section
        $data[] = ['LIABILITIES'];
        $data[] = ['Current Liabilities', '₹' . number_format(array_sum($this->report['liabilities']['current_liabilities']), 2)];
        $data[] = ['  Accounts Payable', '₹' . number_format($this->report['liabilities']['current_liabilities']['payables'], 2)];
        $data[] = ['  Accrued Expenses', '₹' . number_format($this->report['liabilities']['current_liabilities']['accrued'], 2)];
        $data[] = [];
        $data[] = ['Long-term Liabilities', '₹' . number_format(array_sum($this->report['liabilities']['long_term_liabilities']), 2)];
        $data[] = ['  Loans Payable', '₹' . number_format($this->report['liabilities']['long_term_liabilities']['loans'], 2)];
        $data[] = [];
        $data[] = ['Total Liabilities', '₹' . number_format($this->report['liabilities']['total_liabilities'], 2)];
        $data[] = [];
        
        // Equity Section
        $data[] = ['EQUITY'];
        $data[] = ['Owner\'s Capital', '₹' . number_format($this->report['equity']['owner_equity']['capital'], 2)];
        $data[] = ['Retained Earnings', '₹' . number_format($this->report['equity']['retained_earnings']['earnings'], 2)];
        $data[] = [];
        $data[] = ['Total Equity', '₹' . number_format($this->report['equity']['total_equity'], 2)];
        $data[] = [];
        
        // Balance Check
        $data[] = ['Total Liabilities + Equity', '₹' . number_format($this->report['liabilities']['total_liabilities'] + $this->report['equity']['total_equity'], 2)];
        $data[] = ['Balance Check', $this->report['balance_check'] ? 'BALANCED' : 'NOT BALANCED'];
        
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
        $sectionHeaders = [5, 15, 22, 30];
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
        $totalRows = [14, 21, 28, 35, 36];
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
        $sheet->getStyle('A1:B36')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Right align amounts
        $sheet->getStyle('B2:B36')->applyFromArray([
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
        return 'Balance Sheet';
    }
}

