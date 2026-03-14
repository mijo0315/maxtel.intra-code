<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="credit_table_tab">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card oth_income_card oth_library" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Manage Leave Credits <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
            </div>

    <div class="row">
      <div class="col-xl-12 col-sm-12 col-12 ">
        <a id="add_leave_credit" class="btn btn-apply btn-md m-3"
                        data-toggle='modal' 
                        data-target='#leave_credit_modal'
                        data-id = "new"
                        data-emp_id = "0"
                        data-leave_id = "0"
                        data-leave_count = "0"                        
                   >
                   
                        Add Leave Credit
                    </a>
      </div>
        <div class="col-xl-12 col-sm-12 col-12 ">
         
              

                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                            <table class="table table-striped table-bordered table-hover" id="credit_type_tbl">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Leave Name</th>
                                        <th>Starting Credit</th>
                                        <th>Balance</th>
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

<div class="modal fade" tabindex="-1" role="dialog" id="leave_credit_modal">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Leave Credit</h5>
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
					<select id="emp_name" class="form-control form-select" style="width:100%;" multiple>
                        @if(count($emp_list)>1)
                            <option value="all">All Employee</option>
                        @endif
            
                        @foreach($emp_list as $emp)
                            <option value="{{$emp->id}}">{{$emp->emp_code}} {{$emp->last_name}}, {{$emp->first_name}} {{$emp->middle_name}} {{$emp->ext_name}}</option>
                        @endforeach
                    </select>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Leave Type</label>
				</div>
				<div class="col-md-5">
					<select id="leave_type" class="form-control form-select" style="width:100%;">
                        <option value="0">Select Leave Type</option>
                        @foreach($leave_type as $type)
                            <option value="{{$type->id}}">{{$type->leave_name}}</option>
                        @endforeach
                    </select>
				</div>
			</div>

         
            

            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Leave Credit</label>
				</div>
				<div class="col-md-4">
					<input type="number" id="leave_credit" class="form-control">
                      
				</div>
			</div>

			

            

		</div>
		<div class="modal-footer">
			
		  <button type="button" id="credit_update_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>