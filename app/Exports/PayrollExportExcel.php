<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PayrollExportExcel implements FromCollection, WithHeadings, WithEvents
{
    protected $data;
    protected $header;
    protected $company;
    protected $period;
    protected $payDate;

    public function __construct($data, $header, $company, $period, $payDate)
    {
        $this->data = $data;
        $this->header = $header;
        $this->company = $company;
        $this->period = $period;
        $this->payDate = $payDate;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->header;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Insert 4 rows before the actual header
                $sheet->insertNewRowBefore(1, 4);

                // Set company, period, and pay date
                $sheet->setCellValue('A1', $this->company);
                $sheet->setCellValue('A2', $this->period);
                $sheet->setCellValue('A3', $this->payDate);

                // Style the first 3 rows
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                /*
                 * Format payroll numeric values:
                 * 1500      => 1,500.00
                 * 7491.51   => 7,491.51
                 * 0         => 0.00
                 *
                 * Header is on row 5 because 4 rows were inserted.
                 * Employee data starts on row 6.
                 */
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle(
                    'C6:' . $highestColumn . $highestRow
                )->getNumberFormat()->setFormatCode(
                    '#,##0.00'
                );
            },
        ];
    }
}