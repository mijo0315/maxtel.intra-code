@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"] ?? 'Admin'}} - Unlisted Locations Report
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
                                <i class="fas fa-map-marker-alt"></i> Unlisted Locations Report
                            </h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item active">Unlisted Locations Report</li>
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
                            <i class="fas fa-list"></i> All Unlisted Locations
                        </h4>
                        <p class="text-muted small">This report shows all employee locations that are not in the listed locations whitelist.</p>
                    </div>
                    <div class="card-body" style="overflow-x: auto;">
                        <table class="table table-striped table-hover table-bordered" id="unlistedLocationsTable" style="min-width: 1200px;">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">Bio ID</th>
                                    <th style="width: 18%;">Employee Name</th>
                                    <th style="width: 15%;">Department</th>
                                    <th style="width: 40%;">Location</th>
                                    <th style="width: 14%;">Date & Time</th>
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

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#departmentFilter, #employeeFilter').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Initialize DataTable
    var table = $('#unlistedLocationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('unlisted_locations_report_data') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function(d) {
                d.department = $('#departmentFilter').val();
                d.bio_id = $('#employeeFilter').val();
            }
        },
        columns: [
            { data: 'bio_id', name: 'bio_id' },
            { data: 'employee_name', name: 'employee_name' },
            { data: 'department', name: 'department' },
            { data: 'location', name: 'location' },
            { data: 'date_time', name: 'date_time' }
        ],
        order: [[4, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    // Apply Filters
    $('#applyFilters').click(function() {
        table.ajax.reload();
    });

    // Export to CSV
    $('#exportBtn').click(function() {
        var department = $('#departmentFilter').val();
        var bioId = $('#employeeFilter').val();
        var params = '';
        
        if (department) {
            params += '?department=' + encodeURIComponent(department);
        }
        if (bioId) {
            params += (params ? '&' : '?') + 'bio_id=' + encodeURIComponent(bioId);
        }
        
        window.location.href = '{{ route('export_unlisted_locations') }}' + params;
    });
});
</script>
@endsection
