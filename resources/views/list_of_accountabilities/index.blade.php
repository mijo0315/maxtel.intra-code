@extends('layouts.front-app')

@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - List Of Accountabilities
@stop

@section("content")
@php
    $routeName = Route::current()->action["as"];
    $hasReadPermission = isset(Auth::user()->access[$routeName]) && preg_match("/R/i", Auth::user()->access[$routeName]["access"]);
    $isEmployee = Auth::user()->role_id == 2;
@endphp

@if(!$hasReadPermission && !$isEmployee)
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-xl-12 col-sm-12 col-12 mb-4">
                    <div class="row">
                        <div class="col-xl-10 col-sm-8 col-12">
                            <label>YOU HAVE NO PRIVILEGE ON THIS PAGE</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif(Auth::user()->access[Route::current()->action["as"]]["user_type"] == "employee" || Auth::user()->role_id == 2)
    <!-- Staff View - Show only their own accountabilities -->
    <div class="page-wrapper" id="staff_accountabilities_page">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h2 class="card-title h5 mb-0 text-white">
                                My Accountabilities 
                                <i class="fa fa-user float-right" style="cursor: pointer;"></i>
                            </h2>
                        </div>
                        
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="mb-3 h5">Items Assigned to You</h4>
                                    <p class="text-muted small">Below are the company property and items currently assigned to you. Please ensure these items are properly maintained and returned when requested.</p>
                                </div>
                            </div>

                            <!-- Filter Section for Staff -->
                            <div class="row mb-3">
                                <div class="col-md-4 col-sm-6 col-12 mb-2">
                                    <label for="staffStatusFilter" class="form-label">Filter by Status:</label>
                                    <select class="form-control form-control-sm" id="staffStatusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="returned">Returned</option>
                                        <option value="lost">Lost</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 col-12 mb-2">
                                    <label class="form-label d-md-block d-sm-none">&nbsp;</label>
                                    <button class="btn btn-info btn-sm w-100" id="clearStaffFiltersBtn">
                                        <i class="fa fa-refresh"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Accountability Cards for Staff -->
                            <div id="staffAccountabilityCards">
                                @if(isset($accountabilities) && $accountabilities->count() > 0)
                                    @foreach($accountabilities as $accountability)
                                        <div class="card accountability-card mb-3 border-start border-primary border-4" data-status="{{ $accountability->status }}">
                                            <div class="card-header bg-light" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#staff-item-{{ $accountability->id }}">
                                                <div class="d-flex justify-content-between align-items-center flex-column flex-md-row">
                                                    <div class="text-center text-md-start mb-2 mb-md-0">
                                                        <h5 class="mb-0 h6">{{ $accountability->item_name }}</h5>
                                                        <small class="text-muted">Assigned on {{ date('M d, Y', strtotime($accountability->date_assigned)) }}</small>
                                                    </div>
                                                    <span class="badge bg-{{ $accountability->status == 'assigned' ? 'success' : ($accountability->status == 'returned' ? 'info' : ($accountability->status == 'lost' ? 'danger' : 'warning')) }}">
                                                        {{ ucfirst($accountability->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="collapse" id="staff-item-{{ $accountability->id }}">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                                                            <p class="mb-2"><strong>Description:</strong> {{ $accountability->item_description ?? 'No description provided' }}</p>
                                                            <p class="mb-2"><strong>Date Assigned:</strong> {{ date('F d, Y', strtotime($accountability->date_assigned)) }}</p>
                                                            <p class="mb-0"><strong>Status:</strong> 
                                                                <span class="badge bg-{{ $accountability->status == 'assigned' ? 'success' : ($accountability->status == 'returned' ? 'info' : ($accountability->status == 'lost' ? 'danger' : 'warning')) }}">
                                                                    {{ ucfirst($accountability->status) }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            @if($accountability->item_value)
                                                                <p class="mb-2"><strong>Item Value:</strong> ₱{{ number_format($accountability->item_value, 2) }}</p>
                                                            @endif
                                                            @if($accountability->serial_number)
                                                                <p class="mb-2"><strong>Serial Number:</strong> {{ $accountability->serial_number }}</p>
                                                            @endif
                                                            @if($accountability->property_number)
                                                                <p class="mb-0"><strong>Property Number:</strong> {{ $accountability->property_number }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($accountability->condition_assigned)
                                                        <p class="mb-2 mt-3"><strong>Condition When Assigned:</strong> {{ $accountability->condition_assigned }}</p>
                                                    @endif
                                                    @if($accountability->remarks)
                                                        <p class="mb-0"><strong>Remarks:</strong> {{ $accountability->remarks }}</p>
                                                    @endif
                                                    <div class="alert alert-info mt-3 mb-0 p-2 small">
                                                        <i class="fa fa-info-circle"></i>
                                                        <strong>Note:</strong> Please contact the HR department if you need to report any issues with this item or if you need to return it.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="card">
                                        <div class="card-body text-center py-4">
                                            <i class="fa fa-inbox fa-2x text-muted mb-3"></i>
                                            <h5 class="text-muted h6">No Accountabilities Found</h5>
                                            <p class="text-muted small mb-0">You currently have no company property or items assigned to you.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Admin/Timekeeper View - Full accountability management -->
    <div class="page-wrapper" id="accountabilities_page">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-12">
                    @if(Auth::user()->company['version'] == 1)
                    @endif
                </div>
            </div>
            
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="card-title h5 mb-0 text-white">
                                List Of Accountabilities 
                                
                            </h2>
                            <form action="{{ route('accountabilities.export') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-excel"></i> Export to Excel
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                                    <div class="mb-3 d-flex flex-wrap gap-2">
                                        <button class="btn btn-primary btn-sm" id="addAccountabilityBtn">
                                            <i class="fa fa-plus"></i> Add Accountability
                                        </button>
                                    </div>
                                @endif
                                
                                <!-- Filter Section -->
                                <div class="row mb-3">
                                    <div class="col-md-4 col-sm-6 col-12 mb-2">
                                        <label for="statusFilter" class="form-label">Filter by Status:</label>
                                        <select class="form-control form-control-sm" id="statusFilter">
                                            <option value="">All Statuses</option>
                                            <option value="assigned">Assigned</option>
                                            <option value="returned">Returned</option>
                                            <option value="lost">Lost</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 col-12 mb-2">
                                        <label class="form-label d-md-block d-sm-none">&nbsp;</label>
                                        <button class="btn btn-info btn-sm w-100" id="clearFiltersBtn">
                                            <i class="fa fa-refresh"></i> Clear Filters
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover table-sm" id="accountabilities_tbl">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">ID</th>
                                                <th>Employee</th>
                                                <th>Item</th>
                                                <th class="d-none d-md-table-cell">Description</th>
                                                <th class="d-none d-sm-table-cell">Date Assigned</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
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
@endif

<!-- Add Accountability Modal -->
<div class="modal fade" id="addAccountabilityModal" tabindex="-1" role="dialog" aria-labelledby="addAccountabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6" id="addAccountabilityModalLabel">Add New Accountability</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addAccountabilityForm">
                <div class="modal-body">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm select2-search" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <!-- Employee options will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="item_name" name="item_name" required placeholder="Enter item name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label for="item_description" class="form-label">Item Description</label>
                                <textarea class="form-control form-control-sm" id="item_description" name="item_description" rows="2" placeholder="Enter item description"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-4 col-12">
                            <div class="mb-2">
                                <label for="item_value" class="form-label">Item Value</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" id="item_value" name="item_value" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="mb-2">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control form-control-sm" id="serial_number" name="serial_number" placeholder="Enter serial number">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="mb-2">
                                <label for="property_number" class="form-label">Property Number</label>
                                <input type="text" class="form-control form-control-sm" id="property_number" name="property_number" placeholder="Enter property number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="date_assigned" class="form-label">Date Assigned <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="date_assigned" name="date_assigned" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" id="status" name="status" required>
                                    <option value="assigned">Assigned</option>
                                    <option value="returned">Returned</option>
                                    <option value="lost">Lost</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label for="condition_assigned" class="form-label">Condition When Assigned</label>
                                <textarea class="form-control form-control-sm" id="condition_assigned" name="condition_assigned" rows="2" placeholder="Describe the condition of the item when assigned"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control form-control-sm" id="remarks" name="remarks" rows="2" placeholder="Additional notes or remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveAccountabilityBtn">
                        Save Accountability
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Accountability Modal -->
<div class="modal fade" id="editAccountabilityModal" tabindex="-1" role="dialog" aria-labelledby="editAccountabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6" id="editAccountabilityModalLabel">Edit Accountability</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAccountabilityForm">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_accountability_id" name="accountability_id">
                    
                    <div class="row g-2">
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="edit_employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" id="edit_employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <!-- Employee options will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="edit_item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="edit_item_name" name="item_name" required placeholder="Enter item name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label for="edit_item_description" class="form-label">Item Description</label>
                                <textarea class="form-control form-control-sm" id="edit_item_description" name="item_description" rows="2" placeholder="Enter item description"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-4 col-12">
                            <div class="mb-2">
                                <label for="edit_item_value" class="form-label">Item Value</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" id="edit_item_value" name="item_value" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="mb-2">
                                <label for="edit_serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control form-control-sm" id="edit_serial_number" name="serial_number" placeholder="Enter serial number">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="mb-2">
                                <label for="edit_property_number" class="form-label">Property Number</label>
                                <input type="text" class="form-control form-control-sm" id="edit_property_number" name="property_number" placeholder="Enter property number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="edit_date_assigned" class="form-label">Date Assigned <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="edit_date_assigned" name="date_assigned" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="mb-2">
                                <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" id="edit_status" name="status" required>
                                    <option value="assigned">Assigned</option>
                                    <option value="returned">Returned</option>
                                    <option value="lost">Lost</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label for="edit_condition_assigned" class="form-label">Condition When Assigned</label>
                                <textarea class="form-control form-control-sm" id="edit_condition_assigned" name="condition_assigned" rows="2" placeholder="Describe the condition of the item when assigned"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label for="edit_remarks" class="form-label">Remarks</label>
                                <textarea class="form-control form-control-sm" id="edit_remarks" name="remarks" rows="2" placeholder="Additional notes or remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="updateAccountabilityBtn">
                        Update Accountability
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section("scripts")
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@php
    $isStaffOrEmployee = Auth::user()->role_id == 2 || (isset(Auth::user()->access[$routeName]) && Auth::user()->access[$routeName]["user_type"] == "employee");
@endphp

<script>
$(document).ready(function() {
    @if($isStaffOrEmployee)
        // Staff View JavaScript - no AJAX needed, data is already loaded
        
        // Staff filter functionality
        $('#staffStatusFilter').on('change', function() {
            filterStaffAccountabilities();
        });
        
        $('#clearStaffFiltersBtn').on('click', function() {
            $('#staffStatusFilter').val('');
            filterStaffAccountabilities();
        });
        
    @else
        // Admin/Timekeeper View JavaScript
        // Initialize Select2 for employee dropdown
        $('#employee_id').select2({
            placeholder: 'Search for an employee...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addAccountabilityModal')
        });
        
        // Initialize Select2 for edit employee dropdown
        $('#edit_employee_id').select2({
            placeholder: 'Search for an employee...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editAccountabilityModal')
        });
        
        loadAccountabilitiesTable();
        loadEmployees();
        
        // Set default date to today
        $('#date_assigned').val(new Date().toISOString().split('T')[0]);
        
        // Admin filter functionality
        $('#statusFilter').on('change', function() {
            if (window.accountabilitiesTable) {
                window.accountabilitiesTable.ajax.reload();
            }
        });
        
        $('#clearFiltersBtn').on('click', function() {
            $('#statusFilter').val('');
            if (window.accountabilitiesTable) {
                window.accountabilitiesTable.ajax.reload();
            }
        });
        
        $('#addAccountabilityBtn').on('click', function() {
            $('#addAccountabilityModal').modal('show');
        });
        
        // Handle edit button clicks
        $(document).on('click', '.edit-accountability-btn', function() {
            var accountabilityId = $(this).data('id');
            editAccountability(accountabilityId);
        });
        
        // Handle delete button clicks
        $(document).on('click', '.delete-accountability-btn', function() {
            var accountabilityId = $(this).data('id');
            deleteAccountability(accountabilityId);
        });
        
        // Form submission for add
        $('#addAccountabilityForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            $('#saveAccountabilityBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            
            $.ajax({
                url: "{{ route('store_accountability') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $.notify("Accountability record saved successfully!", {type: "success"});
                        $('#addAccountabilityModal').modal('hide');
                        $('#addAccountabilityForm')[0].reset();
                        $('#employee_id').val(null).trigger('change');
                        $('#date_assigned').val(new Date().toISOString().split('T')[0]);
                        loadAccountabilitiesTable();
                    } else {
                        $.notify(response.error || "Failed to save accountability record", {type: "danger"});
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = "Error saving accountability record";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    $.notify(errorMessage, {type: "danger"});
                    console.error('Save accountability error:', {xhr, status, error});
                },
                complete: function() {
                    $('#saveAccountabilityBtn').prop('disabled', false).html('Save Accountability');
                }
            });
        });
        
        // Form submission for edit
        $('#editAccountabilityForm').on('submit', function(e) {
            e.preventDefault();
            
            var accountabilityId = $('#edit_accountability_id').val();
            var formData = $(this).serialize();
            $('#updateAccountabilityBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
            
            $.ajax({
                url: "{{ url('update_accountability') }}/" + accountabilityId,
                type: "PUT",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $.notify("Accountability record updated successfully!", {type: "success"});
                        $('#editAccountabilityModal').modal('hide');
                        loadAccountabilitiesTable();
                    } else {
                        $.notify(response.error || "Failed to update accountability record", {type: "danger"});
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = "Error updating accountability record";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    $.notify(errorMessage, {type: "danger"});
                    console.error('Update accountability error:', {xhr, status, error});
                },
                complete: function() {
                    $('#updateAccountabilityBtn').prop('disabled', false).html('Update Accountability');
                }
            });
        });
        
        // Reset form when modal is hidden
        $('#addAccountabilityModal').on('hidden.bs.modal', function() {
            $('#addAccountabilityForm')[0].reset();
            $('#employee_id').val(null).trigger('change');
            $('#date_assigned').val(new Date().toISOString().split('T')[0]);
        });
        
        // Reset edit form when modal is hidden
        $('#editAccountabilityModal').on('hidden.bs.modal', function() {
            $('#editAccountabilityForm')[0].reset();
            $('#edit_employee_id').val(null).trigger('change');
        });
    @endif
});

function filterStaffAccountabilities() {
    var selectedStatus = $('#staffStatusFilter').val();
    
    $('.accountability-card').each(function() {
        var cardStatus = $(this).data('status');
        
        if (selectedStatus === '' || cardStatus === selectedStatus) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Admin functions (only loaded for admin/timekeeper)
function loadEmployees() {
    $.ajax({
        url: "{{ route('get_employees') }}",
        type: "GET",
        success: function(response) {
            console.log('Employee response:', response);
            const $addSelect = $('#employee_id');
            const $editSelect = $('#edit_employee_id');
            
            // Clear existing options
            $addSelect.empty().append('<option value="">Search for an employee...</option>');
            $editSelect.empty().append('<option value="">Search for an employee...</option>');
            
            if (response.success && response.employees) {
                response.employees.forEach(function(employee) {
                    $addSelect.append(new Option(employee.name, employee.id));
                    $editSelect.append(new Option(employee.name, employee.id));
                });
                
                // Notify Select2 that options have been updated
                $addSelect.trigger('change');
                $editSelect.trigger('change');
                console.log('Loaded ' + response.employees.length + ' employees');
            } else {
                console.error('Failed to parse employee response:', response);
                $.notify("Failed to load employees", {type: "warning"});
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load employees:', {xhr, status, error});
            console.error('Response text:', xhr.responseText);
            console.error('Status code:', xhr.status);
            
            // Set a default option
            $('#employee_id').html('<option value="">Failed to load employees</option>');
            $('#edit_employee_id').html('<option value="">Failed to load employees</option>');
            
            let errorMessage = "Failed to load employees";
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            $.notify(errorMessage, {type: "danger"});
        }
    });
}

function editAccountability(id) {
    $.ajax({
        url: "{{ url('get_accountability') }}/" + id,
        type: "GET",
        success: function(response) {
            if (response.success) {
                var accountability = response.accountability;
                
                // Populate the edit form
                $('#edit_accountability_id').val(accountability.id);
                $('#edit_employee_id').val(accountability.employee_id).trigger('change');
                $('#edit_item_name').val(accountability.item_name);
                $('#edit_item_description').val(accountability.item_description);
                $('#edit_item_value').val(accountability.item_value);
                $('#edit_serial_number').val(accountability.serial_number);
                $('#edit_property_number').val(accountability.property_number);
                $('#edit_date_assigned').val(accountability.date_assigned);
                $('#edit_status').val(accountability.status);
                $('#edit_condition_assigned').val(accountability.condition_assigned);
                $('#edit_remarks').val(accountability.remarks);
                
                // Show the edit modal
                $('#editAccountabilityModal').modal('show');
            } else {
                $.notify(response.error || "Failed to load accountability data", {type: "danger"});
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = "Error loading accountability data";
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            $.notify(errorMessage, {type: "danger"});
            console.error('Load accountability error:', {xhr, status, error});
        }
    });
}

function deleteAccountability(id) {
    if (confirm('Are you sure you want to delete this accountability record? This action cannot be undone.')) {
        var deleteUrl = "{{ route('delete_accountability', ['id' => 'REPLACE_ID']) }}".replace('REPLACE_ID', id);
        console.log('Delete URL:', deleteUrl);
        
        $.ajax({
            url: deleteUrl,
            type: "POST",
            data: {
                '_method': 'DELETE',
                '_token': "{{ csrf_token() }}"
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                console.log('Delete success response:', response);
                if (response.success) {
                    $.notify("Accountability record deleted successfully!", {type: "success"});
                    loadAccountabilitiesTable();
                } else {
                    $.notify(response.error || "Failed to delete accountability record", {type: "danger"});
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error - Full XHR Object:', xhr);
                console.error('Delete error - Status:', xhr.status);
                console.error('Delete error - StatusText:', xhr.statusText);
                console.error('Delete error - Response Text:', xhr.responseText);
                console.error('Delete error - Status Code:', error);
                
                let errorMessage = "Error deleting accountability record";
                if (xhr.status === 404) {
                    errorMessage = "Record not found or route not accessible. Status: 404";
                } else if (xhr.status === 403) {
                    errorMessage = "You do not have permission to delete this record. Status: 403";
                } else if (xhr.status === 405) {
                    errorMessage = "Method not allowed. Status: 405";
                } else if (xhr.status === 500) {
                    errorMessage = "Server error. Status: 500";
                    try {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        if (jsonResponse.error) {
                            errorMessage += " - " + jsonResponse.error;
                        }
                    } catch(e) {}
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                $.notify(errorMessage, {type: "danger"});
                console.error('Delete accountability error:', {xhr, status, error});
            }
        });
    }
}

function loadAccountabilitiesTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#accountabilities_tbl')) {
        $('#accountabilities_tbl').DataTable().destroy();
    }
    
    window.accountabilitiesTable = $('#accountabilities_tbl').DataTable({
        "bDestroy": true,
        "autoWidth": false,
        "searchHighlight": true,
        "searching": true,
        "processing": true,
        "serverSide": true,
        "orderMulti": true,
        "order": [],
        "pageLength": 10,
        "ajax": {
            "url": "{{ route('accountabilities_list') }}",
            "dataType": "json",
            "type": "POST",
            "data": function(d) {
                // Add custom filter parameters
                d._token = "{{ csrf_token() }}";
                d.page = "{{ Route::current()->action['as'] }}";
                d.status_filter = $('#statusFilter').val();
            },
            "error": function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', error, thrown);
                console.error('Response:', xhr.responseText);
                console.error('Status:', xhr.status);
                if (xhr.responseJSON) {
                    console.error('JSON Response:', xhr.responseJSON);
                }
                $.notify("Error loading data: " + error + " (Status: " + xhr.status + ")", {type: "danger"});
            }
        },
        "columns": [
            {'data': 'id'},
            {'data': 'employee'},
            {'data': 'item'},
            {'data': 'description'},
            {'data': 'date_assigned'},
            {'data': 'status'},
            {'data': 'action'}
        ]
    });
}
</script>
@stop