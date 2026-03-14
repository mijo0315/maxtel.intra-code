<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class LogsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return ['Employee Name', 'Date', 'Time In', 'Time Out', 'Location'];
    }

    public function map($log): array
    {
        return [
            $log['employee_name'],
            $log['date'],
            $log['time_in'],
            $log['time_out'],
            $log['location'],
        ];
    }
}

