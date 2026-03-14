<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaryNote extends Model
{
    use HasFactory;

    protected $table = 'disciplinary_notes';

    protected $fillable = [
        'employee_id',
        'nte_note_id',
        'case_details',
        'remarks',
        'date_served',
        'sanction',
        'attachment_path',
        'document_number',
        'parent_id'
    ];

    protected $casts = [
        'date_served' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function nteNote()
    {
        return $this->belongsTo(NteNote::class, 'nte_note_id', 'id');
    }

    // Get all replies/children to this disciplinary note
    public function replies()
    {
        return $this->hasMany(DisciplinaryNote::class, 'parent_id', 'id');
    }

    // Get the parent disciplinary note if this is a reply
    public function parent()
    {
        return $this->belongsTo(DisciplinaryNote::class, 'parent_id', 'id');
    }

    // Get the linked incident report
    public function incidentReport()
    {
        return $this->hasOne(IncidentReport::class, 'disciplinary_note_id', 'id');
    }

    /**
     * Generate document number in format DR-YYYY-NNNN
     * Example: DR-2026-0001
     */
    public static function generateDocumentNumber()
    {
        $year = date('Y');
        $prefix = "DR-{$year}-";
        
        $lastDr = self::where('document_number', 'like', $prefix . '%')
            ->whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDr && preg_match('/DR-' . $year . '-(\d+)/', $lastDr->document_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
