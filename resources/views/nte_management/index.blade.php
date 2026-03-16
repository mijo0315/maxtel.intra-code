@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - nte_management
@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
	.card-header {
		transition: background-color 0.3s ease;
	}
	.card-header:hover {
		background-color: #f8f9fa;
	}
	.card-header[aria-expanded="true"] {
		background-color: #2f47ba;
		color: white;
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
</style>
@endsection

@section('content')
<div class="page-wrapper">
	<div class="content container-fluid" style="max-width: 100%; padding-left: 50px; padding-right: 30px;">
		@if(session('success'))
			<div class="alert alert-success">{{ session('success') }}</div>
		@endif
		@if(session('error'))
			<div class="alert alert-danger">{{ session('error') }}</div>
		@endif

		@if(preg_match("/R/i", Auth::user()->access[Route::current()->action["as"]]["access"])=="0")
			<label>YOU HAVE NO PRIVILEDGE ON THIS PAGE</label>
		@elseif((!$canCreateNte || Auth::user()->access[Route::current()->action["as"]]["user_type"] == "employee") && Auth::user()->role_id != 1 && Auth::user()->role_id != 27)
			<div class="row">
				<div class="col-md-12">
					<h4 class="mb-4">My NTE Notes</h4>
					@forelse($nteNotes as $note)
						<div class="card mb-3">
							<div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-toggle="collapse" data-target="#nte-note-{{ $note->id }}" aria-expanded="false">
								<h5 class="mb-0">{{ $note->case_details }}</h5>
								<span class="badge badge-primary">
									@if($note->date_served instanceof \Carbon\Carbon)
										{{ $note->date_served->format('M d, Y') }}
									@else
										{{ \Carbon\Carbon::parse($note->date_served)->format('M d, Y') }}
									@endif
								</span>
							</div>
							<div class="collapse" id="nte-note-{{ $note->id }}">
								<div class="card-body">
								<div class="mb-3">
									<h6 class="font-weight-bold">Remarks:</h6>
									<p class="text-muted">{{ $note->remarks }}</p>
								</div>
							<div class="mb-3">
								<h6 class="font-weight-bold">Date Served:</h6>
								<p class="text-muted">
									@if($note->date_served instanceof \Carbon\Carbon)
										{{ $note->date_served->format('F d, Y') }}
									@else
										{{ \Carbon\Carbon::parse($note->date_served)->format('F d, Y') }}
									@endif
								</p>
							</div>
								@if($note->attachment_path)
								<div class="mb-3">
									<h6 class="font-weight-bold">Attachment:</h6>
									<a href="{{ asset_with_env($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
										<i class="fas fa-download"></i> Download Attachment
									</a>
								</div>
								@endif
								<div class="mt-3">
									<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#nteReplyModal-{{ $note->id }}">Reply</button>
								</div>
							</div>
							
							<!-- Reply modal -->
							<div class="modal fade" id="nteReplyModal-{{ $note->id }}" tabindex="-1" role="dialog">
							  <div class="modal-dialog" role="document">
								<div class="modal-content">
								  <div class="modal-header">
									<h5 class="modal-title">Reply to NTE</h5>
									<button type="button" class="close" data-dismiss="modal">&times;</button>
								  </div>
								  <form action="{{ route('nte.reply') }}" method="POST" enctype="multipart/form-data">
								  @csrf
								  <div class="modal-body">
									<input type="hidden" name="parent_id" value="{{ $note->id }}">
									<div class="form-group">
										<label>Remarks</label>
										<textarea name="remarks" class="form-control" required></textarea>
									</div>
									<div class="form-group">
										<label>Attachment</label>
										<input type="file" name="attachment" class="form-control-file">
										
									</div>
								  </div>
								  <div class="modal-footer">
									<button class="btn btn-secondary" data-dismiss="modal">Close</button>
									<button class="btn btn-primary">Send Reply</button>
								  </div>
								  </form>
								</div>
							  </div>
							</div>
						</div>
					@empty
						<div class="alert alert-info">No NTE notes found.</div>
					@endforelse
				</div>
			</div>
		@else
			<div class="card mb-4">
				<div class="card-header">Create NTE</div>
				<div class="card-body">
					<form action="{{ route('nte.store') }}" method="POST" enctype="multipart/form-data">
						@csrf
					<div class="form-group">
						<label>Employee</label>
						<select name="employee_id" class="form-control select2-search" required>
							<option value="">Select employee</option>
							@foreach($employees as $employee)
								<option value="{{ $employee->id }}" @if(request('employee_id') && request('employee_id') == $employee->id) selected @endif>{{ $employee->first_name }} {{ $employee->last_name }}</option>
							@endforeach
						</select>
					</div>
						<div class="form-group">
							<label>Case Details</label>
							<input type="text" name="case_details" class="form-control" required>
						</div>
						<div class="form-group">
							<label>Remarks</label>
							<textarea name="remarks" class="form-control" required></textarea>
						</div>
						<div class="form-group">
							<label>Date Served</label>
							<input type="date" name="date_served" class="form-control" required>
						</div>
						<div class="form-group">
						<label>Due Date for Submission</label>
						<input type="date" name="due_date" class="form-control">
					</div>
					<div class="form-group">
						<label>Attachment</label>
						<input type="file" name="attachment" class="form-control-file">
						<p>All Files Attach Have Been Approve</p>
					</div>
					<div class="form-group">
						<label>Resolution</label>
						<textarea name="resolution" class="form-control" rows="3" placeholder="Enter resolution details (optional)"></textarea>
						</div>
						<button class="btn btn-success">Create NTE</button>
					</form>
				</div>
			</div>
		@endif

		<div class="row justify-content-center">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header" style="background-color:#2f47ba;color:white;">
						<div class="d-flex justify-content-between align-items-center">
							<span>NTE Notes</span>
							<form action="{{ route('nte.export') }}" method="POST" style="display:inline;">
								@csrf
								<button type="submit" class="btn btn-sm btn-success">
									<i class="fas fa-file-excel"></i> Export to Excel
								</button>
							</form>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped">
								<thead>
									<tr>
										<th style="width: 12%;">Document No.</th>
										<th style="width: 15%;">Employee</th>
										<th style="width: 20%;">Case</th>
										<th style="width: 20%;">Remarks</th>
										<th style="width: 8%;">Date Served</th>
										<th style="width: 8%;">Due Date</th>
										<th style="width: 8%;">Attachment</th>
										<th style="width: 10%;">Actions</th>
									</tr>
								</thead>
						<tbody>
							@forelse($nteNotes as $note)
							<tr>
								<td>
									<span class="badge badge-primary">{{ $note->document_number ?? 'N/A' }}</span>
								</td>
								<td>{{ $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A' }}</td>
								<td>{{ Str::limit($note->case_details, 50) }}</td>
								<td>{{ Str::limit($note->remarks, 60) }}</td>
								<td>{{ $note->date_served->format('M d, Y') }}</td>
								<td>{{ $note->due_date ? $note->due_date->format('M d, Y') : '-' }}</td>
								<td>
									@if($note->attachment_path)
										<a href="{{ asset_with_env($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-success">View</a>
									@endif
								</td>
								<td>
									<button class="btn btn-sm btn-info" data-toggle="modal" data-target="#nteViewModal-{{ $note->id }}">
										<i class="fas fa-eye"></i> View @if($note->replies && $note->replies->count() > 0)({{ $note->replies->count() }})@endif
									</button>
									@if(Auth::user()->role_id != 1 && Auth::user()->role_id != 27)
										<button class="btn btn-sm btn-success" data-toggle="modal" data-target="#nteReplyModalTable-{{ $note->id }}">Reply</button>
							@else
								<button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#nteEditModal-{{ $note->id }}" title="Edit NTE">
									<i class="fas fa-edit"></i>
								</button>
								<button class="btn btn-sm btn-success" data-toggle="modal" data-target="#createDRModal-{{ $note->id }}" title="Create Disciplinary Report">
									<i class="fas fa-file-alt"></i> Create DR
								</button>
								<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#nteDeleteModal-{{ $note->id }}">
									<i class="fas fa-trash"></i> Delete
								</button>
							@endif
						</td>
						</tr>

						@if(Auth::user()->role_id != 1 && Auth::user()->role_id != 27)
					
						<!-- View Modal -->
						<div class="modal fade" id="nteViewModal-{{ $note->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
							  <div class="modal-header" style="background-color:#2f47ba;color:white;">
								<h5 class="modal-title">NTE Details</h5>
								<button type="button" class="close text-white" data-dismiss="modal">&times;</button>
							  </div>
							  <div class="modal-body">
								<div class="row mb-3">
									<div class="col-md-6">
										<h6 class="font-weight-bold">Employee:</h6>
										<p class="text-muted">{{ $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A' }}</p>
									</div>
									<div class="col-md-6">
										<h6 class="font-weight-bold">Date Served:</h6>
										<p class="text-muted">{{ $note->date_served->format('F d, Y') }}</p>
									</div>
								</div>
							<div class="row mb-3">
								<div class="col-md-6">
									<h6 class="font-weight-bold">Due Date for Submission:</h6>
									<p class="text-muted">{{ $note->due_date ? $note->due_date->format('F d, Y') : 'N/A' }}</p>
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
							@if($note->attachment_path)
							<div class="mb-3">
								<h6 class="font-weight-bold">Attachment:</h6>
								<a href="{{ asset_with_env($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
									<i class="fas fa-download"></i> Download Attachment
								</a>
							</div>
							@endif
								
								@if($note->replies && $note->replies->count() > 0)
								<hr>
								<h6 class="font-weight-bold">Replies ({{ $note->replies->count() }}):</h6>
								@foreach($note->replies as $reply)
								<div class="card mb-2">
									<div class="card-body">
										<div class="d-flex justify-content-between">
											<small class="text-muted">
												<strong>{{ $reply->employee ? $reply->employee->first_name . ' ' . $reply->employee->last_name : 'N/A' }}</strong>
											</small>
											<small class="text-muted">{{ $reply->date_served->format('M d, Y') }}</small>
										</div>
										<p class="mt-2 mb-1">{{ $reply->remarks }}</p>
										@if($reply->attachment_path)
										<a href="{{ asset_with_env($reply->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
											<i class="fas fa-paperclip"></i> Attachment
										</a>
										@endif
									</div>
								</div>
								@endforeach
								@endif
							  </div>
							  <div class="modal-footer">
								<button class="btn btn-secondary" data-dismiss="modal">Close</button>
							  </div>
							</div>
						  </div>
						</div>

						<!-- Reply Modal -->
						<div class="modal fade" id="nteReplyModalTable-{{ $note->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog" role="document">
							<div class="modal-content">
							  <div class="modal-header">
								<h5 class="modal-title">Reply to NTE</h5>
								<button type="button" class="close" data-dismiss="modal">&times;</button>
							  </div>
							  <form action="{{ route('nte.reply') }}" method="POST" enctype="multipart/form-data">
							  @csrf
							  <div class="modal-body">
								<input type="hidden" name="parent_id" value="{{ $note->id }}">
								<div class="form-group">
									<label>Remarks</label>
									<textarea name="remarks" class="form-control" required></textarea>
								</div>
								<div class="form-group">
									<label>Attachment</label>
									<input type="file" name="attachment" class="form-control-file">
								</div>
							  </div>
							  <div class="modal-footer">
								<button class="btn btn-secondary" data-dismiss="modal">Close</button>
								<button class="btn btn-success">Send Reply</button>
							  </div>
							  </form>
							</div>
						  </div>
						</div>

						@else
						<!-- View Modal for Admin -->
						<div class="modal fade" id="nteViewModal-{{ $note->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
							  <div class="modal-header" style="background-color:#2f47ba;color:white;">
								<h5 class="modal-title">NTE Details</h5>
								<button type="button" class="close text-white" data-dismiss="modal">&times;</button>
							  </div>
							  <div class="modal-body">
								<div class="row mb-3">
									<div class="col-md-6">
										<h6 class="font-weight-bold">Employee:</h6>
										<p class="text-muted">{{ $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A' }}</p>
									</div>
									<div class="col-md-6">
										<h6 class="font-weight-bold">Date Served:</h6>
										<p class="text-muted">{{ $note->date_served->format('F d, Y') }}</p>
									</div>
								</div>
								<div class="row mb-3">
									<div class="col-md-6">
										<h6 class="font-weight-bold">Due Date for Submission:</h6>
										<p class="text-muted">{{ $note->due_date ? $note->due_date->format('F d, Y') : 'N/A' }}</p>
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
								@if($note->resolution)
								<div class="mb-3">
									<h6 class="font-weight-bold">Resolution:</h6>
									<p class="text-muted">{{ $note->resolution }}</p>
								</div>
								@endif
								@if($note->attachment_path)
								<div class="mb-3">
									<h6 class="font-weight-bold">Attachment:</h6>
									<a href="{{ asset_with_env($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
										<i class="fas fa-download"></i> Download Attachment
									</a>
								</div>
								@endif
								
								@if($note->replies && $note->replies->count() > 0)
								<hr>
								<h6 class="font-weight-bold">Replies ({{ $note->replies->count() }}):</h6>
								@foreach($note->replies as $reply)
								<div class="card mb-2">
									<div class="card-body">
										<div class="d-flex justify-content-between">
											<small class="text-muted">
												<strong>{{ $reply->employee ? $reply->employee->first_name . ' ' . $reply->employee->last_name : 'N/A' }}</strong>
											</small>
											<small class="text-muted">{{ $reply->date_served->format('M d, Y') }}</small>
										</div>
										<p class="mt-2 mb-1">{{ $reply->remarks }}</p>
										@if($reply->attachment_path)
										<a href="{{ asset_with_env($reply->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
											<i class="fas fa-paperclip"></i> Attachment
										</a>
										@endif
									</div>
								</div>
								@endforeach
								@endif
							  </div>
							  <div class="modal-footer">
								<button class="btn btn-secondary" data-dismiss="modal">Close</button>
							  </div>
							</div>
						  </div>
						</div>

						<!-- Edit Modal for Admin -->
						<div class="modal fade" id="nteEditModal-{{ $note->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
							  <div class="modal-header" style="background-color:#2f47ba;color:white;">
								<h5 class="modal-title">Edit NTE</h5>
								<button type="button" class="close text-white" data-dismiss="modal">&times;</button>
							  </div>
							  <form action="{{ route('nte.update', $note->id) }}" method="POST" enctype="multipart/form-data">
							  @csrf
							  @method('PUT')
							  <div class="modal-body">
								<!-- Employee (Read-only) -->
								<div class="form-group">
									<label>Employee Name</label>
									<input type="text" class="form-control" value="{{ $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A' }}" readonly>
								</div>

								<!-- Case Details -->
								<div class="form-group">
									<label>Case Details <span class="text-danger">*</span></label>
									<input type="text" name="case_details" class="form-control" value="{{ $note->case_details }}" required>
								</div>

								<!-- Remarks -->
								<div class="form-group">
									<label>Remarks <span class="text-danger">*</span></label>
									<textarea name="remarks" class="form-control" rows="3" required>{{ $note->remarks }}</textarea>
								</div>

								<!-- Date Served -->
								<div class="form-group">
									<label>Date Served <span class="text-danger">*</span></label>
									<input type="date" name="date_served" class="form-control" value="{{ $note->date_served->format('Y-m-d') }}" required>
								</div>

								<!-- Due Date -->
								<div class="form-group">
									<label>Due Date for Submission</label>
									<input type="date" name="due_date" class="form-control" value="{{ $note->due_date ? $note->due_date->format('Y-m-d') : '' }}">
								</div>

								<!-- Resolution -->
								<div class="form-group">
									<label>Resolution</label>
									<textarea name="resolution" class="form-control" rows="3">{{ $note->resolution ?? '' }}</textarea>
								</div>

								<!-- Attachment -->
								<div class="form-group">
									<label>Attachment</label>
									@if($note->attachment_path)
										<div class="mb-2">
											<a href="{{ asset_with_env($note->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
												<i class="fas fa-download"></i> Current Attachment
											</a>
										</div>
									@endif
									<input type="file" name="attachment" class="form-control-file">
									<small class="form-text text-muted">Leave empty to keep current attachment</small>
								</div>
							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-success">Update NTE</button>
							  </div>
							  </form>
							</div>
						  </div>
						</div>

						<!-- Delete Modal for Admin -->
						<div class="modal fade" id="nteDeleteModal-{{ $note->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog" role="document">
							<div class="modal-content">
							  <div class="modal-header bg-danger text-white">
								<h5 class="modal-title">Confirm Delete</h5>
								<button type="button" class="close text-white" data-dismiss="modal">&times;</button>
							  </div>
							  <form action="{{ route('nte.delete', $note->id) }}" method="POST">
							  @csrf
							  @method('DELETE')
							  <div class="modal-body">
								<p>Are you sure you want to delete this NTE?</p>
								<div class="alert alert-warning">
									<strong>Employee:</strong> {{ $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A' }}<br>
									<strong>Case:</strong> {{ Str::limit($note->case_details, 50) }}<br>
									<strong>Date:</strong> {{ $note->date_served->format('M d, Y') }}
								</div>
								<p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All replies to this NTE will also be deleted.</p>
							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-danger">Delete NTE</button>
							  </div>
							  </form>
							</div>
						  </div>
						</div>

						<!-- Create Disciplinary Report Modal -->
						<div class="modal fade" id="createDRModal-{{ $note->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
							  <div class="modal-header" style="background-color:#2f47ba;color:white;">
								<h5 class="modal-title">Create Disciplinary Report (DR)</h5>
								<button type="button" class="close text-white" data-dismiss="modal">&times;</button>
							  </div>
							  <form action="{{ route('disciplinary.store') }}" method="POST" enctype="multipart/form-data">
							  @csrf
							  <div class="modal-body">
								<!-- NTE Reference (Hidden) -->
								<input type="hidden" name="nte_note_id" value="{{ $note->id }}">

								<!-- Employee (Read-only) -->
								<div class="form-group">
									<label>Employee Name</label>
									<input type="text" class="form-control" value="{{ $note->employee ? $note->employee->first_name . ' ' . $note->employee->last_name : 'N/A' }}" readonly>
									<input type="hidden" name="employee_id" value="{{ $note->employee_id }}">
								</div>

								<!-- NTE Reference Display -->
								<div class="form-group">
									<label>NTE Reference</label>
									<input type="text" class="form-control" value="{{ $note->document_number ?? 'N/A' }}" readonly>
								</div>

								<!-- Case Details (Pre-filled from NTE) -->
								<div class="form-group">
									<label>Case Details <span class="text-danger">*</span></label>
									<input type="text" name="case_details" class="form-control" value="{{ $note->case_details }}" required>
								</div>

								<!-- Remarks (Pre-filled from NTE) -->
								<div class="form-group">
									<label>Remarks <span class="text-danger">*</span></label>
									<textarea name="remarks" class="form-control" rows="3" required>{{ $note->remarks }}</textarea>
								</div>

								<!-- Date Served -->
								<div class="form-group">
									<label>Date Served <span class="text-danger">*</span></label>
									<input type="date" name="date_served" class="form-control" required>
								</div>

								<!-- Sanction -->
								<div class="form-group">
									<label>Sanction/Decision <span class="text-danger">*</span></label>
									<select name="sanction" class="form-control" required>
										<option value="">Select sanction type</option>
										<option value="warning">Written Warning</option>
										<option value="suspension">Suspension</option>
										<option value="demotion">Demotion</option>
										<option value="termination">Termination</option>
										<option value="reprimand">Reprimand</option>
										<option value="other">Other</option>
									</select>
								</div>

								<!-- Attachment -->
								<div class="form-group">
									<label>Attachment</label>
									<input type="file" name="attachment" class="form-control-file">
									<small class="form-text text-muted">Max file size: 10MB</small>
								</div>
							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-success">Create Disciplinary Report</button>
							  </div>
							  </form>
							</div>
						  </div>
						</div>
						@endif

						@empty
						<tr><td colspan="6">No NTE notes found.</td></tr>
						@endforelse
					</tbody>
				</table>
				</div>
			</div>
				</div>
			</div>
		</div>

		@if(Auth::user()->role_id == 1)
		<!-- <div class="row justify-content-center">
			<div class="col-md-12">
				<div class="card mt-4">
					<div class="card-header" style="background-color:#2f47ba;color:white;">Employee Replies</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Original Case</th>
							<th>Reply By</th>
							<th>Remarks</th>
							<th>Date</th>
							<th>Attachment</th>
						</tr>
					</thead>
					<tbody>
						@foreach($nteNotes->whereNotNull('parent_id') as $reply)
						<tr>
							<td>{{ $reply->id }}</td>
							<td>{{ $reply->parent ? $reply->parent->case_details : '-' }}</td>
							<td>{{ $reply->employee ? $reply->employee->first_name . ' ' . $reply->employee->last_name : 'N/A' }}</td>
							<td>{{ $reply->remarks }}</td>
							<td>{{ $reply->date_served->format('M d, Y') }}</td>
							<td>
								@if($reply->attachment_path)
									<a href="{{ asset_with_env($reply->attachment_path) }}" class="btn btn-sm btn-outline-primary" download>Download</a>
								@else - @endif
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				</div>
			</div>
				</div>
			</div>
		</div> -->
		@endif
	</div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	$(document).ready(function() {
		$('.select2-search').select2({
			placeholder: 'Search and select an employee',
			allowClear: true,
			width: '100%'
		});
	});
</script>
@endsection