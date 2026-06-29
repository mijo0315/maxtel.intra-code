@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Low Attendance Report
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .filter-group {
        margin-bottom: 15px;
    }
    .filter-group label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }
    .btn-export {
        margin-top: 32px;
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="page-title">
                                <i class="fas fa-chart-bar"></i> Low Attendance Report
                            </h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item active">Low Attendance Report</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-group">
                                <label for="departmentFilter">Site:</label>
                                <select class="form-control" id="departmentFilter">
                                    <option value="">-- All Sites --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="filter-group">
                                <label for="employeeFilter">Employee:</label>
                                <select class="form-control" id="employeeFilter">
                                    <option value="">-- All Employees --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->bio_id }}" data-department="{{ $emp->department }}">
                                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->bio_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="filter-group">
                                <button class="btn btn-primary btn-block" id="applyFilters">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                            <div class="filter-group">
                                <button class="btn btn-success btn-block" id="exportBtn">
                                    <i class="fas fa-download"></i> Export to CSV
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i> Employees with Less Than 5 Records Per Day
                        </h4>
                        <p class="text-muted small">This report shows employees who have below 5 location records on a specific day.</p>
                    </div>
                    <div class="card-body" style="overflow-x: auto;">
                        <table class="table table-striped table-hover table-bordered" id="lowAttendanceTable" style="min-width: 1200px;">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">Bio ID</th>
                                    <th style="width: 20%;">Employee Name</th>
                                    <th style="width: 15%;">Sites</th>
                                    <th style="width: 12%;">Date</th>
                                    <th style="width: 15%;">Record Count</th>
                                    <th style="width: 20%;">Actions</th>
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

<!-- View Records Modal -->
<div class="modal fade" id="viewRecordsModal" tabindex="-1" role="dialog" aria-labelledby="viewRecordsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordsLabel">Location Records</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>Bio ID:</strong></label>
                    <p id="modalBioId" class="form-control-plaintext"></p>
                </div>
                <div class="form-group">
                    <label><strong>Employee Name:</strong></label>
                    <p id="modalEmployeeName" class="form-control-plaintext"></p>
                </div>
                <div class="form-group">
                    <label><strong>Date:</strong></label>
                    <p id="modalDate" class="form-control-plaintext"></p>
                </div>
                <hr>
                <div class="form-group">
                    <label><strong>Location Records:</strong></label>
                    <div id="modalRecords" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 on dropdowns
    $('#departmentFilter').select2({
        theme: 'bootstrap-5',
        placeholder: '-- All Sites --',
        allowClear: true,
        width: '100%'
    });

    $('#employeeFilter').select2({
        theme: 'bootstrap-5',
        placeholder: '-- All Employees --',
        allowClear: true,
        width: '100%'
    });

    let table;

    function initializeTable() {
        if (table) {
            table.destroy();
        }

        const department = $('#departmentFilter').val();
        const bioId = $('#employeeFilter').val();

        table = $('#lowAttendanceTable').DataTable({
            "bDestroy": true,
            "autoWidth": false,
            "searchHighlight": true,
            "searching": true,
            "processing": true,
            "serverSide": true,
            "orderMulti": true,
            "order": [[3, 'desc']],
            "pageLength": 10,
            "ajax": {
                "url": "{{ route('low_attendance_data') }}",
                "dataType": "json",
                "type": "POST",
                "data": {
                    "_token": "{{ csrf_token() }}",
                    "department": department,
                    "bio_id": bioId
                }
            },
            "columns": [
                {'data': 'bio_id'},
                {'data': 'employee_name'},
                {'data': 'department'},
                {'data': 'date'},
                {'data': 'count', 'render': function(data) { 
                    return '<span class="badge badge-danger">' + data + '/5</span>'; 
                }},
                {'data': 'action', 'orderable': false, 'searchable': false, 'render': function(data) { return data; }},
            ]
        });
    }

    // Initialize table on page load
    initializeTable();

    // Handle filter apply button
    $('#applyFilters').click(function() {
        initializeTable();
    });

    // Handle export button
    $('#exportBtn').click(function() {
        const department = $('#departmentFilter').val();
        const bioId = $('#employeeFilter').val();

        let url = "{{ route('export_low_attendance') }}";
        if (department) {
            url += "?department=" + encodeURIComponent(department);
        }
        if (bioId) {
            url += (department ? "&" : "?") + "bio_id=" + encodeURIComponent(bioId);
        }

        window.location.href = url;
    });

    // Handle export record button clicks
    $(document).on('click', '.export-record-btn', function(e) {
        e.preventDefault();
        const bioId = $(this).data('bio-id');
        const department = $(this).data('department');
        const date = $(this).data('date');

        let url = "{{ route('export_low_attendance') }}";
        if (department) {
            url += "?department=" + encodeURIComponent(department);
        }
        if (bioId) {
            url += (department ? "&" : "?") + "bio_id=" + encodeURIComponent(bioId);
        }

        window.location.href = url;
    });

    // Auto-filter employees by selected department
    $('#departmentFilter').on('change', function() {
        const selectedDept = $(this).val();
        const employeeSelect = $('#employeeFilter');
        
        if (selectedDept) {
            employeeSelect.find('option').each(function() {
                const dept = $(this).data('department');
                if ($(this).val() === '' || dept === selectedDept) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            employeeSelect.val('').trigger('change'); // Reset employee filter
        } else {
            employeeSelect.find('option').show();
            employeeSelect.val('').trigger('change');
        }
    });
});
</script>
@endsection
