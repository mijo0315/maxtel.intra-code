<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use DateTime;
use DateTimeZone;

class EntriesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $bioId;
    protected $dateRange;

    public function __construct($bioId = null, $dateRange = null)
    {
        $this->bioId = $bioId;
        $this->dateRange = $dateRange;
    }

    public function collection()
    {
        try {
            $query = DB::connection("face_db")->table("tbl_entries");

            if ($this->bioId && $this->bioId != '0') {
                $query->where("biometric_id", $this->bioId);
            }

            if ($this->dateRange) {
                [$start_date, $end_date] = explode(' - ', $this->dateRange);
                
                // Convert to UNIX timestamps
                $start_ts = strtotime($start_date . ' 00:00:00');
                $end_ts = strtotime($end_date . ' 23:59:59');
                
                $query->whereBetween('phone_timestamp', [$start_ts, $end_ts]);
            }

            return $query->orderBy("biometric_id", "ASC")
                ->orderBy("phone_timestamp", "DESC")
                ->limit(2000)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching entries data: ' . $e->getMessage());
            throw new \Exception('Unable to fetch entries data. The face database may not be configured correctly. ' . $e->getMessage());
        }
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Remarks',
            'Server Timestamp',
            'Location'
        ];
    }

    public function map($row): array
    {
        // Get employee name
        $employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("bio_id", $row->biometric_id)
            ->first();

        $empName = $employee ? 
            $employee->last_name . ', ' . $employee->first_name . ' ' . $employee->middle_name 
            : 'Employee ' . $row->biometric_id;

        // Format timestamp
        if (!empty($row->phone_timestamp) && is_numeric($row->phone_timestamp)) {
            $formattedTime = (new DateTime('@' . $row->phone_timestamp))
                ->setTimezone(new DateTimeZone('Asia/Manila'))
                ->format('Y-m-d H:i:s');
        } else {
            $formattedTime = 'N/A';
        }

        return [
            $row->biometric_id,
            $empName,
            $row->remarks ?? 'N/A',
            $formattedTime,
            $row->location ?? 'No Data',
        ];
    }
}

