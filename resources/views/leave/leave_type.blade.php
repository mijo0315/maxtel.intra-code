<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="leave_table_tab">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card oth_income_card oth_library" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Manage Leave Type <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
            </div>
    <div class="row">
      	<div class="col-xl-12 col-sm-12 col-12 ">
           <a class="btn btn-apply btn-md mb-2 m-3"
                        data-toggle='modal' 
                        data-target='#leave_table_modal'
                        data-id = "new"
                        data-type = "0"
                        data-name = ""
                        data-require = "1"                        
                   >
                    
                        Add Leave Type
                    </a>
      </div>
      
        <div class="col-xl-12 col-sm-12 col-12 ">
        
              

                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                            <table class="table table-striped table-bordered table-hover" id="leave_type_tbl">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Leave Name</th>
                                        <th>Require Credits?</th>
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
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="leave_table_modal">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Leave Category</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Leave Type</label>
				</div>
				<div class="col-md-8">
					<select id="table_leave_type" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Leave Type</option>
                        <option value="VL">Vacation Leave</option>
                        <option value="SL">Sick Leave</option>
                        <option value="OL">Special Leave</option>
                    </select>
				</div>
			</div>

            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Leave Name</label>
				</div>
				<div class="col-md-8">
					<input id="table_leave_name" type="text" class="form-control mb-1" placeholder="Leave Name" >
				</div>
			</div>

            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Required Leave Credits?</label>
				</div>
				<div class="col-md-8">
					<select id="is_require" class="form-control form-select" style="width:100%;">
                        <option value="1">YES</option>
                        <option value="0">NO</option>
                        
                    </select>
				</div>
			</div>

			

            

		</div>
		<div class="modal-footer">
			
		  <button type="button" id="table_update_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>