<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class RawLogsExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Employee Name', 'Biometric ID', 'Log State', 'Date Time (Log)', 'Location'];
    }

    public function map($log): array
    {
        return [
            $log['employee_name'],
            $log['biometric_id'],
            $log['state'],
            $log['logs'],
            $log['location'] ?? 'N/A',
        ];
    }
}
