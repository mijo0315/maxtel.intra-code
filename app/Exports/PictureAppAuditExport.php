<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Collection;
use DB;
use DateTime;
use DateTimeZone;

class PictureAppAuditExport implements WithHeadings, WithEvents
{
    protected $bioId;
    protected $dateRange;
    protected $data;

    public function __construct($bioId = null, $dateRange = null)
    {
        $this->bioId = $bioId;
        $this->dateRange = $dateRange;
        $this->data = $this->fetchData();
    }

    protected function fetchData()
    {
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
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Image',
            'Remarks',
            'Server Timestamp',
            'Location'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                set_time_limit(0); // Unlimited execution time for export
                
                $sheet = $event->sheet->getDelegate();
                $rowNum = 2;
                $currentBioId = null;

                foreach ($this->data as $record) {
                    // Add employee section header
                    if ($record->biometric_id != $currentBioId) {
                        if ($currentBioId !== null) {
                            $rowNum += 1;
                        }

                        $currentBioId = $record->biometric_id;

                        // Get employee name
                        $employee = DB::connection("intra_payroll")->table("tbl_employee")
                            ->where("bio_id", $record->biometric_id)
                            ->first();

                        $empName = $employee ? 
                            $employee->last_name . ', ' . $employee->first_name . ' ' . $employee->middle_name 
                            : 'Employee ' . $record->biometric_id;

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
                    $sheet->setCellValue('A' . $rowNum, $record->biometric_id);
                    $sheet->setCellValue('C' . $rowNum, $record->remarks ?? 'N/A');
                    
                    // Format timestamp
                    if (!empty($record->phone_timestamp) && is_numeric($record->phone_timestamp)) {
                        $formattedTime = (new DateTime('@' . $record->phone_timestamp))
                            ->setTimezone(new DateTimeZone('Asia/Manila'))
                            ->format('Y-m-d H:i:s');
                    } else {
                        $formattedTime = 'N/A';
                    }
                    
                    $sheet->setCellValue('D' . $rowNum, $formattedTime);
                    $sheet->setCellValue('E' . $rowNum, $record->location ?? 'No Data');

                    // Set row height for image
                    $sheet->getRowDimension($rowNum)->setRowHeight(100);

                    // Add image if available
                    if (!empty($record->image_url)) {
                        try {
                            // Construct full image URL
                            $imageUrl = $record->image_url;
                            if (strpos($imageUrl, 'http') !== 0) {
                                $imageUrl = 'https://maxtel-face.intra-code.com/' . ltrim($imageUrl, '/');
                            }

                            $imagePath = $this->downloadAndCacheImage($imageUrl, $record->biometric_id . '_' . $record->id);

                            if ($imagePath && file_exists($imagePath)) {
                                $drawing = new Drawing();
                                $drawing->setName('Image_' . $record->id);
                                $drawing->setDescription('Picture App Audit Image');
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
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Increased timeout to 30 seconds per image
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                
                $imageContent = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($imageContent !== false && $httpCode === 200) {
                    file_put_contents($imagePath, $imageContent);
                } else {
                    // Log the error but don't fail the entire export
                    \Log::warning("Failed to download image: $imageUrl - HTTP Code: $httpCode - cURL Error: $curlError");
                    return null;
                }
            }

            return $imagePath;
        } catch (\Exception $e) {
            \Log::warning("Image download exception for $imageUrl: " . $e->getMessage());
            return null;
        }
    }
}
