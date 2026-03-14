<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NteNote extends Model
{
    use HasFactory;

    protected $table = 'nte_notes';
    protected $guarded = [];

    // Cast date_served to Carbon instance
    protected $casts = [
        'date_served' => 'datetime',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class, 'incident_report_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(NteNote::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(NteNote::class, 'parent_id', 'id');
    }

    /**
     * Generate document number in format NTE-YYYY-NNNN
     * Example: NTE-2026-0001
     */
    public static function generateDocumentNumber()
    {
        $year = date('Y');
        $prefix = "NTE-{$year}-";
        
        $lastNte = self::where('document_number', 'like', $prefix . '%')
            ->whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastNte && preg_match('/NTE-' . $year . '-(\d+)/', $lastNte->document_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
