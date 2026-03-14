<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

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
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Insert 4 rows before the actual header
                $sheet->insertNewRowBefore(1, 4);

                // Set company, period, and pay date
                $sheet->setCellValue('A1', $this->company);
                $sheet->setCellValue('A2', $this->period);
                $sheet->setCellValue('A3', $this->payDate);

                // Style the first 3 rows (bold)
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);
            },
        ];
    }
}
