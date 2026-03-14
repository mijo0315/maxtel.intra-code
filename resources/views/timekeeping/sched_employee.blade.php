<div class="row sched_tab" id="sched_by_employee">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card ">
            
            <div class="card-body">
                <div class="row">
                    
                        <div class="col-md-2">
						<label class="btn btn-info btn-md w-100">Employee</label>  
                        
                        </div>
						<!-- hide tab & select **add id="emp_div"-->
                        <div class="col-md-3" id="emp_div">
                            <select id="sched_emp" class="form-control form-select">
                                <option value="0">Select Employee</option>
                                
                                @foreach($tbl_employee as $emp)
                                    <option value="{{$emp->id}}">({{$emp->emp_code}}) {{$emp->last_name}}, {{$emp->first_name}} {{$emp->last_name}} {{$emp->middle_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2" id="set_def_emp_label">
					
						<label class="btn btn-info btn-md w-100">Default Schedule</label>  
                        </div>
                        <div class="col-md-3" id="emp_def_div">
                            <select id="emp_def_sched" class="form-control form-select">
                                    <option value="0">Select Default Schedule</option>
                                @foreach($lib_week_schedule as $sched)
                                    <option value="{{$sched->id}}">({{$sched->code}}) {{$sched->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="set_emp_sched" class="btn btn-apply btn-sm" onclick="set_emp_sched();">Set Schedule</button>
							
                        </div>
                </div>
				<div class="row mt-2">
                    <div class="col-md-3">
                        <label class="btn btn-info btn-sm w-100">Date Range</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="sched_emp_date_range" class="form-control" placeholder="Select date range" disabled>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-xl-12 col-sm-12 col-12">
                        <div id='sched_emp_calendar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="sched_by_employee_modal">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Set Schedule</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
            <div class="row">
				<div class="col-md-12">
					<label class="btn btn-sm btn-success w-100" id='by_emp_target_day'>DAY</label>
				</div>
				
			</div>
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Select Schedule</label>
				</div>
				<div class="col-md-8">
					<select id="by_emp_lib_schedule" class="form-control form-select" style="width:100%;">
                        <option value="0">Rest Day</option>
                        @foreach($lib_schedule as $sched_daily)
                            <option value="{{$sched_daily->id}}">({{$sched_daily->code}}) {{$sched_daily->name}}</option>
                        @endforeach
                    </select>
				</div>
			</div>
			<div id="flexi_schedule">
				<div class="row">
					<div class="col-md-4">
						<label class="btn btn-sm btn-info w-100">REQUIRED HOURS</label>
					</div>
					<div class="col-md-8">
						<input id="by_emp_required_hours" type="text" class="form-control mb-1" readonly>
					</div>
				 
				</div>
			</div>
			<div id="regular_schedule">
				<div class="row">
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">AM IN</label>
					</div>
					<div class="col-md-3">
						<input id="by_emp_am_in" type="text" class="form-control mb-1" readonly>
					</div>
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">AM OUT</label>
					</div>
					<div class="col-md-3">
						<input id="by_emp_am_out" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
	
				<div class="row">
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">PM IN</label>
					</div>
					<div class="col-md-3">
						<input id="by_emp_pm_in" type="text" class="form-control mb-1" readonly>
					</div>
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">PM OUT</label>
					</div>
					<div class="col-md-3">
						<input id="by_emp_pm_out" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
	
				<div class="row">
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">OT IN</label>
					</div>
					<div class="col-md-3">
						<input id="by_emp_ot_in" type="text" class="form-control mb-1" readonly>
					</div>
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">OT OUT</label>
					</div>
					<div class="col-md-3">
						<input id="by_emp_ot_out" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
	
				<div class="row">
					<div class="col-md-4">
						<label class="btn btn-sm btn-info w-100">GRACE PERIOD</label>
					</div>
					<div class="col-md-8">
						<input id="by_emp_grace_period" type="text" class="form-control mb-1" readonly>
					</div>
				 
				</div>
				<div class="row mt-2">
					<div class="col-md-4">
						<label class="btn btn-sm btn-info w-100">SELECT SITE</label>
					</div>
					<div class="col-md-8">
						<select id="by_emp_site" class="form-control form-select" style="width:100%;">
							<option value="">Select here</option>
							@foreach($tbl_branch as $branch)
								<option value="{{$branch->id}}">{{$branch->branch}}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
            
            
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="sched_daily_emp_success" class="btn btn-success">Submit</button>
          <button type="button" id="sched_daily_emp_delete" class="btn btn-warning">Delete</button>
          
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
  <div class="modal fade" tabindex="-1" role="dialog" id="sched_bulk_edit_modal">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Bulk Edit Schedule</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
            <div class="row">
				<div class="col-md-12">
					<label class="btn btn-sm btn-success w-100" id='bulk_edit_date_range'>DATE RANGE</label>
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Select Schedule</label>
				</div>
				<div class="col-md-8">
					<select id="bulk_emp_lib_schedule" class="form-control form-select" style="width:100%;">
                        <option value="0">Rest Day</option>
                        @foreach($lib_schedule as $sched_daily)
                            <option value="{{$sched_daily->id}}">({{$sched_daily->code}}) {{$sched_daily->name}}</option>
                        @endforeach
                    </select>
				</div>
			</div>
			<div id="bulk_flexi_schedule">
				<div class="row mt-2">
					<div class="col-md-4">
						<label class="btn btn-sm btn-info w-100">REQUIRED HOURS</label>
					</div>
					<div class="col-md-8">
						<input id="bulk_emp_required_hours" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
			</div>
			<div id="bulk_regular_schedule">
				<div class="row mt-2">
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">AM IN</label>
					</div>
					<div class="col-md-3">
						<input id="bulk_emp_am_in" type="text" class="form-control mb-1" readonly>
					</div>
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">AM OUT</label>
					</div>
					<div class="col-md-3">
						<input id="bulk_emp_am_out" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
	
				<div class="row mt-2">
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">PM IN</label>
					</div>
					<div class="col-md-3">
						<input id="bulk_emp_pm_in" type="text" class="form-control mb-1" readonly>
					</div>
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">PM OUT</label>
					</div>
					<div class="col-md-3">
						<input id="bulk_emp_pm_out" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
	
				<div class="row mt-2">
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">OT IN</label>
					</div>
					<div class="col-md-3">
						<input id="bulk_emp_ot_in" type="text" class="form-control mb-1" readonly>
					</div>
					<div class="col-md-3">
						<label class="btn btn-sm btn-info w-100">OT OUT</label>
					</div>
					<div class="col-md-3">
						<input id="bulk_emp_ot_out" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
	
				<div class="row mt-2">
					<div class="col-md-4">
						<label class="btn btn-sm btn-info w-100">GRACE PERIOD</label>
					</div>
					<div class="col-md-8">
						<input id="bulk_emp_grace_period" type="text" class="form-control mb-1" readonly>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-md-4">
						<label class="btn btn-sm btn-info w-100">SELECT SITE</label>
					</div>
					<div class="col-md-8">
						<select id="bulk_emp_site" class="form-control form-select" style="width:100%;">
							<option value="">Select here</option>
							@foreach($tbl_branch as $branch)
								<option value="{{$branch->id}}">{{$branch->branch}}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
		  <button type="button" id="bulk_sched_emp_submit" class="btn btn-success">Apply to All Dates</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>