@extends('layouts.front-app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4>Create Incident Report</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('incident-report.store') }}" method="POST">
                        @csrf
                        
                        <!-- Hidden field for disciplinary_note_id if coming from disciplinary page -->
                        @if(request('disciplinary_id'))
                        <input type="hidden" name="disciplinary_note_id" value="{{ request('disciplinary_id') }}">
                        @endif
                        <!-- Row 1: Reported By and Position -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reported_by">Reported By <span class="text-danger">*</span></label>
                                    <select 
                                        id="reported_by" 
                                        name="reported_by" 
                                        class="form-control form-select"
                                        required
                                    >
                                        <option value="">-- Select Employee --</option>
                                        @forelse($employees ?? [] as $emp)
                                            <option value="{{ $emp->id }}" data-position="{{ $emp->position_name ?? '' }}" @if($employee && $employee->id == $emp->id) selected @endif>
                                                {{ $emp->emp_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                            </option>
                                        @empty
                                            <option value="">No employees available</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Position <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="position" 
                                        name="position" 
                                        class="form-control"
                                        value="@if($employee && isset($employee->position_name)){{ $employee->position_name }}@endif"
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Row 2: Date and Time of Report and Incident No -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_time_report">Date and Time of Report <span class="text-danger">*</span></label>
                                    <input 
                                        type="datetime-local" 
                                        id="date_time_report" 
                                        name="date_time_report" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="incident_no">Incident No. <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="incident_no" 
                                        name="incident_no" 
                                        class="form-control"
                                        placeholder="Auto-generated"
                                        readonly
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Row 3: Incident Type and Date of Incident -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="incident_type">Incident Type <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="incident_type" 
                                        name="incident_type" 
                                        class="form-control"
                                        placeholder="Enter incident type"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_incident">Date of Incident <span class="text-danger">*</span></label>
                                    <input 
                                        type="datetime-local" 
                                        id="date_incident" 
                                        name="date_incident" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Row 4: Location -->
                        <div class="form-group">
                            <label for="location">Location <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                id="location" 
                                name="location" 
                                class="form-control"
                                placeholder="Enter location of incident"
                                required
                            >
                        </div>
                        
                        <!-- Row 5: Incident Description -->
                        <div class="form-group">
                            <label for="incident_description">Incident Description <span class="text-danger">*</span></label>
                            <textarea 
                                id="incident_description" 
                                name="incident_description" 
                                rows="3"
                                class="form-control"
                                placeholder="Describe the incident in detail..."
                                required
                            ></textarea>
                        </div>
                        
                        <!-- Row 6: Name of Involved -->
                        <div class="form-group">
                            <label for="name_involved">Name of Involved <span class="text-danger">*</span></label>
                            <select 
                                id="name_involved" 
                                name="name_involved" 
                                class="form-control form-select"
                                required
                            >
                                <option value="">-- Select Employee --</option>
                                @forelse($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->emp_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                    </option>
                                @empty
                                    <option value="">No employees available</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Row 7: Name of Witness -->
                        <div class="form-group">
                            <label for="name_witness">Name of Witness</label>
                            <select 
                                id="name_witness" 
                                name="name_witness" 
                                class="form-control form-select"
                            >
                                <option value="">-- Select Employee --</option>
                                @forelse($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->emp_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                    </option>
                                @empty
                                    <option value="">No employees available</option>
                                @endforelse
                            </select>
                        </div>
                        
                        <!-- Row 8: Recommended Action -->
                        <div class="form-group">
                            <label for="recommended_action">Recommended Action <span class="text-danger">*</span></label>
                            <textarea 
                                id="recommended_action" 
                                name="recommended_action" 
                                rows="3"
                                class="form-control"
                                placeholder="Describe recommended actions to prevent future incidents..."
                                required
                            ></textarea>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="form-group">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                Back
                            </a>
                            <button 
                                type="reset" 
                                class="btn btn-secondary"
                            >
                                Clear
                            </button>
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                            >
                                Submit Report
                            </button>
                        </div>
                    </form>
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
        // Initialize Select2 for employee dropdowns with search
        $('#reported_by, #name_involved, #name_witness').select2({
            placeholder: "-- Select Employee --",
            allowClear: true,
            width: '100%'
        });

        // Auto-populate position when employee is selected in reported_by
        $('#reported_by').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const position = selectedOption.data('position');
            $('#position').val(position || '');
        });

        // If employee was pre-filled, trigger change to populate position
        if ($('#reported_by').val()) {
            $('#reported_by').trigger('change');
        }

        // Generate incident number on form load
        generateIncidentNumber();
    });

    function generateIncidentNumber() {
        // Generate format: INC-YYYYMMDD-NNNN (where NNNN is a random 4-digit number)
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const randomNum = String(Math.floor(Math.random() * 10000)).padStart(4, '0');
        
        const incidentNo = `INC-${year}${month}${day}-${randomNum}`;
        $('#incident_no').val(incidentNo);
    }
</script>
@stop
