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

class ExpenseAnalysisExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
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
        $data[] = ['EXPENSE ANALYSIS REPORT'];
        $data[] = [];
        
        // Period and Branch Info
        $data[] = ['Period:', $this->report['period']['start_date'] . ' to ' . $this->report['period']['end_date']];
        if ($this->branch) {
            $data[] = ['Branch:', $this->branch->name];
        }
        $data[] = [];
        
        // Summary
        $data[] = ['SUMMARY'];
        $data[] = ['Total Expenses', '₹' . number_format($this->report['total_expenses'], 2)];
        $data[] = ['Total Units Sold', $this->report['cost_per_unit']['total_units_sold']];
        $data[] = ['Cost Per Unit', '₹' . number_format($this->report['cost_per_unit']['cost_per_unit'], 2)];
        $data[] = [];
        
        // Category Breakdown
        $data[] = ['CATEGORY BREAKDOWN'];
        foreach ($this->report['category_breakdown'] as $category) {
            $data[] = [
                $category['category_name'],
                '₹' . number_format($category['total_amount'], 2),
                $category['count'] . ' transactions',
                '₹' . number_format($category['average_amount'], 2) . ' avg'
            ];
        }
        $data[] = [];
        
        // Variance Analysis
        $data[] = ['VARIANCE ANALYSIS'];
        $data[] = ['Total Budgeted', '₹' . number_format($this->report['variance_analysis']['total_budgeted'], 2)];
        $data[] = ['Total Actual', '₹' . number_format($this->report['variance_analysis']['total_actual'], 2)];
        $data[] = ['Variance', '₹' . number_format($this->report['variance_analysis']['variance'], 2)];
        $data[] = ['Variance %', number_format($this->report['variance_analysis']['variance_percentage'], 2) . '%'];
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Description',
            'Amount',
            'Details',
            'Additional Info'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->applyFromArray([
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
        $sectionHeaders = [5, 9, 15, 20];
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
        $totalRows = [6, 7, 8, 21, 22, 23, 24];
        foreach ($totalRows as $row) {
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
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
        $sheet->getStyle('A1:D24')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Right align amounts
        $sheet->getStyle('B2:D24')->applyFromArray([
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
            'C' => 20,
            'D' => 20,
        ];
    }

    public function title(): string
    {
        return 'Expense Analysis';
    }
}

