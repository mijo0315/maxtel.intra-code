<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;
class exportExcel implements FromCollection, WithHeadings
{

    protected $attribute1;
    protected $attribute2;

    public function __construct($data,$header)
    {
        $this->data = $data;
        $this->header = $header;
    }

    public function collection()
    {
       return $this->data;
    //    return $payroll = DB::connection("intra_payroll")->table("tbl_payroll")->where("id", $pay_id)->get();
    
    }

    public function headings(): array
    {
        return $this->header;
    }
}



?>