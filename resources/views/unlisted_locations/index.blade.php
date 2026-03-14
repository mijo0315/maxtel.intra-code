@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Unlisted Locations
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
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

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
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
    });
</script>
@endsection
