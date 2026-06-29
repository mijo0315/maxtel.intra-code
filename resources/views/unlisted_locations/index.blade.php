@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Unlisted Locations
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <!-- Action Buttons -->
                <div class="mb-3">
                    <button class="btn btn-success" id="viewReportBtn" data-toggle="modal" data-target="#reportModal">
                        <i class=""></i>Entries Report
                    </button>
                    <button class="btn btn-info" id="viewUnlistedReportBtn" data-toggle="modal" data-target="#unlistedReportModal">
                        <i class="fas fa-chart-line"></i> Unlisted Reports
                    </button>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Unlisted Locations Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-map-marker-alt"></i> {{ __('Unlisted Locations') }}
                        </h4>
                    </div>
                    <div class="card-body" style="overflow-x: auto;">
                        <table class="table table-striped table-hover table-bordered" id="tbl_unlisted_locations" style="min-width: 1000px;">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Bio ID</th>
                                    <th style="width: 20%;">Employee Name</th>
                                    <th style="width: 35%;">Location</th>
                                    <th style="width: 15%;">Date & Time</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Listed Locations Table -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-map"></i> {{ __('Listed Locations') }}
                        </h4>
                    </div>
                    <div class="card-body" style="overflow-x: auto;">
                        <table class="table table-striped table-hover table-bordered" id="tbl_listed_locations" style="min-width: 1000px;">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Bio ID</th>
                                    <th style="width: 20%;">Employee Name</th>
                                    <th style="width: 30%;">Location</th>
                                    <th style="width: 15%;">Date Listed</th>
                                    <th style="width: 15%;">Actions</th>
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

<!-- View Location Modal -->
<div class="modal fade" id="viewLocationModal" tabindex="-1" role="dialog" aria-labelledby="viewLocationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLocationLabel">Location Details</h5>
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
                    <label><strong>Full Location:</strong></label>
                    <p id="modalLocation" class="form-control-plaintext" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Unlisted Locations Report Modal -->
<div class="modal fade" id="unlistedReportModal" tabindex="-1" role="dialog" aria-labelledby="unlistedReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unlistedReportModalLabel">
                    <i class="fas fa-map-marker-alt"></i> Unlisted Locations Report
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Filter Section -->
                <div class="filter-section" style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unlistedDepartmentFilter"><strong>Site:</strong></label>
                                <select class="form-control" id="unlistedDepartmentFilter">
                                    <option value="">-- All Sites --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unlistedEmployeeFilter"><strong>Employee:</strong></label>
                                <select class="form-control" id="unlistedEmployeeFilter">
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
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" id="unlistedApplyFilters" style="margin-top: 32px;">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div style="overflow-x: auto;">
                    <table class="table table-striped table-hover table-bordered" id="modalUnlistedLocationsTable" style="min-width: 1000px;">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Bio ID</th>
                                <th style="width: 18%;">Employee Name</th>
                                <th style="width: 15%;">Sites</th>
                                <th style="width: 34%;">Location</th>
                                <th style="width: 25%;">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="unlistedExportBtn">
                    <i class="fas fa-download"></i> Export to Excel
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Low Attendance Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-chart-bar"></i> Low Attendance Report
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Filter Section -->
                <div class="filter-section" style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="modalDepartmentFilter"><strong>Sites:</strong></label>
                                <select class="form-control" id="modalDepartmentFilter">
                                    <option value="">-- All Sites --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="modalEmployeeFilter"><strong>Employee:</strong></label>
                                <select class="form-control" id="modalEmployeeFilter">
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
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" id="modalApplyFilters" style="margin-top: 32px;">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div style="overflow-x: auto;">
                    <table class="table table-striped table-hover table-bordered" id="modalLowAttendanceTable" style="min-width: 1000px;">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Bio ID</th>
                                <th style="width: 20%;">Employee Name</th>
                                <th style="width: 15%;">Sites</th>
                                <th style="width: 12%;">Date</th>
                                <th style="width: 20%;">Record Count</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="modalExportBtn">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
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
    // Initialize Select2 on modal dropdowns when modal is opened
    $('#reportModal').on('shown.bs.modal', function() {
        $('#modalDepartmentFilter').select2({
            theme: 'bootstrap-5',
            placeholder: '-- All Sites --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#reportModal')
        });

        $('#modalEmployeeFilter').select2({
            theme: 'bootstrap-5',
            placeholder: '-- All Employees --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#reportModal')
        });
    });

    // Handle view location button clicks
    $(document).on('click', '.view-location-btn', function(e) {
        e.preventDefault();
        const bioId = $(this).data('bio-id');
        const employeeName = $(this).data('employee-name');
        const location = $(this).data('location');
        
        document.getElementById('modalBioId').textContent = bioId;
        document.getElementById('modalEmployeeName').textContent = employeeName;
        document.getElementById('modalLocation').textContent = location;
        $('#viewLocationModal').modal('show');
    });

    // Unlisted Locations Table
    $('#tbl_unlisted_locations').DataTable({
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
                "url": "{{ route('unlisted_locations_list') }}",
                "dataType": "json",
                "type": "POST",
                "data": {
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns": [
                {'data': 'bio_id'},
                {'data': 'employee_name'},
                {'data': 'location'},
                {'data': 'date_time'},
                {'data': 'action', 'orderable': false, 'searchable': false, 'render': function(data) { return data; }},
            ]
        });

        // Listed Locations Table
        $('#tbl_listed_locations').DataTable({
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
                "url": "{{ route('listed_locations_list') }}",
                "dataType": "json",
                "type": "POST",
                "data": {
                    "_token": "{{ csrf_token() }}"
                }
            },
            "columns": [
                {'data': 'bio_id'},
                {'data': 'employee_name'},
                {'data': 'location'},
                {'data': 'date_listed'},
                {'data': 'action', 'orderable': false, 'searchable': false, 'render': function(data) { return data; }},
            ]
        });

        // Handle Report Modal
        
        // Initialize modal report table when modal is opened
        $('#reportModal').on('shown.bs.modal', function() {
            initializeModalReportTable();
        });

        function initializeModalReportTable() {
            if ($.fn.DataTable.isDataTable('#modalLowAttendanceTable')) {
                $('#modalLowAttendanceTable').DataTable().destroy();
            }

            const department = $('#modalDepartmentFilter').val();
            const bioId = $('#modalEmployeeFilter').val();

            $('#modalLowAttendanceTable').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searching": true,
                "processing": true,
                "serverSide": true,
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

        // Handle modal apply filters button
        $(document).on('click', '#modalApplyFilters', function() {
            initializeModalReportTable();
        });

        // Handle modal export button
        $(document).on('click', '#modalExportBtn', function() {
            const department = $('#modalDepartmentFilter').val();
            const bioId = $('#modalEmployeeFilter').val();

            let url = "{{ route('export_low_attendance') }}";
            if (department) {
                url += "?department=" + encodeURIComponent(department);
            }
            if (bioId) {
                url += (department ? "&" : "?") + "bio_id=" + encodeURIComponent(bioId);
            }

            window.location.href = url;
        });

        // Auto-filter employees by selected department in modal
        $(document).on('change', '#modalDepartmentFilter', function() {
            const selectedDept = $(this).val();
            const employeeSelect = $('#modalEmployeeFilter');
            
            if (selectedDept) {
                employeeSelect.find('option').each(function() {
                    const dept = $(this).data('department');
                    if ($(this).val() === '' || dept === selectedDept) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                employeeSelect.val('').trigger('change');
            } else {
                employeeSelect.find('option').show();
                employeeSelect.val('').trigger('change');
            }
        });

        // Handle export record button clicks in modal
        $(document).on('click', '.export-record-btn', function(e) {
            e.preventDefault();
            const bioId = $(this).data('bio-id');
            const department = $(this).data('department');

            let url = "{{ route('export_low_attendance') }}";
            if (department) {
                url += "?department=" + encodeURIComponent(department);
            }
            if (bioId) {
                url += (department ? "&" : "?") + "bio_id=" + encodeURIComponent(bioId);
            }

            window.location.href = url;
        });

        // Handle Unlisted Report Modal
        $('#unlistedReportModal').on('shown.bs.modal', function() {
            $('#unlistedDepartmentFilter').select2({
                theme: 'bootstrap-5',
                placeholder: '-- All Sites --',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#unlistedReportModal')
            });

            $('#unlistedEmployeeFilter').select2({
                theme: 'bootstrap-5',
                placeholder: '-- All Employees --',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#unlistedReportModal')
            });

            initializeUnlistedReportTable();
        });

        function initializeUnlistedReportTable() {
            if ($.fn.DataTable.isDataTable('#modalUnlistedLocationsTable')) {
                $('#modalUnlistedLocationsTable').DataTable().destroy();
            }

            const department = $('#unlistedDepartmentFilter').val();
            const bioId = $('#unlistedEmployeeFilter').val();

            $('#modalUnlistedLocationsTable').DataTable({
                "bDestroy": true,
                "autoWidth": false,
                "searching": true,
                "processing": true,
                "serverSide": true,
                "order": [[4, 'desc']],
                "pageLength": 10,
                "ajax": {
                    "url": "{{ route('unlisted_locations_report_data') }}",
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
                    {'data': 'location'},
                    {'data': 'date_time'},
                ]
            });
        }

        // Handle unlisted apply filters button
        $(document).on('click', '#unlistedApplyFilters', function() {
            initializeUnlistedReportTable();
        });

        // Handle unlisted export button
        $(document).on('click', '#unlistedExportBtn', function() {
            const department = $('#unlistedDepartmentFilter').val();
            const bioId = $('#unlistedEmployeeFilter').val();

            let url = "{{ route('export_unlisted_locations') }}";
            if (department) {
                url += "?department=" + encodeURIComponent(department);
            }
            if (bioId) {
                url += (department ? "&" : "?") + "bio_id=" + encodeURIComponent(bioId);
            }

            window.location.href = url;
        });

        // Auto-filter employees by selected department in unlisted modal
        $(document).on('change', '#unlistedDepartmentFilter', function() {
            const selectedDept = $(this).val();
            const employeeSelect = $('#unlistedEmployeeFilter');
            
            if (selectedDept) {
                employeeSelect.find('option').each(function() {
                    const dept = $(this).data('department');
                    if ($(this).val() === '' || dept === selectedDept) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                employeeSelect.val('').trigger('change');
            } else {
                employeeSelect.find('option').show();
                employeeSelect.val('').trigger('change');
            }
        });
    });
</script>
@endsection
