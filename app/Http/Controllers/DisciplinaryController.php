<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryNote;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DisciplinaryController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get user permission for this page
            $routeName = 'disciplinary_management';
            $userAccess = Auth::user()->access[$routeName] ?? null;
            $userPermission = $userAccess['access'] ?? null;
            
            // Check if user has read-only permission (value 3 = "R" only)
            $isReadOnly = $userPermission === '3' || (preg_match("/R/i", $userPermission ?? '') && !preg_match("/C|U/i", $userPermission ?? ''));
            
            // Get search query from request
            $search = $request->input('search', '');
            
            // Get employee ID from the logged-in user
            $employee = Employee::where('user_id', auth()->id())->first();
            $role_id = Auth::user()->role_id;
            
            if (auth()->user()->role_id == 1 && !$isReadOnly) {
                // Admin view (non-read-only) - show all parent notes and employees
                $employees = Employee::all();
                $noteQuery = DisciplinaryNote::whereNull('parent_id')->with(['employee', 'replies.employee'])->whereNotNull('employee_id');
                
                // Apply search filter
                if ($search) {
                    $noteQuery->where(function ($q) use ($search) {
                        $q->where('document_number', 'LIKE', "%$search%")
                          ->orWhere('case_details', 'LIKE', "%$search%")
                          ->orWhereHas('employee', function ($eq) use ($search) {
                              $eq->where('first_name', 'LIKE', "%$search%")
                                ->orWhere('last_name', 'LIKE', "%$search%");
                          });
                    });
                }
                
                $disciplinaryNotes = $noteQuery->latest()->paginate(15);
            } elseif (!$isReadOnly) {
                // HR Managers: filter employees and notes based on role-based groups
                $query = Employee::where('is_active', 1);
                $noteQuery = DisciplinaryNote::with(['employee', 'replies.employee'])->whereNull('parent_id')->whereNotNull('employee_id');
                
                if ($role_id === 4) { // HR Group D
                    $employees = $query->where(function ($q) {
                        $q->where("hr_group", "group_d")
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $noteQuery->whereIn('employee_id', $employees->pluck('id'));
                } elseif ($role_id === 5) { // HR Group B,C,E
                    $employees = $query->where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $noteQuery->whereIn('employee_id', $employees->pluck('id'));
                } elseif ($role_id === 14) { // HR Group B,C
                    $employees = $query->where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $noteQuery->whereIn('employee_id', $employees->pluck('id'));
                } elseif ($role_id === 15) { // HR Group C,E
                    $employees = $query->where(function ($q) {
                        $q->whereIn("hr_group", ["group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->get();
                    $noteQuery->whereIn('employee_id', $employees->pluck('id'));
                } else {
                    // For other roles, only show their own notes
                    $employees = [];
                    if ($employee) {
                        $noteQuery->where('employee_id', $employee->id);
                    }
                }
                
                // Apply search filter
                if ($search) {
                    $noteQuery->where(function ($q) use ($search) {
                        $q->where('document_number', 'LIKE', "%$search%")
                          ->orWhere('case_details', 'LIKE', "%$search%")
                          ->orWhereHas('employee', function ($eq) use ($search) {
                              $eq->where('first_name', 'LIKE', "%$search%")
                                ->orWhere('last_name', 'LIKE', "%$search%");
                          });
                    });
                }
                
                $disciplinaryNotes = $noteQuery->latest()->paginate(15);
            } else {
                // Read-only staff or employee view - only show their own parent notes
                $employees = [];
                if ($employee) {
                    $noteQuery = DisciplinaryNote::where('employee_id', $employee->id)->whereNull('parent_id')->whereNotNull('employee_id')->with(['employee', 'replies.employee']);
                    
                    // Apply search filter
                    if ($search) {
                        $noteQuery->where(function ($q) use ($search) {
                            $q->where('document_number', 'LIKE', "%$search%")
                              ->orWhere('case_details', 'LIKE', "%$search%");
                        });
                    }
                    
                    $disciplinaryNotes = $noteQuery->latest()->paginate(15);
                } else {
                    $disciplinaryNotes = collect([]);
                }
            }
            
            return view('disciplinary.index', compact('employees', 'disciplinaryNotes', 'isReadOnly', 'search'));
        } catch (\Exception $e) {
            \Log::error('Error in disciplinary index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:tbl_employee,id',
            'nte_note_id' => 'nullable|exists:nte_notes,id',
            'case_details' => 'required|string',
            'remarks' => 'required|string',
            'date_served' => 'required|date',
            'sanction' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max file size
        ]);

        $data = $request->except('attachment');
        $data['date_served'] = date('Y-m-d', strtotime($request->date_served));
        
        $disciplinaryNote = new DisciplinaryNote($data);
        // Generate document number
        $disciplinaryNote->document_number = DisciplinaryNote::generateDocumentNumber();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Check environment to determine storage path
            if (env('APP_ENV') === 'local') {
                // Local environment - store in public path
                $storagePath = 'uploads/disciplinary/';
                $file->move(public_path($storagePath), $fileName);
                $disciplinaryNote->attachment_path = $storagePath . $fileName;
            } else {
                // Production environment - store in FTP path
                $storagePath = 'public/uploads/disciplinary/';
                $file->move($storagePath, $fileName);
                $disciplinaryNote->attachment_path = str_replace('public/', '', $storagePath) . $fileName;
            }
        }

        try {
            $disciplinaryNote->save();
            return redirect()->route('disciplinary')->with('success', 'Disciplinary note created successfully with Document No: ' . $disciplinaryNote->document_number);
        } catch (\Exception $e) {
            Log::error('Error creating disciplinary note: ' . $e->getMessage());
            return redirect()->route('disciplinary')->with('error', 'Error creating disciplinary note: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store an employee reply to a disciplinary note (creates a new row with parent_id)
     */
    public function reply(Request $request)
    {
        \Log::debug('Disciplinary reply request data: ' . json_encode($request->all()));
        $request->validate([
            'parent_id' => 'required|exists:disciplinary_notes,id',
            'remarks' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $parent = DisciplinaryNote::find($request->parent_id);
        if (!$parent) {
            return back()->with('error', 'Parent disciplinary note not found.');
        }

        // determine employee id for the logged-in user
        $employee = Employee::where('user_id', auth()->id())->first();
        $employeeId = $employee ? $employee->id : null;

        $data = [
            'employee_id' => $employeeId,
            'case_details' => 'Reply to: ' . substr($parent->case_details, 0, 100),
            'remarks' => $request->remarks,
            'sanction' => $parent->sanction,
            'date_served' => now()->toDateString(),
            'parent_id' => $parent->id,
        ];

        $reply = new DisciplinaryNote($data);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $storagePath = 'uploads/disciplinary/';
            // ensure directory exists
            if (!file_exists(public_path($storagePath))) {
                @mkdir(public_path($storagePath), 0755, true);
            }
            $file->move(public_path($storagePath), $fileName);
            $reply->attachment_path = $storagePath . $fileName;
        }

        try {
            $reply->save();
            \Log::info('Disciplinary reply saved', ['reply_id' => $reply->id, 'parent_id' => $reply->parent_id, 'employee_id' => $reply->employee_id]);
            return redirect()->route('disciplinary')->with('success', 'Reply posted successfully.');
        } catch (\Exception $e) {
            if ($request->hasFile('attachment') && isset($fileName)) {
                @unlink(public_path('uploads/disciplinary/' . $fileName));
            }
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

            $disciplinaryNote = DisciplinaryNote::findOrFail($id);
            
            // Delete attachment file if exists
            if ($disciplinaryNote->attachment_path && file_exists(public_path($disciplinaryNote->attachment_path))) {
                @unlink(public_path($disciplinaryNote->attachment_path));
            }

            // Delete all replies and their attachments
            $replies = DisciplinaryNote::where('parent_id', $id)->get();
            foreach ($replies as $reply) {
                if ($reply->attachment_path && file_exists(public_path($reply->attachment_path))) {
                    @unlink(public_path($reply->attachment_path));
                }
                $reply->delete();
            }

            // Delete the main disciplinary note
            $disciplinaryNote->delete();

            \Log::info('Disciplinary note deleted', ['id' => $id, 'deleted_by' => auth()->id()]);
            return redirect()->route('disciplinary')->with('success', 'Disciplinary note and all its replies deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting disciplinary note: ' . $e->getMessage());
            return back()->with('error', 'Error deleting disciplinary note: ' . $e->getMessage());
        }
    }

    public function export()
    {
        try {
            $employee = Employee::where('user_id', auth()->id())->first();
            $role_id = Auth::user()->role_id;
            
            if (auth()->user()->role_id == 1) {
                // Admin exports all notes
                $disciplinaryNotes = DisciplinaryNote::whereNull('parent_id')->with(['employee'])->latest()->get();
            } else {
                // HR Managers: filter notes based on role-based groups
                $noteQuery = DisciplinaryNote::with(['employee'])->whereNull('parent_id');
                
                if ($role_id === 4) { // HR Group D
                    $employees = Employee::where(function ($q) {
                        $q->where("hr_group", "group_d")
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $disciplinaryNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } elseif ($role_id === 5) { // HR Group B,C,E
                    $employees = Employee::where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $disciplinaryNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } elseif ($role_id === 14) { // HR Group B,C
                    $employees = Employee::where(function ($q) {
                        $q->whereIn("hr_group", ["group_b","group_c"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $disciplinaryNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } elseif ($role_id === 15) { // HR Group C,E
                    $employees = Employee::where(function ($q) {
                        $q->whereIn("hr_group", ["group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    })->pluck('id');
                    $disciplinaryNotes = $noteQuery->whereIn('employee_id', $employees)->latest()->get();
                } else {
                    // For other roles, only export their own notes
                    $disciplinaryNotes = $employee ? 
                        DisciplinaryNote::where('employee_id', $employee->id)->whereNull('parent_id')->with(['employee'])->latest()->get() :
                        collect([]);
                }
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Disciplinary Notes');

            $headers = ['Employee', 'Case Details', 'Remarks', 'Date Served', 'Sanction', 'Attachment'];
            $sheet->fromArray($headers, null, 'A1');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2f47ba']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ];
            foreach (range('A', 'F') as $column) {
                $sheet->getStyle($column . '1')->applyFromArray($headerStyle);
            }

            $row = 2;
            foreach ($disciplinaryNotes as $note) {
                $sheet->setCellValue('A' . $row, $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A');
                $sheet->setCellValue('B' . $row, $note->case_details);
                $sheet->setCellValue('C' . $row, $note->remarks);
                $sheet->setCellValue('D' . $row, $note->date_served ? $note->date_served->format('M d, Y') : '');
                $sheet->setCellValue('E' . $row, $note->sanction ? ucfirst(str_replace('_', ' ', $note->sanction)) : '-');
                
                if ($note->attachment_path) {
                    $attachmentName = basename($note->attachment_path);
                    $sheet->setCellValue('F' . $row, $attachmentName);
                } else {
                    $sheet->setCellValue('F' . $row, 'No');
                }
                $row++;
            }

            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Disciplinary_Notes_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            $tempPath = storage_path('app/temp/' . $fileName);
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            $writer->save($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error exporting disciplinary notes: ' . $e->getMessage());
            return back()->with('error', 'Error exporting disciplinary notes: ' . $e->getMessage());
        }
    }
}