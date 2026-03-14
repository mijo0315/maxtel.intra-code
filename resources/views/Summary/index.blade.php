@extends('layouts.front-app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .summary-container {
        padding: 20px;
    }

    .summary-header {
        margin-bottom: 30px;
    }

    .summary-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 0 0 20px 0;
    }

    .employee-selector {
        margin-bottom: 25px;
        background: white;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
    }

    .form-group select:focus {
        outline: none;
        border-color: #2f47ba;
        box-shadow: 0 0 0 2px rgba(47, 71, 186, 0.1);
    }

    .summary-table-wrapper {
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .summary-table thead {
        background-color: #f5f5f5;
        border-bottom: 2px solid #ddd;
    }

    .summary-table th {
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }

    .summary-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        color: #666;
    }

    .summary-table tbody tr:hover {
        background-color: #fafafa;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-total {
        background-color: #e3f2fd;
        color: #1976d2;
    }

    .badge-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-resolved {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-approved {
        background-color: #d4edda;
        color: #155724;
    }

    .action-link {
        color: #2f47ba;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 8px;
        background: #f0f4ff;
        border-radius: 3px;
        transition: all 0.2s;
        display: inline-block;
    }

    .action-link:hover {
        background: #2f47ba;
        color: white;
        text-decoration: none;
    }

    .text-center {
        text-align: center;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 14px;
    }

    .select2-container--default .select2-selection--single {
        border: 1px solid #ddd;
        border-radius: 4px;
        height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-top: 4px;
        color: #333;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2f47ba;
        box-shadow: 0 0 0 2px rgba(47, 71, 186, 0.1);
    }

    .select2-dropdown {
        border: 1px solid #ddd;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #2f47ba;
    }

    .select2-search__field {
        border: 1px solid #ddd;
        padding: 6px 10px;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .summary-table {
            font-size: 12px;
        }

        .summary-table th,
        .summary-table td {
            padding: 8px 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="summary-container">
            <!-- Header -->
            <div class="summary-header">
                <h1 class="summary-title">Management Summary</h1>
            </div>

            <!-- Employee Selector -->
            <div class="employee-selector">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 15px;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label for="employee_select">Select Employee</label>
                        <select id="employee_select" name="employee_id" style="width: 100%;">
                            <option value="">-- Choose Employee --</option>
                        </select>
                    </div>
                    <button id="export_btn" class="btn btn-primary" style="padding: 10px 20px; display: none;">
                        <i class="fas fa-download"></i> Export to Excel
                    </button>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="summary-table-wrapper">
                <div id="table_container">
                    <div id="no_employee_message" class="empty-state" style="padding: 40px;">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="empty-state-text">Select an employee to view their management records</div>
                    </div>
                    
                    <!-- NTE Table -->
                    <div id="nte_section" style="display: none; margin-bottom: 30px;">
                        <h3 style="padding: 20px 15px 10px 15px; margin: 0; font-size: 16px; font-weight: 600; color: #2f47ba; border-bottom: 2px solid #2f47ba;">NTE (Notice to Explain)</h3>
                        <table class="summary-table" style="margin-top: 0;">
                            <thead>
                                <tr>
                                    <th>Date Served</th>
                                    <th>Case Details</th>
                                    <th>Remarks</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="nte_tbody">
                            </tbody>
                        </table>
                    </div>

                    <!-- Performance Improvement Table -->
                    <div id="performance_section" style="display: none; margin-bottom: 30px;">
                        <h3 style="padding: 20px 15px 10px 15px; margin: 0; font-size: 16px; font-weight: 600; color: #2f47ba; border-bottom: 2px solid #2f47ba;">Performance Improvement Plan</h3>
                        <table class="summary-table" style="margin-top: 0;">
                            <thead>
                                <tr>
                                    <th>Date Served</th>
                                    <th>Case Details</th>
                                    <th>Remarks</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="performance_tbody">
                            </tbody>
                        </table>
                    </div>

                    <!-- Disciplinary Table -->
                    <div id="disciplinary_section" style="display: none; margin-bottom: 30px;">
                        <h3 style="padding: 20px 15px 10px 15px; margin: 0; font-size: 16px; font-weight: 600; color: #2f47ba; border-bottom: 2px solid #2f47ba;">Disciplinary Actions</h3>
                        <table class="summary-table" style="margin-top: 0;">
                            <thead>
                                <tr>
                                    <th>Date Served</th>
                                    <th>Case Details</th>
                                    <th>Remarks</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="disciplinary_tbody">
                            </tbody>
                        </table>
                    </div>

                    <!-- View Details Modal -->
                    <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewDetailsModalLabel">Details</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div id="modalContent"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 with search
        $('#employee_select').select2({
            placeholder: '-- Choose Employee --',
            allowClear: true,
            width: '100%'
        });

        // Load employees on page load
        loadEmployees();

        // Handle employee selection
        $('#employee_select').on('change', function() {
            var employeeId = $(this).val();
            if (employeeId) {
                loadEmployeeSummary(employeeId);
                $('#export_btn').show();
            } else {
                $('#no_employee_message').show();
                $('#nte_section').hide();
                $('#performance_section').hide();
                $('#disciplinary_section').hide();
                $('#export_btn').hide();
            }
        });

        // Load all employees
        function loadEmployees() {
            $.ajax({
                url: "{{ route('get_all_employees') }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">-- Choose Employee --</option>';
                    $.each(response, function(index, employee) {
                        options += '<option value="' + employee.id + '">' + employee.name + ' (' + employee.emp_code + ')</option>';
                    });
                    $('#employee_select').html(options).trigger('change');
                    // Reinitialize Select2
                    $('#employee_select').select2({
                        placeholder: '-- Choose Employee --',
                        allowClear: true,
                        width: '100%'
                    });
                },
                error: function(xhr) {
                    console.error('Error loading employees:', xhr);
                    alert('Error loading employees. Please refresh the page.');
                }
            });
        }

        // Load employee summary data
        function loadEmployeeSummary(employeeId) {
            $.ajax({
                url: "{{ route('get_employee_records') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_id: employeeId
                },
                dataType: 'json',
                success: function(data) {
                    // Hide no employee message
                    $('#no_employee_message').hide();
                    
                    // Populate NTE table
                    let nteContent = '';
                    if (data.nte && data.nte.length > 0) {
                        data.nte.forEach(function(record) {
                            nteContent += `<tr>
                                <td>${new Date(record.date_served).toLocaleDateString()}</td>
                                <td>${record.case_details}</td>
                                <td>${record.remarks || '-'}</td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-info view-record" data-type="nte" data-id="${record.id}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>`;
                        });
                        $('#nte_tbody').html(nteContent);
                        $('#nte_section').show();
                    } else {
                        $('#nte_tbody').html('');
                        $('#nte_section').hide();
                    }
                    
                    // Populate Performance Improvement table
                    let performanceContent = '';
                    if (data.performance && data.performance.length > 0) {
                        data.performance.forEach(function(record) {
                            performanceContent += `<tr>
                                <td>${new Date(record.date_served).toLocaleDateString()}</td>
                                <td>${record.case_details}</td>
                                <td>${record.remarks || '-'}</td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-info view-record" data-type="performance" data-id="${record.id}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>`;
                        });
                        $('#performance_tbody').html(performanceContent);
                        $('#performance_section').show();
                    } else {
                        $('#performance_tbody').html('');
                        $('#performance_section').hide();
                    }
                    
                    // Populate Disciplinary table
                    let disciplinaryContent = '';
                    if (data.disciplinary && data.disciplinary.length > 0) {
                        data.disciplinary.forEach(function(record) {
                            disciplinaryContent += `<tr>
                                <td>${new Date(record.date_served).toLocaleDateString()}</td>
                                <td>${record.case_details}</td>
                                <td>${record.remarks || '-'}</td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-info view-record" data-type="disciplinary" data-id="${record.id}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>`;
                        });
                        $('#disciplinary_tbody').html(disciplinaryContent);
                        $('#disciplinary_section').show();
                    } else {
                        $('#disciplinary_tbody').html('');
                        $('#disciplinary_section').hide();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading records:', xhr);
                    alert('Error loading employee records');
                }
            });
        }

        // Export to Excel
        $('#export_btn').on('click', function() {
            var employeeId = $('#employee_select').val();
            if (employeeId) {
                window.location.href = "{{ route('export_employee_summary') }}?employee_id=" + employeeId;
            }
        });

        // Handle view button clicks
        $(document).on('click', '.view-record', function(e) {
            e.preventDefault();
            var recordType = $(this).data('type');
            var recordId = $(this).data('id');
            
            // Show modal with record details
            $.ajax({
                url: "{{ route('get_record_details') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: recordType,
                    id: recordId
                },
                dataType: 'json',
                success: function(record) {
                    let modalTitle = '';
                    let content = '<div class="record-details">';
                    
                    if (recordType === 'nte') {
                        modalTitle = 'Notice to Explain - Details';
                    } else if (recordType === 'performance') {
                        modalTitle = 'Performance Improvement Plan - Details';
                    } else if (recordType === 'disciplinary') {
                        modalTitle = 'Disciplinary Action - Details';
                    }
                    
                    content += `
                        <div class="form-group">
                            <label class="font-weight-bold">Date Served:</label>
                            <p>${new Date(record.date_served).toLocaleDateString()}</p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Case Details:</label>
                            <p>${record.case_details}</p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Remarks:</label>
                            <p>${record.remarks || 'N/A'}</p>
                        </div>
                    `;
                    
                    if (record.attachment_path) {
                        content += `
                            <div class="form-group">
                                <label class="font-weight-bold">Attachment:</label>
                                <p><a href="${record.attachment_path}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-download"></i> Download
                                </a></p>
                            </div>
                        `;
                    }
                    
                    content += '</div>';
                    
                    $('#viewDetailsModalLabel').text(modalTitle);
                    $('#modalContent').html(content);
                    $('#viewDetailsModal').modal('show');
                },
                error: function(xhr) {
                    alert('Error loading record details');
                }
            });
        });
    });
</script>
@endsection
