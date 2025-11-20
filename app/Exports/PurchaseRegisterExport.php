<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PurchaseRegisterExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $report;
    protected $branch;

    public function __construct($report, $branch = null)
    {
        $this->report = $report;
        $this->branch = $branch;
    }

    public function collection()
    {
        return $this->report['transactions']->map(function ($transaction) {
            return [
                'Invoice Date' => $transaction->invoice_date->format('d-m-Y'),
                'Invoice Number' => $transaction->invoice_number,
                'Vendor Name' => $transaction->vendor ? $transaction->vendor->name : 'Unknown Vendor',
                'Vendor GSTIN' => $transaction->vendor ? $transaction->vendor->gst_number : '',
                'Branch' => $transaction->branch->name,
                'Taxable Value' => number_format($transaction->taxable_value, 2),
                'CGST Amount' => number_format($transaction->cgst_amount, 2),
                'SGST Amount' => number_format($transaction->sgst_amount, 2),
                'IGST Amount' => number_format($transaction->igst_amount, 2),
                'Total GST' => number_format($transaction->total_gst, 2),
                'Total Amount' => number_format($transaction->total_amount, 2),
                'GST Rate' => $transaction->gst_rate,
                'Place of Supply' => $transaction->place_of_supply,
                'ITC Available' => number_format($transaction->total_gst, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice Date',
            'Invoice Number',
            'Vendor Name',
            'Vendor GSTIN',
            'Branch',
            'Taxable Value',
            'CGST Amount',
            'SGST Amount',
            'IGST Amount',
            'Total GST',
            'Total Amount',
            'GST Rate',
            'Place of Supply',
            'ITC Available',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->report['transactions']->count() + 1;

        // Header styling
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1976D2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add summary section
        $summaryStartRow = $lastRow + 3;
        
        $sheet->setCellValue("A{$summaryStartRow}", 'SUMMARY');
        $sheet->getStyle("A{$summaryStartRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
        ]);

        $summaryData = [
            ['Total Invoices', $this->report['summary']['total_invoices']],
            ['Total Taxable Value', '₹' . number_format($this->report['summary']['total_taxable_value'], 2)],
            ['Total CGST', '₹' . number_format($this->report['summary']['total_cgst'], 2)],
            ['Total SGST', '₹' . number_format($this->report['summary']['total_sgst'], 2)],
            ['Total IGST', '₹' . number_format($this->report['summary']['total_igst'], 2)],
            ['Total GST', '₹' . number_format($this->report['summary']['total_gst'], 2)],
            ['Total Amount', '₹' . number_format($this->report['summary']['total_amount'], 2)],
            ['Total ITC Available', '₹' . number_format($this->report['summary']['total_itc'], 2)],
        ];

        foreach ($summaryData as $index => $row) {
            $rowNum = $summaryStartRow + 1 + $index;
            $sheet->setCellValue("A{$rowNum}", $row[0]);
            $sheet->setCellValue("B{$rowNum}", $row[1]);
            
            if ($index === count($summaryData) - 1) { // Last row (Total ITC)
                $sheet->getStyle("A{$rowNum}:B{$rowNum}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
            }
        }

        // Style all data cells
        $sheet->getStyle("A1:N{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Right align numeric columns
        $numericColumns = ['F', 'G', 'H', 'I', 'J', 'K', 'N'];
        foreach ($numericColumns as $column) {
            $sheet->getStyle("{$column}2:{$column}{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Invoice Date
            'B' => 20, // Invoice Number
            'C' => 25, // Vendor Name
            'D' => 20, // Vendor GSTIN
            'E' => 20, // Branch
            'F' => 15, // Taxable Value
            'G' => 15, // CGST Amount
            'H' => 15, // SGST Amount
            'I' => 15, // IGST Amount
            'J' => 15, // Total GST
            'K' => 15, // Total Amount
            'L' => 10, // GST Rate
            'M' => 20, // Place of Supply
            'N' => 15, // ITC Available
        ];
    }

    public function title(): string
    {
        return 'Purchase Register';
    }
}

