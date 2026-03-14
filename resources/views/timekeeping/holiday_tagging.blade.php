<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="holiday_tab">
    
    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card ">
               
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                            <div id='holiday_calendar'></div>
                        </div>
                        
                       
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="holiday_modal">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Set Holiday</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
            <div class="row">
				<div class="col-md-12">
					<label class="btn btn-sm btn-success w-100" id='holiday_target_day'>DAY</label>
				</div>
				
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Code</label>
				</div>
				<div class="col-md-8">
                    <input type="text" id="holiday_name" class="form-control" placeholder="Holiday Name">
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Type</label>
				</div>
				<div class="col-md-8">
                    <select id="holiday_type" class="form-control form-select" style="width:100%">
                        <option value="0">Select Holiday Type</option>
                        <option value="RH">Regular Holiday</option>
                        <option value="SH">Special Holiday</option>
                        
                    </select>
				</div>
			</div>
			<div class="row mt-2" id="delete_button_row" style="display: none;">
				<div class="col-md-12">
					<button type="button" id="holiday_delete_btn" class="btn btn-danger w-100" onclick="delete_holiday();">Delete Holiday</button>
				</div>
			</div>  
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="holiday_update_btn" onclick="set_holiday();" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>