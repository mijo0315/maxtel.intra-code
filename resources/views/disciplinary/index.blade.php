@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Disciplinary Management
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid" style="max-width: 100%; padding-left: 50px; padding-right: 30px;">
        <div class="row">
            <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(Auth::user()->role_id != 2)
            <div class="card mb-4">
                <div class="card-header">
                    {{ __('Create Disciplinary Note') }}
                </div>

                <div class="card-body">
                    <form action="{{ route('disciplinary.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label for="employee_id" class="col-md-2 col-form-label">{{ __('Employee') }}</label>
                            <div class="col-md-10">
                                <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror select2-search" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="case_details" class="col-md-2 col-form-label">{{ __('Disciplinary Case') }}</label>
                            <div class="col-md-10">
                                <input type="text" name="case_details" id="case_details" class="form-control @error('case_details') is-invalid @enderror" required>
                                @error('case_details')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="remarks" class="col-md-2 col-form-label">{{ __('Remarks') }}</label>
                            <div class="col-md-10">
                                <textarea name="remarks" id="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" required></textarea>
                                @error('remarks')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="date_served" class="col-md-2 col-form-label">{{ __('Date Served') }}</label>
                            <div class="col-md-10">
                                <input type="date" name="date_served" id="date_served" class="form-control @error('date_served') is-invalid @enderror" required>
                                @error('date_served')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="sanction" class="col-md-2 col-form-label">{{ __('Sanction') }}</label>
                            <div class="col-md-10">
                                <select name="sanction" id="sanction" class="form-control @error('sanction') is-invalid @enderror">
                                    <option value="">Select Sanction </option>
                                    <option value="1st_reprimand">1st Reprimand</option>
                                    <option value="2nd_reprimand">2nd Reprimand</option>
                                    <option value="3rd_reprimand">3rd Reprimand</option>
                                    <option value="final_reprimand">Final Reprimand</option>
                                    <option value="1day_suspension">1 Day Suspension</option>
                                    <option value="3day_suspension">3 Day Suspension</option>
                                    <option value="5day_suspension">5 Day Suspension</option>
                                    <option value="7day_suspension">7 Day Suspension</option>
                                    <option value="15day_suspension">15 Day Suspension</option>
                                    <option value="30day_suspension">30 Day Suspension</option>
                                    <option value="30day_preventive_suspension">30 Day Preventive Suspension</option>
                                    <option value="end_of_contract">End of Contract(OEC)</option>
                                    <option value="termination">For Termination</option>
                                 
                                </select>
                                @error('sanction')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="attachment" class="col-md-2 col-form-label">{{ __('Attachment') }}</label>
                            <div class="col-md-10">
                                <input type="file" name="attachment" id="attachment" class="form-control-file @error('attachment') is-invalid @enderror">
                                <p>All Files Attach Have Been Approve</p>
                                @error('attachment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-10 offset-md-2">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Create Disciplinary Note') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="card oth_income_card">
                <div class="card-header" style="background-color: #2f47ba;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="card-titles" style="color: white; margin: 0;">{{ __('Disciplinary Notes') }}</h2>
                        <form action="{{ route('disciplinary.export') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </form>
                    </div>
                    
                </div>

                <p class="m-2">Note: This Disciplinary Notice shall be deemed received and accepted if no complaints or arguments
                    are raised upon receipt.
                </p>

                <!-- Search Form -->
                <div class="row m-3">
                    <div class="col-md-12">
                        <form action="{{ route('disciplinary') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2" style="flex: 1;">
                                <input type="text" name="search" class="form-control w-100" placeholder="Search by employee name, document number, or case details..." value="{{ $search }}">
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-search"></i> Search
                            </button>
                            @if($search)
                                <a href="{{ route('disciplinary') }}" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-sm-12 col-12">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="tbl_disciplinary_list">
                                        <thead>
                                            <tr>
                                                <th>Document No.</th>
                                                <th>Employee</th>
                                                <th>Case Details</th>
                                                <th>Incident Document #</th>
                                                <th>Remarks</th>
                                                <th>Date Served</th>
                                                <th>Sanction</th>
                                                <th width="120px">Attachment</th>
                                                <th width="150px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($disciplinaryNotes as $note)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $note->document_number ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>{{ $note->employee?->first_name ?? 'N/A' }} {{ $note->employee?->last_name ?? '' }}</td>
                                                    <td>{{ \Str::limit($note->case_details, 50) }}</td>
                                                    <td>
                                                        @php
                                                            // Try to find incident report for this employee
                                                            $incidentReport = \App\Models\IncidentReport::where('reported_by', $note->employee_id)->latest()->first();
                                                        @endphp
                                                        @if($incidentReport)
                                                            <a href="javascript:void(0)" onclick="viewIncidentReport({{ $incidentReport->id }})" class="badge badge-info" style="cursor: pointer; font-size: 0.85em;">
                                                                {{ $incidentReport->document_number ?? '#' . $incidentReport->id }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ \Str::limit($note->remarks, 60) }}</td>
                                                    <td>{{ $note->date_served->format('M d, Y') }}</td>
                                                    <td>
                                                        @if($note->sanction)
                                                            <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $note->sanction)) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($note->attachment_path)
                                                            <a href="{{ asset($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-success">View</a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#disciplinaryViewModal-{{ $note->id }}">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <a href="{{ route('incident-report.create', ['disciplinary_id' => $note->id, 'employee_id' => $note->employee_id]) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-plus"></i> Add IR
                                                        </a>
                                                        <!-- @if(Auth::user()->role_id == 1)
                                                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#disciplinaryDeleteModal-{{ $note->id }}">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        @endif -->
                                                    </td>
                                                </tr>

                                                <!-- View Modal -->
                                                <div class="modal fade" id="disciplinaryViewModal-{{ $note->id }}" tabindex="-1" role="dialog">
                                                  <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                      <div class="modal-header" style="background-color:#2f47ba;color:white;">
                                                        <h5 class="modal-title">Disciplinary Note Details</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                      </div>
                                                      <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6 class="font-weight-bold">Document No.:</h6>
                                                                <p class="text-muted"><span class="badge badge-primary">{{ $note->document_number ?? 'N/A' }}</span></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="font-weight-bold">Date Served:</h6>
                                                                <p class="text-muted">{{ $note->date_served->format('F d, Y') }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6 class="font-weight-bold">Employee:</h6>
                                                                <p class="text-muted">{{ $note->employee?->first_name ?? 'N/A' }} {{ $note->employee?->last_name ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Case Details:</h6>
                                                            <p class="text-muted">{{ $note->case_details }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Remarks:</h6>
                                                            <p class="text-muted">{{ $note->remarks }}</p>
                                                        </div>
                                                        @if($note->sanction)
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Sanction:</h6>
                                                            <p class="text-muted"><span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $note->sanction)) }}</span></p>
                                                        </div>
                                                        @endif
                                                        @if($note->attachment_path)
                                                        <div class="mb-3">
                                                            <h6 class="font-weight-bold">Attachment:</h6>
                                                            <a href="{{ asset($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download Attachment
                                                            </a>
                                                        </div>
                                                        @endif
                                                      </div>
                                                      <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>

                                                <!-- Delete Modal for Admin -->
                                                @if(Auth::user()->role_id == 1)
                                                <div class="modal fade" id="disciplinaryDeleteModal-{{ $note->id }}" tabindex="-1" role="dialog">
                                                  <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                      <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                      </div>
                                                      <form action="{{ route('disciplinary.delete', $note->id) }}" method="POST">
                                                      @csrf
                                                      @method('DELETE')
                                                      <div class="modal-body">
                                                        <p>Are you sure you want to delete this disciplinary note?</p>
                                                        <div class="alert alert-warning">
                                                            <strong>Employee:</strong> {{ $note->employee?->first_name ?? 'N/A' }} {{ $note->employee?->last_name ?? '' }}<br>
                                                            <strong>Case:</strong> {{ \Str::limit($note->case_details, 50) }}<br>
                                                            <strong>Date:</strong> {{ $note->date_served->format('M d, Y') }}
                                                        </div>
                                                        <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All replies to this disciplinary note will also be deleted.</p>
                                                      </div>
                                                      <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Delete Note</button>
                                                      </div>
                                                      </form>
                                                    </div>
                                                  </div>
                                                </div>
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No disciplinary notes found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    
                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div class="text-muted">
                                            Showing {{ $disciplinaryNotes->firstItem() ?? 0 }} to {{ $disciplinaryNotes->lastItem() ?? 0 }} of {{ $disciplinaryNotes->total() }} results
                                        </div>
                                        <nav>
                                            {{ $disciplinaryNotes->appends(request()->query())->links() }}
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incident Report Modal -->
<div class="modal fade" id="incidentReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#2f47ba;color:white;">
                <h5 class="modal-title">Incident Report Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Incident #:</h6>
                        <p id="modalIncidentNo">N/A</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Reported By:</h6>
                        <p id="modalReportedBy">N/A</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Incident Type:</h6>
                        <p id="modalIncidentType">N/A</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Date of Incident:</h6>
                        <p id="modalDateIncident">N/A</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6 class="font-weight-bold">Location:</h6>
                        <p id="modalLocation">N/A</p>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="font-weight-bold">Incident Description:</h6>
                    <p id="modalIncidentDescription" style="white-space: pre-wrap;">N/A</p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Involved Employee:</h6>
                        <p id="modalNameInvolved">N/A</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Witness:</h6>
                        <p id="modalNameWitness">N/A</p>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="font-weight-bold">Recommended Action:</h6>
                    <p id="modalRecommendedAction" style="white-space: pre-wrap;">N/A</p>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	$(document).ready(function() {
		$('.select2-search').select2({
			placeholder: 'Search and select an employee',
			allowClear: true,
			width: '100%'
		});
	});

	function viewIncidentReport(reportId) {
		// Fetch report details via AJAX
		console.log('Fetching incident report:', reportId);
		$.ajax({
			url: "/incident-report/" + reportId,
			type: "GET",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			},
			dataType: 'json',
			success: function(response) {
				console.log('Response received:', response);
				// Populate modal with report details
				$('#modalIncidentNo').text(response.incident_no || 'N/A');
				$('#modalReportedBy').text(response.reported_by_name || 'N/A');
				$('#modalIncidentType').text(response.incident_type || 'N/A');
				$('#modalDateIncident').text(response.date_incident ? new Date(response.date_incident).toLocaleString() : 'N/A');
				$('#modalLocation').text(response.location || 'N/A');
				$('#modalIncidentDescription').text(response.incident_description || 'N/A');
				$('#modalNameInvolved').text(response.name_involved_name || 'N/A');
				$('#modalNameWitness').text(response.name_witness_name || 'N/A');
				$('#modalRecommendedAction').text(response.recommended_action || 'N/A');
				
				// Show the modal
				$('#incidentReportModal').modal('show');
			},
			error: function(xhr, status, error) {
				console.error("Error:", error);
				console.error("Status:", status);
				console.error("Response:", xhr.responseText);
				alert('Error loading incident report details. Please try again.');
			}
		});
	}
</script>
@endsection
