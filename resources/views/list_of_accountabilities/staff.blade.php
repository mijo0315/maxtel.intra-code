@extends('layouts.front-app')

@section('title')
@php
    $routeName = Route::current()->action["as"];
    $isStaff = Auth::user()->role_id == 2;
    $access = Auth::user()->access[$routeName] ?? Auth::user()->access['list_of_accountabilities'] ?? null;
    $userType = $isStaff ? 'Staff' : ($access['user_type'] ?? 'Unknown');
@endphp
{{ $userType }} - My Accountabilities
@stop

@section("styles")
<style>
    th {
        text-align: center;
    }
    .btn-check {
        display: none;
    }
    
    /* Button spacing and padding improvements */
    .btn {
        padding: 8px 16px !important;
        font-weight: 500;
        letter-spacing: 0.5px;
        line-height: 1.5;
    }
    
    .btn i {
        margin-right: 6px;
    }
    
    .btn-primary {
        padding: 10px 20px !important;
    }
    
    .btn-info, .btn-warning, .btn-secondary {
        padding: 8px 16px !important;
    }
    
    .btn-sm {
        padding: 6px 12px !important;
        font-size: 0.875rem;
    }
    
    .btn-block {
        padding: 10px 16px !important;
    }
    
    /* Custom status badge colors */
    .badge-assigned {
        background-color: #28a745 !important;
        color: white;
    }
    
    .badge-returned {
        background-color: #17a2b8 !important;
        color: white;
    }
    
    .badge-lost {
        background-color: #dc3545 !important;
        color: white;
    }
    
    .badge-damaged {
        background-color: #ffc107 !important;
        color: #212529;
    }
    
    /* Staff accountability card styling */
    .accountability-card {
        transition: transform 0.2s ease-in-out;
        border-left: 4px solid #2f47ba;
    }
    
    .accountability-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .accountability-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .accountability-meta {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-assigned { background-color: #28a745; }
    .status-returned { background-color: #17a2b8; }
    .status-lost { background-color: #dc3545; }
    .status-damaged { background-color: #ffc107; }
</style>
@stop

@section("content")
@php
    $routeName = Route::current()->action["as"];
    // Staff (role_id == 2) should always have access to their own accountabilities
    $isStaff = Auth::user()->role_id == 2;
    $access = Auth::user()->access[$routeName] ?? Auth::user()->access['list_of_accountabilities'] ?? null;
    $hasAccess = $isStaff || ($access && (preg_match("/R/i", $access['access']) || preg_match("/C/i", $access['access'])));
@endphp
@if(!$hasAccess)
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
@else
    <div class="page-wrapper" id="staff_accountabilities_page">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-xl-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header" style="background-color: #2f47ba;">
                            <h2 class="card-titles" style="color: white;">
                                My Accountabilities 
                                <i style="float:right; cursor: pointer;" class="fa fa-user"></i>
                            </h2>
                        </div>
                        
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h4 class="mb-3">Items Assigned to You</h4>
                                    <p class="text-muted">Below are the company property and items currently assigned to you. Please ensure these items are properly maintained and returned when requested.</p>
                                </div>
                            </div>

                            <!-- Filter Section for Staff -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="staffStatusFilter">Filter by Status:</label>
                                    <select class="form-control" id="staffStatusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="returned">Returned</option>
                                        <option value="lost">Lost</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-info btn-block" id="clearStaffFiltersBtn">
                                        <i class="fa fa-refresh"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Accountability Cards -->
                            <div id="accountabilityCards">
                                @forelse($accountabilities as $accountability)
                                    <div class="card accountability-card mb-3" data-status="{{ $accountability->status }}">
                                        <div class="card-header accountability-header d-flex justify-content-between align-items-center" style="cursor: pointer;" 
                                             data-toggle="collapse" data-target="#accountability-{{ $accountability->id }}" aria-expanded="false">
                                            <div>
                                                <h5 class="mb-0">
                                                    <span class="status-indicator status-{{ $accountability->status }}"></span>
                                                    {{ $accountability->item_name }}
                                                </h5>
                                                <small class="accountability-meta">
                                                    Assigned on {{ date('M d, Y', strtotime($accountability->date_assigned)) }}
                                                </small>
                                            </div>
                                            <span class="badge badge-{{ $accountability->badge_class }}">
                                                {{ ucfirst($accountability->status) }}
                                            </span>
                                        </div>
                                        
                                        <div class="collapse" id="accountability-{{ $accountability->id }}">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Item Description:</h6>
                                                            <p class="text-muted">{{ $accountability->item_description ?? 'No description provided' }}</p>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Date Assigned:</h6>
                                                            <p class="text-muted">{{ date('F d, Y', strtotime($accountability->date_assigned)) }}</p>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Status:</h6>
                                                            <span class="badge badge-{{ $accountability->badge_class }}">
                                                                {{ ucfirst($accountability->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        @if($accountability->item_value)
                                                            <div class="mb-3">
                                                                <h6 class="font-weight-bold">Item Value:</h6>
                                                                <p class="text-muted">₱{{ number_format($accountability->item_value, 2) }}</p>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($accountability->serial_number)
                                                            <div class="mb-3">
                                                                <h6 class="font-weight-bold">Serial Number:</h6>
                                                                <p class="text-muted">{{ $accountability->serial_number }}</p>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($accountability->property_number)
                                                            <div class="mb-3">
                                                                <h6 class="font-weight-bold">Property Number:</h6>
                                                                <p class="text-muted">{{ $accountability->property_number }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                @if($accountability->condition_assigned)
                                                    <div class="mb-3">
                                                        <h6 class="font-weight-bold">Condition When Assigned:</h6>
                                                        <p class="text-muted">{{ $accountability->condition_assigned }}</p>
                                                    </div>
                                                @endif
                                                
                                                @if($accountability->remarks)
                                                    <div class="mb-3">
                                                        <h6 class="font-weight-bold">Remarks:</h6>
                                                        <p class="text-muted">{{ $accountability->remarks }}</p>
                                                    </div>
                                                @endif
                                                
                                                <div class="alert alert-info mt-3">
                                                    <i class="fa fa-info-circle"></i>
                                                    <strong>Note:</strong> Please contact the HR department if you need to report any issues with this item or if you need to return it.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Accountabilities Found</h5>
                                            <p class="text-muted">You currently have no company property or items assigned to you.</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@stop

@section("scripts")
<script>
$(document).ready(function() {
    // Filter functionality for staff view
    $('#staffStatusFilter').on('change', function() {
        var selectedStatus = $(this).val();
        
        $('.accountability-card').each(function() {
            var cardStatus = $(this).data('status');
            
            if (selectedStatus === '' || cardStatus === selectedStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Clear filters
    $('#clearStaffFiltersBtn').on('click', function() {
        $('#staffStatusFilter').val('');
        $('.accountability-card').show();
    });
    
    // Add smooth animation to card toggling
    $('.accountability-card .card-header').on('click', function() {
        var target = $(this).data('target');
        $(target).slideToggle(300);
    });
});
</script>
@stop