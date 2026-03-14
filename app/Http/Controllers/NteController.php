<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\NteNote;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NteController extends Controller
{
    public function index()
    {
        try {
            // Get user permission for this page
            $routeName = 'nte_management';
            $userAccess = Auth::user()->access[$routeName] ?? null;
            $userPermission = $userAccess['access'] ?? null;
            
            // Check if user has read-only permission (value 3 = "R" only)
            $isReadOnly = $userPermission === '3' || (preg_match("/R/i", $userPermission ?? '') && !preg_match("/C|U/i", $userPermission ?? ''));
            
            // Check if user can create NTE (role_id 2 cannot create)
            $canCreateNte = auth()->user()->role_id != 2;
            
            // Get current employee
            $employee = Employee::where('user_id', auth()->id())->first();
            $role_id = auth()->user()->role_id;
            
            // Determine which employees to display for creation and which notes to show
            $employees = [];
            $nteNotesQuery = NteNote::with(['employee', 'replies.employee'])->whereNull('parent_id');
            
            if ($canCreateNte && !$isReadOnly) {
                if ($role_id == 1) {
                    // Admin sees all employees and all notes
                    $employees = Employee::all();
                    $nteNotes = $nteNotesQuery->latest()->get();
                } else {
                    // HR managers see employees in their assigned groups
                    $assignedGroups = [];
                    $query = Employee::query();
                    
                    if ($role_id === 4) { // HR Group D
                        $assignedGroups = ["group_d"];
                        $query->where(function ($q) {
                            $q->where("hr_group", "group_d")
                            ->orWhere("user_id", Auth::user()->id);
                        });
                    } elseif ($role_id === 5) { // HR Group B,C,E
                        $assignedGroups = ["group_b", "group_c", "group_e"];
                        $query->where(function ($q) {
                            $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                            ->orWhere("user_id", Auth::user()->id);
                        });
                    } elseif ($role_id === 14) { // HR Group B,C
                        $assignedGroups = ["group_b", "group_c"];
                        $query->where(function ($q) {
                            $q->whereIn("hr_group", ["group_b","group_c"])
                            ->orWhere("user_id", Auth::user()->id);
                        });
                    } elseif ($role_id === 15) { // HR Group C,E
                        $assignedGroups = ["group_c", "group_e"];
                        $query->where(function ($q) {
                            $q->whereIn("hr_group", ["group_c","group_e"])
                            ->orWhere("user_id", Auth::user()->id);
                        });
                    }
                    
                    $employees = $query->where('is_active', 1)->get();
                    
                    // Filter NTE notes to only show those for employees in assigned groups
                    $nteNotes = $nteNotesQuery->whereHas('employee', function ($q) use ($assignedGroups) {
                        $q->whereIn("hr_group", $assignedGroups);
                    })->orWhereHas('employee', function ($q) {
                        $q->where("user_id", Auth::user()->id);
                    })->latest()->get();
                }
            } else {
                // Staff (role_id 2) or read-only users see only their own notes
                $employees = [];
                $nteNotes = $employee ? NteNote::where('employee_id', $employee->id)->whereNull('parent_id')->with(['employee', 'replies.employee'])->latest()->get() : collect([]);
            }
            
            return view('nte_management.index', compact('employees', 'nteNotes', 'isReadOnly', 'canCreateNte'));
        } catch (\Throwable $e) {
            \Log::error('NTE index render error: '.$e->getMessage(), ['exception' => $e]);
            return response('Server error rendering NTE page', 500);
        }
    }

    public function show($id)
    {
        try {
            $nteNote = NteNote::find($id);
            
            if (!$nteNote) {
                return response()->json(['error' => 'NTE not found'], 404);
            }
            
            // Get employee details
            $employee = $nteNote->employee;
            
            return response()->json([
                'id' => $nteNote->id,
                'document_number' => $nteNote->document_number ?? 'N/A',
                'employee_name' => $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'N/A',
                'case_details' => $nteNote->case_details,
                'remarks' => $nteNote->remarks,
                'date_served' => $nteNote->date_served,
                'due_date' => $nteNote->due_date,
                'resolution' => $nteNote->resolution,
                'attachment_path' => $nteNote->attachment_path ? asset($nteNote->attachment_path) : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching NTE: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching NTE details'], 500);
        }
    }

    public function store(Request $request)
    {
        // Restrict role_id 2 from creating NTEs
        if (auth()->user()->role_id == 2) {
            return back()->with('error', 'You do not have permission to create NTE notes.');
        }

        $request->validate([
            'incident_report_id' => 'nullable|exists:incident_reports,id',
            'employee_id' => 'required|exists:tbl_employee,id',
            'case_details' => 'required|string',
            'remarks' => 'required|string',
            'date_served' => 'required|date',
            'due_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:10240',
            'resolution' => 'nullable|string',
        ]);

        $data = $request->except('attachment');
        $data['date_served'] = date('Y-m-d', strtotime($request->date_served));
        if ($request->has('due_date') && $request->due_date) {
            $data['due_date'] = date('Y-m-d', strtotime($request->due_date));
        }

        $nte = new NteNote($data);
        // Generate document number
        $nte->document_number = NteNote::generateDocumentNumber();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $storagePath = 'uploads/nte/';
            if (!file_exists(public_path($storagePath))) { @mkdir(public_path($storagePath), 0755, true); }
            $file->move(public_path($storagePath), $fileName);
            $nte->attachment_path = $storagePath . $fileName;
        }

        try {
            $nte->save();
            return redirect()->route('nte_management')->with('success', 'NTE created successfully with Document No: ' . $nte->document_number);
        } catch (\Exception $e) {
            Log::error('Error creating NTE: ' . $e->getMessage());
            return back()->with('error', 'Error creating NTE: ' . $e->getMessage())->withInput();
        }
    }

    public function reply(Request $request)
    {
        Log::debug('NTE reply request: ' . json_encode($request->all()));
        $request->validate([
            'parent_id' => 'required|exists:nte_notes,id',
            'remarks' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $parent = NteNote::find($request->parent_id);
        if (!$parent) {
            return back()->with('error', 'Parent NTE not found.');
        }

        $employee = Employee::where('user_id', auth()->id())->first();
        $data = [
            'employee_id' => $employee ? $employee->id : null,
            'case_details' => 'Reply to: ' . substr($parent->case_details, 0, 100),
            'remarks' => $request->remarks,
            'date_served' => now()->toDateString(),
            'parent_id' => $parent->id,
        ];

        $reply = new NteNote($data);
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $storagePath = 'uploads/nte/';
            if (!file_exists(public_path($storagePath))) { @mkdir(public_path($storagePath), 0755, true); }
            $file->move(public_path($storagePath), $fileName);
            $reply->attachment_path = $storagePath . $fileName;
        }

        try {
            $reply->save();
            Log::info('NTE reply saved', ['id' => $reply->id]);
            return redirect()->route('nte_management')->with('success', 'Reply saved.');
        } catch (\Exception $e) {
            Log::error('Error saving NTE reply: ' . $e->getMessage());
            return back()->with('error', 'Error saving reply: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        // Only admins can update
        if (auth()->user()->role_id != 1) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'case_details' => 'required|string',
            'remarks' => 'required|string',
            'date_served' => 'required|date',
            'due_date' => 'nullable|date',
            'resolution' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        try {
            $nte = NteNote::findOrFail($id);

            $nte->case_details = $request->case_details;
            $nte->remarks = $request->remarks;
            $nte->date_served = date('Y-m-d', strtotime($request->date_served));
            $nte->due_date = $request->due_date ? date('Y-m-d', strtotime($request->due_date)) : null;
            $nte->resolution = $request->resolution;

            // Handle attachment upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($nte->attachment_path && file_exists(public_path($nte->attachment_path))) {
                    @unlink(public_path($nte->attachment_path));
                }

                $file = $request->file('attachment');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $storagePath = 'uploads/nte/';
                if (!file_exists(public_path($storagePath))) { @mkdir(public_path($storagePath), 0755, true); }
                $file->move(public_path($storagePath), $fileName);
                $nte->attachment_path = $storagePath . $fileName;
            }

            $nte->save();
            Log::info('NTE updated', ['id' => $id, 'updated_by' => auth()->id()]);
            return redirect()->route('nte_management')->with('success', 'NTE updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating NTE: ' . $e->getMessage());
            return back()->with('error', 'Error updating NTE: ' . $e->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        try {
            // Only admins can delete
            if (auth()->user()->role_id != 1) {
                return back()->with('error', 'Unauthorized action.');
            }

            $nte = NteNote::findOrFail($id);
            
            // Delete attachment file if exists
            if ($nte->attachment_path && file_exists(public_path($nte->attachment_path))) {
                @unlink(public_path($nte->attachment_path));
            }

            // Delete all replies and their attachments
            $replies = NteNote::where('parent_id', $id)->get();
            foreach ($replies as $reply) {
                if ($reply->attachment_path && file_exists(public_path($reply->attachment_path))) {
                    @unlink(public_path($reply->attachment_path));
                }
                $reply->delete();
            }

            // Delete the main NTE
            $nte->delete();

            Log::info('NTE deleted', ['id' => $id, 'deleted_by' => auth()->id()]);
            return redirect()->route('nte_management')->with('success', 'NTE and all its replies deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting NTE: ' . $e->getMessage());
            return back()->with('error', 'Error deleting NTE: ' . $e->getMessage());
        }
    }

    public function export()
    {
        try {
            // Get notes based on user role
            $employee = Employee::where('user_id', auth()->id())->first();
            if (auth()->user()->role_id == 1) {
                $nteNotes = NteNote::with(['employee'])->whereNull('parent_id')->latest()->get();
            } else {
                $nteNotes = $employee ? NteNote::where('employee_id', $employee->id)->whereNull('parent_id')->with(['employee'])->latest()->get() : collect([]);
            }

            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('NTE Notes');

            // Set headers
            $headers = ['Employee', 'Case Details', 'Remarks', 'Date Served', 'Due Date', 'Attachment'];
            $sheet->fromArray($headers, null, 'A1');

            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2f47ba']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ];
            foreach (range('A', 'F') as $column) {
                $sheet->getStyle($column . '1')->applyFromArray($headerStyle);
            }

            // Add data
            $row = 2;
            foreach ($nteNotes as $note) {
                $sheet->setCellValue('A' . $row, $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A');
                $sheet->setCellValue('B' . $row, $note->case_details);
                $sheet->setCellValue('C' . $row, $note->remarks);
                $sheet->setCellValue('D' . $row, $note->date_served ? $note->date_served->format('M d, Y') : '');
                $sheet->setCellValue('E' . $row, $note->due_date ? $note->due_date->format('M d, Y') : '');
                
                // Show attachment file name or path
                if ($note->attachment_path) {
                    $attachmentName = basename($note->attachment_path);
                    $sheet->setCellValue('F' . $row, $attachmentName);
                } else {
                    $sheet->setCellValue('F' . $row, 'No');
                }
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Create Excel file and download
            $writer = new Xlsx($spreadsheet);
            $fileName = 'NTE_Notes_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            // Write to temporary file
            $tempPath = storage_path('app/temp/' . $fileName);
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            $writer->save($tempPath);

            // Download and delete
            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting NTE notes: ' . $e->getMessage());
            return back()->with('error', 'Error exporting NTE notes: ' . $e->getMessage());
        }
    }
}