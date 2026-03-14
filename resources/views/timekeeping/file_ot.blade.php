<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="ot_apply_tab">
    <div class="row">
      	<div class="col-xl-12 col-sm-12 col-12 mb-2">
            <a class="btn btn-apply btn-md ml-2"
                        data-toggle='modal' 
                        data-target='#ot_apply_modal'
                        data-id = "new"
                        data-emp_id = "0"
                        data-ot_type = "0"
                        data-ot_date = "{{date("Y-m-d")}}"                        
                        data-ot_from = "{{date("H:i")}}"                        
                        data-ot_to = "{{date("H:i")}}"       
						data-ot_site = ""          
                        data-reason =""
                        data-ot_status = "FILED"
                   >
                   
                        File Over Time
                    </a>
      </div>
      
        <div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card ">
                <div class="card-title">
                 
                </div>
                <div class="card-body">
					<div class="row" id="datepicker_div">
                        <div class="col-md-4">
                            <input type="text" id="date_range_ot" class="form-control date_range" placeholder="Select Date Range" /><br>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-xl-12 col-sm-12 col-12 table-responsive">
							<button class="btn btn-success d-none" id="export_ot_apply" style="margin-bottom: 20px;">Export To Excel</button><br>
                            <table class="table table-striped table-bordered table-hover" id="ot_apply_tbl">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
										<th>Site</th>
                                        <th>Over Time Type</th>
                                        <th>Date</th>
                                        <th>Time</th>
										<th>Reason</th>
                                        <th>Status</th>
                                        <th>Action</th>
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
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="ot_apply_modal">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Over Time Filling</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Select Employee</label>
				</div>
				<div class="col-md-8">
					<select id="ot_emp_name" class="form-control form-select" style="width:100%;">
                    </select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Over Time Type</label>
				</div>
				<div class="col-md-5">
					<select id="ot_type" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Over Time Type</option>
						@foreach($lib_ot_table as $ot)
							<option value="{{ $ot->code }}">{{ $ot->name }}</option>
						@endforeach
                    </select>
				</div>
			</div>
            <div class="row mb-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Date</label>
				</div>
				<div class="col-md-4">
					<input type="text" id="ot_date" class="form-control" placeholder="Date">
				</div>
			</div>
            <div class="row mb-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Time From</label>
				</div>
				<div class="col-md-4">
					<input type="time" id="ot_from" class="form-control" placeholder="OT From">
				</div>
			</div>
            <div class="row mb-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Time To</label>
				</div>
				<div class="col-md-4">
					<input type="time" id="ot_to" class="form-control" placeholder="OT To">
				</div>
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Reason</label>
				</div>
				<div class="col-md-8">
					<textarea id="ot_reason" class="form-control" cols="10" rows="3" placeholder="OT Reason"></textarea>
				</div>
			</div>
			<div class="row mb-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Select Site</label>
				</div>
				<div class="col-md-5">
					<select id="ot_site" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Site</option>
						@foreach($tbl_branch as $branch)
							<option value="{{$branch->id}}">{{$branch->branch}}</option>
						@endforeach
                    </select>
				</div>
			</div>
            <div class="row mt-1">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Status</label>
				</div>
				<div class="col-md-4">
                    <select class="form-control form-select" id="ot_status" style="width:100%">
                    </select>
				</div>
			</div>
			
            
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="ot_update_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>