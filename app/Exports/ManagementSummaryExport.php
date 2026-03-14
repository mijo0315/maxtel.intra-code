<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Support\Collection;

class ManagementSummaryExport implements FromCollection, WithHeadings, WithColumnWidths
{
    protected $employee;
    protected $nte_records;
    protected $performance_records;
    protected $disciplinary_records;

    public function __construct($employee, $nte_records, $performance_records, $disciplinary_records)
    {
        $this->employee = $employee;
        $this->nte_records = $nte_records;
        $this->performance_records = $performance_records;
        $this->disciplinary_records = $disciplinary_records;
    }

    public function collection()
    {
        $data = collect();

        // Add employee header
        $data->push(['Employee Name:', $this->employee->name]);
        $data->push(['Employee Code:', $this->employee->emp_code]);
        $data->push(['']);
        $data->push(['']);

        // NTE Section
        $data->push(['NTE (Notice to Explain)']);
        $data->push(['Date Served', 'Case Details', 'Remarks']);
        
        if ($this->nte_records->count() > 0) {
            foreach ($this->nte_records as $record) {
                $data->push([
                    \Carbon\Carbon::parse($record->date_served)->format('Y-m-d'),
                    $record->case_details,
                    $record->remarks ?? '-',
                ]);
            }
        } else {
            $data->push(['No records found', '', '']);
        }
        
        $data->push(['']);
        $data->push(['']);

        // Performance Improvement Section
        $data->push(['Performance Improvement Plan']);
        $data->push(['Date Served', 'Case Details', 'Remarks']);
        
        if ($this->performance_records->count() > 0) {
            foreach ($this->performance_records as $record) {
                $data->push([
                    \Carbon\Carbon::parse($record->date_served)->format('Y-m-d'),
                    $record->case_details,
                    $record->remarks ?? '-',
                ]);
            }
        } else {
            $data->push(['No records found', '', '']);
        }
        
        $data->push(['']);
        $data->push(['']);

        // Disciplinary Section
        $data->push(['Disciplinary Actions']);
        $data->push(['Date Served', 'Case Details', 'Remarks']);
        
        if ($this->disciplinary_records->count() > 0) {
            foreach ($this->disciplinary_records as $record) {
                $data->push([
                    \Carbon\Carbon::parse($record->date_served)->format('Y-m-d'),
                    $record->case_details,
                    $record->remarks ?? '-',
                ]);
            }
        } else {
            $data->push(['No records found', '', '']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 50,
            'C' => 40,
        ];
    }
}
