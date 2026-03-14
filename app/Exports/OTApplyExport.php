<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class OTApplyExport implements FromCollection, WithHeadings, WithMapping
{
    protected $otApplications;

    public function __construct($otApplications)
    {
        $this->otApplications = $otApplications;
    }

    public function collection()
    {
        return $this->otApplications;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Over Time Type',
            'Date',
            'From (Time)',
            'To (Time)',
            'Total Hours',
            'Status'
        ];
    }

    // public function map($row): array
    // {
    //     $fromTime = strtotime($row['time_from']);
    //     $toTime = strtotime($row['time_to']);
    //     $totalHours = round(($toTime - $fromTime) / 3600, 2); // Convert seconds to hours

    //     return [
    //         $row['employee_name'],
    //         $row['ot_type'],
    //         $row['date_target'],
    //         date("H:i A", $fromTime),
    //         date("H:i A", $toTime),
    //         $totalHours . " hrs",
    //         $row['status']
    //     ];
    // }
    public function map($row): array
    {
        $fromTime = strtotime($row['time_from']);
        $toTime = strtotime($row['time_to']);
        $totalSeconds = $toTime - $fromTime;
    
        // Convert time components
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
    
        // Format output dynamically
        $totalTime = [];
        if ($hours > 0) {
            $totalTime[] = "{$hours} hr" . ($hours > 1 ? "s" : "");
        }
        if ($minutes > 0) {
            $totalTime[] = "{$minutes} min" . ($minutes > 1 ? "s" : "");
        }
        if ($seconds > 0 && $hours == 0) { // Show seconds only if there are no hours
            $totalTime[] = "{$seconds} sec" . ($seconds > 1 ? "s" : "");
        }
    
        return [
            $row['employee_name'],
            $row['ot_type'],
            $row['date_target'],
            date("H:i A", $fromTime),
            date("H:i A", $toTime),
            implode(" ", $totalTime), // Properly formatted total duration
            $row['status']
        ];
    }
    
}
