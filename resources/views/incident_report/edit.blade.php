
@extends('layouts.front-app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="card">
            <div class="card-header">
                <h4>Edit Incident Report</h4>
                <strong>{{ $report->document_number }}</strong>
            </div>

            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('incident-report.update', $report->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reported By</label>

                                <select name="reported_by"
                                        id="reported_by"
                                        class="form-control"
                                        required>
                                    <option value="">-- Select Employee --</option>

                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            data-position="{{ $emp->position_name ?? '' }}"
                                            {{ old('reported_by', $report->reported_by) == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->emp_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text"
                                       name="position"
                                       id="position"
                                       class="form-control"
                                       value="{{ old('position', $report->position) }}"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date and Time of Report</label>
                                <input type="datetime-local"
                                       name="date_time_report"
                                       class="form-control"
                                       value="{{ old('date_time_report', $report->date_time_report ? date('Y-m-d\TH:i', strtotime($report->date_time_report)) : '') }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Incident No.</label>
                                <input type="text"
                                       name="incident_no"
                                       class="form-control"
                                       value="{{ old('incident_no', $report->incident_no) }}"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Incident Type</label>
                                <input type="text"
                                       name="incident_type"
                                       class="form-control"
                                       value="{{ old('incident_type', $report->incident_type) }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Incident</label>
                                <input type="datetime-local"
                                       name="date_incident"
                                       class="form-control"
                                       value="{{ old('date_incident', $report->date_incident ? date('Y-m-d\TH:i', strtotime($report->date_incident)) : '') }}"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <input type="text"
                               name="location"
                               class="form-control"
                               value="{{ old('location', $report->location) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Incident Description</label>
                        <textarea name="incident_description"
                                  rows="4"
                                  class="form-control"
                                  required>{{ old('incident_description', $report->incident_description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Name of Involved</label>
                        <select name="name_involved[]"
                                id="name_involved"
                                class="form-control"
                                multiple
                                required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ in_array($emp->id, old('name_involved', $involvedIds)) ? 'selected' : '' }}>
                                    {{ $emp->emp_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Name of Witness</label>
                        <select name="name_witness[]"
                                id="name_witness"
                                class="form-control"
                                multiple>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ in_array($emp->id, old('name_witness', $witnessIds)) ? 'selected' : '' }}>
                                    {{ $emp->emp_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Recommended Action</label>
                        <textarea name="recommended_action"
                                  rows="4"
                                  class="form-control"
                                  required>{{ old('recommended_action', $report->recommended_action) }}</textarea>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('incident-report.index') }}"
                           class="btn btn-secondary">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i>
                            Update Incident Report
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#reported_by').select2({
            width: '100%',
            placeholder: '-- Select Employee --'
        });

        $('#name_involved').select2({
            width: '100%',
            placeholder: '-- Select Employees --',
            maximumSelectionLength: 10
        });

        $('#name_witness').select2({
            width: '100%',
            placeholder: '-- Select Witnesses --',
            maximumSelectionLength: 10
        });

        $('#reported_by').on('change', function() {
            const position = $(this).find('option:selected').data('position');
            $('#position').val(position || '');
        });
    });
</script>
@stop
