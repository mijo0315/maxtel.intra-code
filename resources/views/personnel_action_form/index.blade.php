@extends('layouts.front-app')
@section('title')
Personnel Action Form
@stop
@section("styles")
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card-header {
        transition: background-color 0.3s ease;
        background-color: #2f47ba;
        color: white;
    }
    .card-header:hover {
        background-color: #1f2f7a;
    }
    .badge-primary {
        background-color: #2f47ba;
    }
    .btn-outline-primary {
        color: #2f47ba;
        border-color: #2f47ba;
    }
    .btn-outline-primary:hover {
        background-color: #2f47ba;
        color: white;
    }
    .content {
        max-width: 95%;
        margin: 0 auto;
    }
    .table-responsive-sm {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive-sm::-webkit-scrollbar {
        height: 10px;
    }
    .table-responsive-sm::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .table-responsive-sm::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    .table-responsive-sm::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
    #detailsTable input,
    #detailsTable select {
        font-size: 0.55rem;
        padding: 0.08rem 0.12rem;
        height: 14px;
    }
    .badge {
        font-size: 0.55rem;
        padding: 0.1rem 0.25rem !important;
        display: inline-block;
    }
</style>
@stop
@section("content")
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Personnel Action Notice</h4>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error!</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(!$isReadOnly)
                        <!-- Form only visible to non-read-only users (admin/managers) -->
                        <form id="personnelActionForm" method="POST" action="{{ route('personnel_action.store') }}">
                            @csrf
                            
                            <!-- Employee Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employee_select" class="form-label">Employee <span class="text-danger">*</span></label>
                                        <select class="form-control form-select" id="employee_select" required>
                                            <option value="">-- Select Employee --</option>
                                            @forelse($employees ?? [] as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->emp_code }} - {{ $employee->first_name }} {{ $employee->last_name }}
                                                </option>
                                            @empty
                                                <option value="">No employees available</option>
                                            @endforelse
                                        </select>
                                        <input type="hidden" id="employee_id" name="employee_id">
                                        @error('employee_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="action_type_select" class="form-label">Action Type <span class="text-danger">*</span></label>
                                        <select class="form-control form-select" id="action_type_select" required>
                                            <option value="">-- Select Action --</option>
                                            <option value="employment">Employment</option>
                                            <option value="regularization">Regularization</option>
                                            <option value="general_increase">General Increase</option>
                                            <option value="change_emp_status">Change in Employment Status</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="merit_increase">Merit Increase</option>
                                            <option value="change_position">Change in Position</option>
                                            <option value="promotion">Promotion</option>
                                            <option value="reclassification">Reclassification</option>
                                            <option value="other">Other (Specify)</option>
                                        </select>
                                        <input type="hidden" id="action_type" name="action_type">
                                        @error('action_type')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Auto-filled Employee Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position" class="form-label">Position</label>
                                        <input type="text" class="form-control readonly-field" id="position" placeholder="Position will be auto-filled" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_hired" class="form-label">Date Hired</label>
                                        <input type="date" class="form-control readonly-field" id="date_hired" placeholder="Date hired will be auto-filled" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control readonly-field" id="department" placeholder="Department will be auto-filled" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sss_number" class="form-label">SSS Number</label>
                                        <input type="text" class="form-control readonly-field" id="sss_number" placeholder="SSS Number will be auto-filled" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hdmf_number" class="form-label">HDMF Number</label>
                                        <input type="text" class="form-control readonly-field" id="hdmf_number" placeholder="HDMF Number will be auto-filled" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tin_number" class="form-label">TIN Number</label>
                                        <input type="text" class="form-control readonly-field" id="tin_number" placeholder="TIN Number will be auto-filled" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="effective_date" name="effective_date" required>
                                        @error('effective_date')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="other_specify" class="form-label">Other (Specify)</label>
                                        <input type="text" class="form-control" id="other_specify" placeholder="Specify other action if selected" style="display:none;">
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="form-group">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks or additional information..."></textarea>
                            </div>

                            <!-- Details Table Section -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header" style="background-color:#2f47ba;color:white;">
                                            <h5 class="mb-0" style="font-size: 0.95rem;">Personnel Action Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover" id="detailsTable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 20%;">Particulars</th>
                                                            <th style="width: 40%;">From</th>
                                                            <th style="width: 40%;">To</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Department/Team</strong></td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][department_from]">
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($departments ?? [] as $dept)
                                                                        <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][department_to]">
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($departments ?? [] as $dept)
                                                                        <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Branch</strong></td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][branch_from]">
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($branches ?? [] as $branch)
                                                                        <option value="{{ $branch->branch }}">{{ $branch->branch }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][branch_to]">
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($branches ?? [] as $branch)
                                                                        <option value="{{ $branch->branch }}">{{ $branch->branch }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Employment Status</strong></td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][emp_status_from]">
                                                                    <option value="">-- Select --</option>
                                                                    <option value="Probationary">Probationary</option>
                                                                    <option value="Trainee">Trainee</option>
                                                                    <option value="Project Employee">Project Employee</option>
                                                                    <option value="Regular">Regular</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][emp_status_to]">
                                                                    <option value="">-- Select --</option>
                                                                    <option value="Probationary">Probationary</option>
                                                                    <option value="Trainee">Trainee</option>
                                                                    <option value="Project Employee">Project Employee</option>
                                                                    <option value="Regular">Regular</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Position/Title</strong></td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][position_from]">
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($positions ?? [] as $position)
                                                                        <option value="{{ $position->name }}">{{ $position->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][position_to]">
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($positions ?? [] as $position)
                                                                        <option value="{{ $position->name }}">{{ $position->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Schedule/Shift</strong></td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][schedule_from]" id="schedule_from">
                                                                    <option value="">-- Select Schedule --</option>
                                                                    @foreach($schedules ?? [] as $schedule)
                                                                        <option value="{{ $schedule->name }}">{{ $schedule->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control form-select" name="details[0][schedule_to]" id="schedule_to">
                                                                    <option value="">-- Select Schedule --</option>
                                                                    @foreach($schedules ?? [] as $schedule)
                                                                        <option value="{{ $schedule->name }}">{{ $schedule->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Name of Immediate Supervisor</strong></td>
                                                            <td><input type="text" class="form-control" name="details[0][supervisor_from]" placeholder="Current Supervisor"></td>
                                                            <td><input type="text" class="form-control" name="details[0][supervisor_to]" placeholder="New Supervisor"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Position/Title of Immediate Supervisor</strong></td>
                                                            <td><input type="text" class="form-control" name="details[0][supervisor_position_from]" placeholder="Current Supervisor Position"></td>
                                                            <td><input type="text" class="form-control" name="details[0][supervisor_position_to]" placeholder="New Supervisor Position"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Salary/Rate</strong></td>
                                                            <td><input type="number" class="form-control" name="details[0][salary_from]" placeholder="Current Salary" step="0.01"></td>
                                                            <td><input type="number" class="form-control" name="details[0][salary_to]" placeholder="New Salary" step="0.01"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Allowance(s)</strong></td>
                                                            <td><input type="text" class="form-control" name="details[0][allowance_from]" placeholder="Current Allowance"></td>
                                                            <td><input type="text" class="form-control" name="details[0][allowance_to]" placeholder="New Allowance"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Others (Specify)</strong></td>
                                                            <td><input type="text" class="form-control" name="details[0][others_from]" placeholder="Current Others"></td>
                                                            <td><input type="text" class="form-control" name="details[0][others_to]" placeholder="New Others"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Compensation</strong></td>
                                                            <td><input type="number" class="form-control" name="details[0][total_comp_from]" placeholder="Current Total" step="0.01"></td>
                                                            <td><input type="number" class="form-control" name="details[0][total_comp_to]" placeholder="New Total" step="0.01"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-group text-end mb-0">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="e"></i> Submit
                                        </button>
                                        <button type="reset" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-refresh"></i> Clear
                                        </button>
                                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-arrow-left"></i> Back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Personnel Actions List Table - visible to all users with read permission -->
                <div class="card mt-4" style="margin-left: 0; margin-right: 0;">
                    <div class="card-header" style="background-color:#2f47ba;color:white;padding: 1rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><h5 class="mb-0">Personnel Actions List</h5></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                                <table class="table table-striped table-bordered table-hover" id="personnelActionsTable">
                                <thead>
                                    <tr>
                                        <th>Emp Code</th>
                                        <th>Employee Name</th>
                                        <th>Action Type</th>
                                        <th>Department</th>
                                        <th>Eff. Date</th>
                                        <th>Created</th>
                                        <th>Details</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($personnelActions as $key => $action)
                                    <tr class="main-row" data-action-id="{{ $action->id }}">
                                        <td>{{ $action->emp_code ?? 'N/A' }}</td>
                                        <td>{{ $action->first_name . ' ' . $action->last_name }}</td>
                                        <td>
                                            @switch($action->action_type)
                                                @case('employment')
                                                    <span>Employment</span>
                                                    @break
                                                @case('regularization')
                                                    <span>Regularization</span>
                                                    @break
                                                @case('general_increase')
                                                    <span>General Increase</span>
                                                    @break
                                                @case('change_emp_status')
                                                    <span>Change Emp Status</span>
                                                    @break
                                                @case('transfer')
                                                    <span>Transfer</span>
                                                    @break
                                                @case('merit_increase')
                                                    <span>Merit Increase</span>
                                                    @break
                                                @case('change_position')
                                                    <span>Change Position</span>
                                                    @break
                                                @case('promotion')
                                                    <span>Promotion</span>
                                                    @break
                                                @case('reclassification')
                                                    <span>Reclassification</span>
                                                    @break
                                                @case('other')
                                                    <span>Other</span>
                                                    @break
                                                @default
                                                    <span>{{ $action->action_type }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $action->department_name ?? 'N/A' }}</td>
                                        <td>{{ $action->effective_date ? date('M d, Y', strtotime($action->effective_date)) : 'N/A' }}</td>
                                        <td>{{ $action->date_created ? date('M d, Y', strtotime($action->date_created)) : 'N/A' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary expand-btn" type="button" data-id="{{ $action->id }}" title="View Details" style="padding: 0.25rem 0.5rem;" onclick="viewDetailsModal('{{ $action->id }}')">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </td>
                                        <td>
                                            @if(!$isReadOnly)
                                                <button type="button" class="btn btn-sm btn-info" onclick="editAction('{{ $action->id }}')" title="Edit" style="padding: 0.25rem 0.5rem;">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteAction('{{ $action->id }}')" title="Delete" style="padding: 0.25rem 0.5rem;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @else
                                                <span class="text-muted" title="View only">View only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox" style="font-size: 2em; color: #ccc;"></i><br>
                                            <small>No personnel actions saved yet.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#2f47ba;color:white;">
        <h5 class="modal-title" id="detailsModalLabel">Personnel Action Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="modalDetailsContent">
          <p class="text-center text-muted">Loading details...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#2f47ba;color:white;">
        <h5 class="modal-title" id="editModalLabel">Edit Personnel Action</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          @csrf
          <input type="hidden" id="edit_action_id" name="action_id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_employee_display" class="form-label">Employee <span class="text-danger">*</span></label>
                <div class="form-control" id="edit_employee_display" style="background-color: #e9ecef; border: 1px solid #ced4da; padding: 0.375rem 0.75rem; border-radius: 0.25rem; height: auto; min-height: 2.5rem; display: flex; align-items: center;">
                  <span id="edit_employee_name" style="color: #495057;">-- Select Employee --</span>
                </div>
                <input type="hidden" id="edit_employee_id" name="employee_id">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_action_type_display" class="form-label">Action Type <span class="text-danger">*</span></label>
                <div class="form-control" id="edit_action_type_display" style="background-color: #e9ecef; border: 1px solid #ced4da; padding: 0.375rem 0.75rem; border-radius: 0.25rem; height: auto; min-height: 2.5rem; display: flex; align-items: center;">
                  <span id="edit_action_type_name" style="color: #495057;">-- Select Action --</span>
                </div>
                <input type="hidden" id="edit_action_type" name="action_type">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="edit_effective_date" name="effective_date" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_remarks" class="form-label">Remarks</label>
                <input type="text" class="form-control" id="edit_remarks" name="remarks" placeholder="Enter remarks">
              </div>
            </div>
          </div>

          <!-- Details Table Section -->
          <div class="row mt-3">
            <div class="col-12">
              <div class="card">
                <div class="card-header" style="background-color:#2f47ba;color:white;">
                  <h5 class="mb-0" style="font-size: 0.95rem;">Personnel Action Details</h5>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="editDetailsTable">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 20%;">Particulars</th>
                          <th style="width: 40%;">From</th>
                          <th style="width: 40%;">To</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><strong>Department/Team</strong></td>
                          <td>
                            <select class="form-control form-select" id="edit_department_from" name="details[0][department_from]">
                              <option value="">-- Select --</option>
                              @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-select" id="edit_department_to" name="details[0][department_to]">
                              <option value="">-- Select --</option>
                              @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                              @endforeach
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Branch</strong></td>
                          <td>
                            <select class="form-control form-select" id="edit_branch_from" name="details[0][branch_from]">
                              <option value="">-- Select --</option>
                              @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->branch }}">{{ $branch->branch }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-select" id="edit_branch_to" name="details[0][branch_to]">
                              <option value="">-- Select --</option>
                              @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->branch }}">{{ $branch->branch }}</option>
                              @endforeach
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Employment Status</strong></td>
                          <td>
                            <select class="form-control form-select" id="edit_emp_status_from" name="details[0][emp_status_from]">
                              <option value="">-- Select --</option>
                              <option value="Probationary">Probationary</option>
                              <option value="Trainee">Trainee</option>
                              <option value="Project Employee">Project Employee</option>
                              <option value="Regular">Regular</option>
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-select" id="edit_emp_status_to" name="details[0][emp_status_to]">
                              <option value="">-- Select --</option>
                              <option value="Probationary">Probationary</option>
                              <option value="Trainee">Trainee</option>
                              <option value="Project Employee">Project Employee</option>
                              <option value="Regular">Regular</option>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Position/Title</strong></td>
                          <td>
                            <select class="form-control form-select" id="edit_position_from" name="details[0][position_from]">
                              <option value="">-- Select --</option>
                              @foreach($positions ?? [] as $position)
                                <option value="{{ $position->name }}">{{ $position->name }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-select" id="edit_position_to" name="details[0][position_to]">
                              <option value="">-- Select --</option>
                              @foreach($positions ?? [] as $position)
                                <option value="{{ $position->name }}">{{ $position->name }}</option>
                              @endforeach
                            </select>
                          </td>
                        </tr>
                                        <tr>
                                          <td><strong>Schedule/Shift</strong></td>
                                          <td>
                                            <select class="form-control form-select" id="edit_schedule_from" name="details[0][schedule_from]">
                                              <option value="">-- Select Schedule --</option>
                                              @foreach($schedules ?? [] as $schedule)
                                                <option value="{{ $schedule->name }}">{{ $schedule->name }}</option>
                                              @endforeach
                                            </select>
                                          </td>
                                          <td>
                                            <select class="form-control form-select" id="edit_schedule_to" name="details[0][schedule_to]">
                                              <option value="">-- Select Schedule --</option>
                                              @foreach($schedules ?? [] as $schedule)
                                                <option value="{{ $schedule->name }}">{{ $schedule->name }}</option>
                                              @endforeach
                                            </select>
                                          </td>
                                        </tr>
                        <tr>
                          <td><strong>Immediate Supervisor</strong></td>
                          <td><input type="text" class="form-control" id="edit_supervisor_from" name="details[0][supervisor_from]" placeholder="Current Supervisor"></td>
                          <td><input type="text" class="form-control" id="edit_supervisor_to" name="details[0][supervisor_to]" placeholder="New Supervisor"></td>
                        </tr>
                        <tr>
                          <td><strong>Supervisor Position</strong></td>
                          <td><input type="text" class="form-control" id="edit_supervisor_position_from" name="details[0][supervisor_position_from]" placeholder="Current Position"></td>
                          <td><input type="text" class="form-control" id="edit_supervisor_position_to" name="details[0][supervisor_position_to]" placeholder="New Position"></td>
                        </tr>
                        <tr>
                          <td><strong>Salary/Rate</strong></td>
                          <td><input type="number" class="form-control" id="edit_salary_from" name="details[0][salary_from]" placeholder="Current Salary" step="0.01"></td>
                          <td><input type="number" class="form-control" id="edit_salary_to" name="details[0][salary_to]" placeholder="New Salary" step="0.01"></td>
                        </tr>
                        <tr>
                          <td><strong>Allowance(s)</strong></td>
                          <td><input type="number" class="form-control" id="edit_allowance_from" name="details[0][allowance_from]" placeholder="Current Allowance" step="0.01"></td>
                          <td><input type="number" class="form-control" id="edit_allowance_to" name="details[0][allowance_to]" placeholder="New Allowance" step="0.01"></td>
                        </tr>
                        <tr>
                          <td><strong>Others (Specify)</strong></td>
                          <td><input type="text" class="form-control" id="edit_others_from" name="details[0][others_from]" placeholder="Current Others"></td>
                          <td><input type="text" class="form-control" id="edit_others_to" name="details[0][others_to]" placeholder="New Others"></td>
                        </tr>
                        <tr>
                          <td><strong>Total Compensation</strong></td>
                          <td><input type="number" class="form-control" id="edit_total_comp_from" name="details[0][total_comp_from]" placeholder="Current Total" step="0.01"></td>
                          <td><input type="number" class="form-control" id="edit_total_comp_to" name="details[0][total_comp_to]" placeholder="New Total" step="0.01"></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitEditForm()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

@stop

@section("scripts")
<script>
    $(document).ready(function() {
        // Map for action types
        const actionTypeMap = {
            'employment': 'Employment',
            'regularization': 'Regularization',
            'general_increase': 'General Increase',
            'change_emp_status': 'Change in Employment Status',
            'transfer': 'Transfer',
            'merit_increase': 'Merit Increase',
            'change_position': 'Change in Position',
            'promotion': 'Promotion',
            'reclassification': 'Reclassification',
            'other': 'Other (Specify)'
        };

        // Build employees data object
        const employeesData = {};
        @foreach($employees ?? [] as $e)
            employeesData[{{ $e->id }}] = '{{ $e->emp_code }} - {{ $e->first_name }} {{ $e->last_name }}';
        @endforeach

        // Initialize Select2
        $(".form-select").select2({
            placeholder: "Select an option",
            allowClear: true
        });

        // Handle URL parameters for pre-filling employee and action type from dashboard
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Check if employee_id is in URL (from dashboard PAN action)
        var urlEmployeeId = getUrlParameter('employee_id');
        var urlAction = getUrlParameter('action');

        if (urlEmployeeId && urlAction === 'pan') {
            // Auto-select the employee
            setTimeout(function() {
                $('#employee_select').val(urlEmployeeId).trigger('change');
                // Set action type to Regularization for PAN (Personnel Action Note)
                $('#action_type_select').val('regularization').trigger('change');
            }, 500);
        }

        // Handle employee selection change
        $("#employee_select").on("change", function() {
            const employeeId = $(this).val();
            
            console.log("Employee ID selected:", employeeId);
            
            if (!employeeId) {
                clearEmployeeFields();
                $('#employee_id').val('');
                return;
            }

            // Store the employee ID in the hidden field
            $('#employee_id').val(employeeId);

            // Show loading indicator
            console.log("Fetching employee data for ID:", employeeId);

            // Fetch employee data
            $.ajax({
                url: "{{ route('get_employee_data') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {
                    employee_id: employeeId
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Employee data loaded successfully:", response);
                    $("#position").val(response.position_name || '');
                    $("#date_hired").val(response.date_hired || '');
                    $("#department").val(response.department_name || '');
                    $("#sss_number").val(response.sss_number || '');
                    $("#hdmf_number").val(response.hdmf_number || '');
                    $("#tin_number").val(response.tin_number || '');

                    
                    // Auto-populate table fields from employee data
                    // For department dropdown
                    $('select[name="details[0][department_from]"]').val(response.department_name || '').trigger('change');
                    // For position dropdown
                    $('select[name="details[0][position_from]"]').val(response.position_name || '').trigger('change');
                    // For branch dropdown
                    $('select[name="details[0][branch_from]"]').val(response.branch_name || '').trigger('change');
                    
                    // Auto-populate employment status (from employee_status field)
                    let empStatus = response.employee_status || (response.is_mwe === 1 ? 'Regular' : 'Contractual');
                    $('select[name="details[0][emp_status_from]"]').val(empStatus).trigger('change');
                    
                    // Auto-populate salary/rate
                    $('input[name="details[0][salary_from]"]').val(response.salary_rate || 0);
                    
                    // Auto-populate allowance from employee data
                    $('input[name="details[0][allowance_from]"]').val(response.allowance || '');
                    
                    // Fetch and populate schedules
                    $.ajax({
                        url: "{{ route('get_employee_schedule') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        data: {
                            employee_id: employeeId
                        },
                        dataType: 'json',
                        success: function(scheduleResponse) {
                            console.log("Schedules loaded:", scheduleResponse);
                            
                            // Auto-populate the "From" schedule with employee's current schedule
                            if (scheduleResponse.schedule_id && scheduleResponse.schedule_id > 0) {
                                // Find the week schedule name from the available schedules
                                let weekScheduleName = '';
                                @foreach($schedules ?? [] as $sched)
                                    if ({{ $sched->id }} === scheduleResponse.schedule_id) {
                                        weekScheduleName = '{{ $sched->name }}';
                                    }
                                @endforeach
                                
                                if (weekScheduleName) {
                                    $('select[name="details[0][schedule_from]"]').val(weekScheduleName).trigger('change');
                                    console.log("Set schedule_from to:", weekScheduleName);
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching schedules:", error);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error Status:", status);
                    console.error("Full XHR:", xhr);
                    console.error("Error:", error);
                    if (xhr.responseJSON) {
                        console.error("Error Response:", xhr.responseJSON);
                    } else {
                        console.error("Error Text:", xhr.responseText);
                    }
                    clearEmployeeFields();
                    let errorMessage = "Error loading employee data.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    alert(errorMessage);
                }
            });
        });

        // Handle action type change to show/hide other_specify field and display action type
        $("#action_type_select").on("change", function() {
            const actionType = $(this).val();
            
            // Store the action type in the hidden field
            $('#action_type').val(actionType);
            
            if (actionType === "other") {
                $("#other_specify").show();
                $("#other_specify").prop("required", true);
            } else {
                $("#other_specify").hide();
                $("#other_specify").prop("required", false);
                $("#other_specify").val("");
            }
        });

        // Handle employee selection in edit modal
        $(document).on("change", "#edit_employee_id", function() {
            const employeeId = $(this).val();
            
            if (!employeeId) {
                return;
            }

            $.ajax({
                url: "{{ route('get_employee_data') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {
                    employee_id: employeeId
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Employee data received:", response);
                    console.log("Department name from API:", response.department_name);
                    console.log("Position name from API:", response.position_name);
                    
                    // Auto-populate edit form fields - use select for dropdowns just like employment status
                    const deptValue = (response.department_name || '').trim();
                    const posValue = (response.position_name || '').trim();
                    const branchValue = (response.branch_name || '').trim();
                    
                    // Set department dropdown - same as employment status
                    if (deptValue) {
                        console.log("Setting department to:", deptValue);
                        $('#edit_department_from').val(deptValue).trigger('change');
                    }
                    
                    // Set position dropdown - same as employment status
                    if (posValue) {
                        console.log("Setting position to:", posValue);
                        $('#edit_position_from').val(posValue).trigger('change');
                    }
                    
                    // Set branch dropdown
                    if (branchValue) {
                        console.log("Setting branch to:", branchValue);
                        $('#edit_branch_from').val(branchValue).trigger('change');
                    }
                    
                    // For employment status - same pattern
                    const empStatusValue = response.employee_status || (response.is_mwe === 1 ? 'Regular' : 'Contractual');
                    if (empStatusValue) {
                        $('#edit_emp_status_from').val(empStatusValue).trigger('change');
                    }
                    
                    // For salary and allowance (these are text inputs)
                    $('input[name="details[0][salary_from]"]').val(response.salary_rate || 0);
                    $('input[name="details[0][allowance_from]"]').val(response.allowance || '');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching employee data:", error);
                    console.error("Response:", xhr.responseText);
                }
            });
        });

        // Auto-calculate Total Compensation when Salary or Allowance changes
        // Removed - user will enter Total Compensation manually

        // Function to calculate total compensation
        function calculateTotalCompensation() {
            const salaryFrom = parseFloat($('input[name="details[0][salary_from]"]').val()) || 0;
            const salaryTo = parseFloat($('input[name="details[0][salary_to]"]').val()) || 0;
            
            // You might need to parse allowance differently if it contains multiple values
            const allowanceFrom = parseFloat($('input[name="details[0][allowance_from]"]').val()) || 0;
            const allowanceTo = parseFloat($('input[name="details[0][allowance_to]"]').val()) || 0;
            
            const totalFrom = salaryFrom + allowanceFrom;
            const totalTo = salaryTo + allowanceTo;
            
            $('input[name="details[0][total_comp_from]"]').val(totalFrom.toFixed(2));
            $('input[name="details[0][total_comp_to]"]').val(totalTo.toFixed(2));
        }

        // Clear employee fields
        function clearEmployeeFields() {
            $("#position").val("");
            $("#date_hired").val("");
            $("#department").val("");
            $("#sss_number").val("");
            $("#hdmf_number").val("");
            $("#tin_number").val("");
        }

        // Initialize datepicker for effective_date if needed
        $("#effective_date").datepicker({
            dateFormat: "yy-mm-dd",
            minDate: 0
        });
        // Form submission with AJAX
        $("#personnelActionForm").on("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const actionUrl = $(this).attr("action");
            
            $.ajax({
                url: actionUrl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    console.log("Form submitted successfully:", response);
                    
                    // Show success modal
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    
                    // Reset form
                    $("#personnelActionForm")[0].reset();
                    
                    // Hide modal and reload after 2 seconds
                    document.getElementById('successModalBtn').addEventListener('click', function() {
                        location.reload();
                    });
                    
                    // Auto reload after 2 seconds if user doesn't click OK
                    setTimeout(function() {
                        if (document.getElementById('successModal').classList.contains('show')) {
                            location.reload();
                        }
                    }, 2000);
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    console.error("Response:", xhr.responseText);
                    
                    let errorMessage = "An error occurred while creating the personnel action.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    alert("Error: " + errorMessage);
                }
            });
        });

    });

    // Function to view details of a personnel action in a modal (OUTSIDE document.ready for global access)
    function viewDetailsModal(id) {
        $.ajax({
            url: "{{ route('get_personnel_action_details') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: {
                action_id: id
            },
            dataType: 'json',
            success: function(response) {
                let detailsHTML = `
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Employee: </h6>
                        <p>${response.employee_name || 'N/A'}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Action Type: </h6>
                        <p>${response.action_type || 'N/A'}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Effective Date: </h6>
                        <p>${response.effective_date ? new Date(response.effective_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold mb-3">Details:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead style="background-color:#2f47ba;color:white;">
                                <tr>
                                    <th>Particulars</th>
                                    <th>Current (From)</th>
                                    <th>New (To)</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                if (response.details && response.details.length > 0) {
                    const detail = response.details[0];
                    const particulars = [
                        { label: 'Department/Team', from: 'department_from', to: 'department_to' },
                        { label: 'Branch', from: 'branch_from', to: 'branch_to' },
                        { label: 'Employment Status', from: 'emp_status_from', to: 'emp_status_to' },
                        { label: 'Position/Title', from: 'position_from', to: 'position_to' },
                        { label: 'Schedule/Shift', from: 'schedule_from', to: 'schedule_to' },
                        { label: 'Name of Immediate Supervisor', from: 'supervisor_from', to: 'supervisor_to' },
                        { label: 'Position/Title of Immediate Supervisor', from: 'supervisor_position_from', to: 'supervisor_position_to' },
                        { label: 'Salary/Rate', from: 'salary_from', to: 'salary_to' },
                        { label: 'Allowance(s)', from: 'allowance_from', to: 'allowance_to' },
                        { label: 'Others (Specify)', from: 'others_from', to: 'others_to' },
                        { label: 'Total Compensation', from: 'total_comp_from', to: 'total_comp_to' }
                    ];
                    
                    particulars.forEach(particular => {
                        const fromValue = detail[particular.from] || '-';
                        const toValue = detail[particular.to] || '-';
                        detailsHTML += `
                            <tr>
                                <td><strong>${particular.label}</strong></td>
                                <td>${fromValue}</td>
                                <td>${toValue}</td>
                            </tr>
                        `;
                    });
                } else {
                    detailsHTML += '<tr><td colspan="3" class="text-center text-muted">No details found</td></tr>';
                }
                
                detailsHTML += '</tbody></table></div>';
                $('#modalDetailsContent').html(detailsHTML);
                $('#detailsModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                $('#modalDetailsContent').html('<p class="text-danger">Error loading details. Please try again.</p>');
                $('#detailsModal').modal('show');
            }
        });
    }

    // Function to delete a personnel action (OUTSIDE document.ready for global access)
    function deleteAction(id) {
        if (confirm('Are you sure you want to delete this personnel action? This action cannot be undone.')) {
            $.ajax({
                url: "{{ route('delete_personnel_action') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {
                    action_id: id
                },
                success: function(response) {
                    alert('Personnel action deleted successfully!');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error("Error Status:", xhr.status);
                    console.error("Error:", error);
                    console.error("Response:", xhr.responseText);
                    alert('Error deleting personnel action: ' + (xhr.responseText || error));
                }
            });
        }
    }

    // Function to edit a personnel action (OUTSIDE document.ready for global access)
    function editAction(id) {
        // Fetch the action details and populate the edit form
        $.ajax({
            url: "{{ route('get_personnel_action_details') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: {
                action_id: id
            },
            dataType: 'json',
            success: function(response) {
                // Show the edit modal first
                $('#editModal').modal('show');
                
                // Wait for modal to be fully shown before initializing
                $('#editModal').on('shown.bs.modal', function() {
                    // Initialize Select2 on edit modal selects if not already initialized
                    $('#edit_department_from, #edit_department_to, #edit_position_from, #edit_position_to, #edit_emp_status_from, #edit_emp_status_to').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2({
                                placeholder: "Select an option",
                                allowClear: true
                            });
                        }
                    });
                    
                    // Populate the edit form with the data
                    $('#edit_action_id').val(id);
                    
                    // Store values in hidden fields
                    $('#edit_employee_id').val(response.employee_id);
                    $('#edit_action_type').val(response.action_type);
                    
                    // Display employee name in the display field
                    $('#edit_employee_name').text(response.employee_name || 'N/A');
                    
                    // Display action type with proper naming
                    const actionTypeMap = {
                        'employment': 'Employment',
                        'regularization': 'Regularization',
                        'general_increase': 'General Increase',
                        'change_emp_status': 'Change in Employment Status',
                        'transfer': 'Transfer',
                        'merit_increase': 'Merit Increase',
                        'change_position': 'Change in Position',
                        'promotion': 'Promotion',
                        'reclassification': 'Reclassification',
                        'other': 'Other (Specify)'
                    };
                    const actionTypeName = actionTypeMap[response.action_type] || response.action_type;
                    $('#edit_action_type_name').text(actionTypeName);
                    
                    $('#edit_effective_date').val(response.effective_date);
                    $('#edit_remarks').val(response.remarks || '');
                    
                    // Populate the details
                    if (response.details && response.details.length > 0) {
                        const detail = response.details[0];
                        
                    // Fetch schedules for the employee
                                        $.ajax({
                                            url: "{{ route('get_employee_schedule') }}",
                                            type: "POST",
                                            headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                            },
                                            data: {
                                                employee_id: response.employee_id
                                            },
                                            dataType: 'json',
                                            success: function(scheduleResponse) {
                                                // Set the "From" value from detail (lib_week_schedule name)
                                                $('#edit_schedule_from').val(detail.schedule_from || '').trigger('change');
                                                
                                                // Set the "To" value from detail (lib_week_schedule name)
                                                $('#edit_schedule_to').val(detail.schedule_to || '').trigger('change');
                                            },
                                            error: function(xhr, status, error) {
                                                console.error("Error fetching schedules:", error);
                                            }
                                        });
                        
                        // Set select dropdowns and trigger change
                        $('#edit_department_from').val(detail.department_from || '').trigger('change');
                        $('#edit_department_to').val(detail.department_to || '').trigger('change');
                        $('#edit_branch_from').val(detail.branch_from || '').trigger('change');
                        $('#edit_branch_to').val(detail.branch_to || '').trigger('change');
                        $('#edit_position_from').val(detail.position_from || '').trigger('change');
                        $('#edit_position_to').val(detail.position_to || '').trigger('change');
                        $('#edit_emp_status_from').val(detail.emp_status_from || '').trigger('change');
                        $('#edit_emp_status_to').val(detail.emp_status_to || '').trigger('change');
                        
                        // Set text/number inputs
                        $('#edit_supervisor_from').val(detail.supervisor_from || '');
                        $('#edit_supervisor_to').val(detail.supervisor_to || '');
                        $('#edit_supervisor_position_from').val(detail.supervisor_position_from || '');
                        $('#edit_supervisor_position_to').val(detail.supervisor_position_to || '');
                        $('#edit_salary_from').val(detail.salary_from || '');
                        $('#edit_salary_to').val(detail.salary_to || '');
                        $('#edit_allowance_from').val(detail.allowance_from || '');
                        $('#edit_allowance_to').val(detail.allowance_to || '');
                        $('#edit_others_from').val(detail.others_from || '');
                        $('#edit_others_to').val(detail.others_to || '');
                        $('#edit_total_comp_from').val(detail.total_comp_from || '');
                        $('#edit_total_comp_to').val(detail.total_comp_to || '');
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                alert('Error loading personnel action for editing. Please try again.');
            }
        });
    }

    // Function to submit the edit form
    function submitEditForm() {
        const actionId = $('#edit_action_id').val();
        const formData = new FormData($('#editForm')[0]);
        formData.append('_token', '{{ csrf_token() }}');
        
        $.ajax({
            url: "{{ route('personnel_action.update') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
                console.log("Form updated successfully:", response);
                alert('Personnel action updated successfully!');
                $('#editModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                console.error("Response:", xhr.responseText);
                
                let errorMessage = "An error occurred while updating the personnel action.";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                alert("Error: " + errorMessage);
            }
        });
    }</script>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div style="font-size: 4rem; color: #28a745; margin-bottom: 1rem;">
                    <i class="fa fa-check-circle"></i>
                </div>
                <h4 class="modal-title mb-3" id="successModalLabel">Success!</h4>
                <p class="text-muted mb-4">Personnel action has been created successfully.</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successModalBtn">OK</button>
            </div>
        </div>
    </div>
</div>

@stop
