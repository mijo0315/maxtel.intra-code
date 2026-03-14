<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_by',
        'position',
        'date_time_report',
        'incident_no',
        'document_number',
        'incident_type',
        'date_incident',
        'location',
        'incident_description',
        'name_involved',
        'name_witness',
        'recommended_action',
        'disciplinary_note_id',
    ];

    protected $casts = [
        'date_time_report' => 'datetime',
        'date_incident' => 'datetime',
    ];

    // Relationships
    public function reportedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'reported_by', 'id');
    }

    // Get all involved employees from JSON
    public function getInvolvedEmployees()
    {
        if (!$this->name_involved) {
            return collect();
        }
        
        $involvedIds = json_decode($this->name_involved, true);
        if (!is_array($involvedIds)) {
            return collect();
        }
        
        return Employee::whereIn('id', $involvedIds)->get();
    }

    // Get all witness employees from JSON
    public function getWitnessEmployees()
    {
        if (!$this->name_witness) {
            return collect();
        }
        
        $witnessIds = json_decode($this->name_witness, true);
        if (!is_array($witnessIds)) {
            return collect();
        }
        
        return Employee::whereIn('id', $witnessIds)->get();
    }

    // Link to disciplinary note
    public function disciplinaryNote()
    {
        return $this->belongsTo(DisciplinaryNote::class, 'disciplinary_note_id', 'id');
    }

    // Link to NTE notes created from this incident report
    public function nteNotes()
    {
        return $this->hasMany(NteNote::class, 'incident_report_id', 'id')->whereNull('parent_id');
    }

    /**
     * Generate document number in format IR-YYYY-NNNN
     * Example: IR-2026-0001
     */
    public static function generateDocumentNumber()
    {
        $year = date('Y');
        $prefix = "IR-{$year}-";
        
        $lastReport = self::where('document_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReport && preg_match('/IR-' . $year . '-(\d+)/', $lastReport->document_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
