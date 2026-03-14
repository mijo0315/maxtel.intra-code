<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PerformanceImprovementNote;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PerformanceImprovementController extends Controller
{
    public function index()
    {
        try {
            // Get user permission for this page
            $routeName = 'performance_management';
            $userAccess = Auth::user()->access[$routeName] ?? null;
            $userPermission = $userAccess['access'] ?? null;
            
            // Check if user has read-only permission (value 3 = "R" only)
            $isReadOnly = $userPermission === '3' || (preg_match("/R/i", $userPermission ?? '') && !preg_match("/C|U/i", $userPermission ?? ''));

            // Get current user's employee record
            $employee = Employee::where('user_id', Auth::id())->first();
            $role_id = Auth::user()->role_id;
            
            // Determine what data to show based on role and permissions
            if (Auth::user()->role_id == 1) {
                // Admin: show all employees and all notes (unless read-only)
                $employees = $isReadOnly ? [] : Employee::all();
                $piNotes = PerformanceImprovementNote::with(['employee', 'replies.employee'])->whereNull('parent_id')->latest()->get();
            } elseif (!$isReadOnly) {
                // HR Managers: filter employees and notes based on role-based groups
                $query = Employee::where('is_active', 1);
                $noteQuery = PerformanceImprovementNote::with(['employee', 'replies.employee'])->whereNull('parent_id');
                
                if ($role_id === 4) { // HR Group D
                    $employees = $query->where(function ($q) {
                        $q->where("hr_group", "group_d")
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $piNotes = $noteQuery->whereIn('employee_id', $employees->pluck('id'))->latest()->get();
                } elseif ($role_id === 5) { // HR Group B,C,E
                    $employees = $query->where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $piNotes = $noteQuery->whereIn('employee_id', $employees->pluck('id'))->latest()->get();
                } elseif ($role_id === 14) { // HR Group B,C
                    $employees = $query->where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $piNotes = $noteQuery->whereIn('employee_id', $employees->pluck('id'))->latest()->get();
                } elseif ($role_id === 15) { // HR Group C,E
                    $employees = $query->where(function ($q) {
                        $q->whereIn("hr_group", ["group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $piNotes = $noteQuery->whereIn('employee_id', $employees->pluck('id'))->latest()->get();
                } else {
                    // For other roles, only show their own notes
                    $employees = [];
                    $piNotes = $employee ? PerformanceImprovementNote::where('employee_id', $employee->id)->whereNull('parent_id')->with(['employee', 'replies.employee'])->latest()->get() : collect([]);
                }
            } else {
                // Staff/Employee or read-only users: show only their own notes
                $employees = [];
                $piNotes = $employee ? PerformanceImprovementNote::where('employee_id', $employee->id)->whereNull('parent_id')->with(['employee', 'replies.employee'])->latest()->get() : collect([]);
            }
            
            return view('performance_improvement.index', compact('employees', 'piNotes', 'isReadOnly'));
        } catch (\Throwable $e) {
            Log::error('Performance Improvement index render error: '.$e->getMessage(), ['exception' => $e]);
            return response('Server error rendering Performance Improvement page', 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:tbl_employee,id',
            'case_details' => 'required|string',
            'remarks' => 'required|string',
            'date_served' => 'required|date',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $data = $request->except('attachment');
        $data['date_served'] = date('Y-m-d', strtotime($request->date_served));

        $note = new PerformanceImprovementNote($data);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $storagePath = 'uploads/performance_improvement/';
            if (!file_exists(public_path($storagePath))) { @mkdir(public_path($storagePath), 0755, true); }
            $file->move(public_path($storagePath), $fileName);
            $note->attachment_path = $storagePath . $fileName;
        }

        try {
            $note->save();
            return redirect()->route('performance_improvement')->with('success', 'Performance Improvement note created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating Performance Improvement note: ' . $e->getMessage());
            return back()->with('error', 'Error creating Performance Improvement note: ' . $e->getMessage())->withInput();
        }
    }

    public function reply(Request $request)
    {
        Log::debug('Performance Improvement reply request: ' . json_encode($request->all()));
        $request->validate([
            'parent_id' => 'required|exists:performance_improvement_notes,id',
            'remarks' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $parent = PerformanceImprovementNote::find($request->parent_id);
        if (!$parent) {
            return back()->with('error', 'Parent Performance Improvement note not found.');
        }

        $employee = Employee::where('user_id', auth()->id())->first();
        $data = [
            'employee_id' => $employee ? $employee->id : null,
            'case_details' => 'Reply to: ' . substr($parent->case_details, 0, 100),
            'remarks' => $request->remarks,
            'date_served' => now()->toDateString(),
            'parent_id' => $parent->id,
        ];

        $reply = new PerformanceImprovementNote($data);
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $storagePath = 'uploads/performance_improvement/';
            if (!file_exists(public_path($storagePath))) { @mkdir(public_path($storagePath), 0755, true); }
            $file->move(public_path($storagePath), $fileName);
            $reply->attachment_path = $storagePath . $fileName;
        }

        try {
            $reply->save();
            Log::info('Performance Improvement reply saved', ['id' => $reply->id]);
            return redirect()->route('performance_improvement')->with('success', 'Reply saved.');
        } catch (\Exception $e) {
            Log::error('Error saving Performance Improvement reply: ' . $e->getMessage());
            return back()->with('error', 'Error saving reply: ' . $e->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        try {
            // Only admins can delete
            if (auth()->user()->role_id != 1) {
                return back()->with('error', 'Unauthorized action.');
            }

            $note = PerformanceImprovementNote::findOrFail($id);
            
            // Delete attachment file if exists
            if ($note->attachment_path && file_exists(public_path($note->attachment_path))) {
                @unlink(public_path($note->attachment_path));
            }

            // Delete all replies and their attachments
            $replies = PerformanceImprovementNote::where('parent_id', $id)->get();
            foreach ($replies as $reply) {
                if ($reply->attachment_path && file_exists(public_path($reply->attachment_path))) {
                    @unlink(public_path($reply->attachment_path));
                }
                $reply->delete();
            }

            // Delete the main note
            $note->delete();

            Log::info('Performance Improvement note deleted', ['id' => $id, 'deleted_by' => auth()->id()]);
            return redirect()->route('performance_improvement')->with('success', 'Performance Improvement note and all its replies deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting Performance Improvement note: ' . $e->getMessage());
            return back()->with('error', 'Error deleting Performance Improvement note: ' . $e->getMessage());
        }
    }

    public function export()
    {
        try {
            // Get notes based on user role and role-based group management
            $employee = Employee::where('user_id', auth()->id())->first();
            $role_id = auth()->user()->role_id;
            
            if (auth()->user()->role_id == 1) {
                // Admin sees all notes
                $piNotes = PerformanceImprovementNote::with(['employee'])->whereNull('parent_id')->latest()->get();
            } else {
                // HR Managers: filter notes based on role-based groups
                $noteQuery = PerformanceImprovementNote::with(['employee'])->whereNull('parent_id');
                
                if ($role_id === 4) { // HR Group D
                    $employees = Employee::where(function ($q) {
                        $q->where("hr_group", "group_d")
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $piNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } elseif ($role_id === 5) { // HR Group B,C,E
                    $employees = Employee::where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $piNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } elseif ($role_id === 14) { // HR Group B,C
                    $employees = Employee::where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $piNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } elseif ($role_id === 15) { // HR Group C,E
                    $employees = Employee::where(function ($q) {
                        $q->whereIn("hr_group", ["group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $piNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } else {
                    // For other roles, only show their own notes
                    $piNotes = $employee ? PerformanceImprovementNote::where('employee_id', $employee->id)->whereNull('parent_id')->with(['employee'])->latest()->get() : collect([]);
                }
            }

            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('PI Notes');

            // Set headers
            $headers = ['Employee', 'Case Details', 'Remarks', 'Date Served', 'Attachment'];
            $sheet->fromArray($headers, null, 'A1');

            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2f47ba']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ];
            foreach (range('A', 'E') as $column) {
                $sheet->getStyle($column . '1')->applyFromArray($headerStyle);
            }

            // Add data
            $row = 2;
            foreach ($piNotes as $note) {
                $sheet->setCellValue('A' . $row, $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A');
                $sheet->setCellValue('B' . $row, $note->case_details);
                $sheet->setCellValue('C' . $row, $note->remarks);
                $sheet->setCellValue('D' . $row, $note->date_served ? $note->date_served->format('M d, Y') : '');
                
                if ($note->attachment_path) {
                    $attachmentName = basename($note->attachment_path);
                    $sheet->setCellValue('E' . $row, $attachmentName);
                } else {
                    $sheet->setCellValue('E' . $row, 'No');
                }
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Performance_Improvement_Notes_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            $tempPath = storage_path('app/temp/' . $fileName);
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            $writer->save($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting Performance Improvement notes: ' . $e->getMessage());
            return back()->with('error', 'Error exporting PI notes: ' . $e->getMessage());
        }
    }
}
