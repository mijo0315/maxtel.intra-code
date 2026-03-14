<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Collection;
use DB;

class FaceTimeAuditExport implements WithHeadings, WithEvents
{
    protected $empId;
    protected $dateRange;
    protected $data;

    public function __construct($empId = null, $dateRange = null)
    {
        $this->empId = $empId;
        $this->dateRange = $dateRange;
        $this->data = $this->fetchData();
    }

    protected function fetchData()
    {
        $query = DB::connection("intra_payroll")->table("tbl_face_time_audit");

        if ($this->empId && $this->empId != '0') {
            $query->where("emp_id", $this->empId);
        }

        if ($this->dateRange) {
            [$start_date, $end_date] = explode(' - ', $this->dateRange);
            $query->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        return $query->orderBy("emp_id", "ASC")
            ->orderBy("created_at", "DESC")
            ->limit(2000) // Reasonable limit for image embedding
            ->get();
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Image',
            'State',
            'Date/Time',
            'Location'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                set_time_limit(300); // Increase timeout for this operation
                
                $sheet = $event->sheet->getDelegate();
                $rowNum = 2;
                $currentEmpId = null;

                foreach ($this->data as $record) {
                    // Add employee section header
                    if ($record->emp_id != $currentEmpId) {
                        if ($currentEmpId !== null) {
                            $rowNum += 1; // Add spacing between employees
                        }

                        $currentEmpId = $record->emp_id;

                        // Get employee name
                        $employee = DB::connection("intra_payroll")->table("tbl_employee")
                            ->where("emp_code", $record->emp_id)
                            ->first();

                        $empName = $employee ? 
                            $employee->last_name . ', ' . $employee->first_name . ' ' . $employee->middle_name 
                            : 'Employee ' . $record->emp_id;

                        // Employee header
                        $sheet->setCellValue('A' . $rowNum, $empName);
                        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)
                            ->getFont()
                            ->setBold(true)
                            ->setSize(12);
                        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
                        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)
                            ->getFont()
                            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

                        $rowNum++;
                    }

                    // Set row data
                    $sheet->setCellValue('A' . $rowNum, $record->emp_id);
                    $sheet->setCellValue('C' . $rowNum, $this->getStateButton($record->state));
                    $sheet->setCellValue('D' . $rowNum, date('Y-m-d H:i:s', strtotime($record->created_at)));
                    $sheet->setCellValue('E' . $rowNum, $record->location ?? 'N/A');

                    // Set row height for image
                    $sheet->getRowDimension($rowNum)->setRowHeight(100);

                    // Add image if available
                    if (!empty($record->image)) {
                        try {
                            // Construct full image URL
                            $imageUrl = $record->image;
                            if (strpos($imageUrl, 'http') !== 0) {
                                $imageUrl = 'https://maxtel-face.intra-code.com/' . ltrim($imageUrl, '/');
                            }

                            $imagePath = $this->downloadAndCacheImage($imageUrl, $record->emp_id . '_' . $record->id);

                            if ($imagePath && file_exists($imagePath)) {
                                $drawing = new Drawing();
                                $drawing->setName('Image_' . $record->id);
                                $drawing->setDescription('Face Time Audit Image');
                                $drawing->setPath($imagePath);
                                $drawing->setHeight(95);
                                $drawing->setWidth(120);
                                $drawing->setCoordinates('B' . $rowNum);
                                $drawing->setWorksheet($sheet);
                            } else {
                                $sheet->setCellValue('B' . $rowNum, 'Image error');
                            }
                        } catch (\Exception $e) {
                            $sheet->setCellValue('B' . $rowNum, 'Error loading');
                        }
                    } else {
                        $sheet->setCellValue('B' . $rowNum, 'N/A');
                    }

                    $rowNum++;
                }

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(25);

                // Freeze the header row
                $sheet->freezePane('A2');
            },
        ];
    }

    private function downloadAndCacheImage($imageUrl, $imageName)
    {
        try {
            $tempDir = storage_path('temp_images');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $imagePath = $tempDir . '/' . $imageName . '.jpg';

            // Only download if not already cached
            if (!file_exists($imagePath)) {
                // Use cURL for better timeout control
                $ch = curl_init($imageUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout per image
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                
                $imageContent = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($imageContent !== false && $httpCode === 200) {
                    file_put_contents($imagePath, $imageContent);
                } else {
                    return null;
                }
            }

            return $imagePath;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getStateButton($state)
    {
        $stateLabels = [
            'AM_IN' => 'AM IN',
            'AM_OUT' => 'AM OUT',
            'PM_IN' => 'PM IN',
            'PM_OUT' => 'PM OUT',
            'OT_IN' => 'OT IN',
            'OT_OUT' => 'OT OUT',
            'FLEX_IN' => 'START TIME',
            'FLEX_OUT' => 'END TIME',
        ];

        return $stateLabels[$state] ?? 'UNKNOWN';
    }
}

