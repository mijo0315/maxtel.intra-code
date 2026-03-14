<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="ot_table_tab">
    
    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card ">
                <div class="card-title">
                 
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                            <table class="table table-striped table-bordered table-hover" id="ot_table_tbl">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Rate</th>
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

<div class="modal fade" tabindex="-1" role="dialog" id="ot_table_modal">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Over Time Table</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Code</label>
				</div>
				<div class="col-md-4">
                    <input type="text" id="tbl_code" class="form-control" placeholder="OT Code" readonly>
				</div>
			</div>

            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">OT Name</label>
				</div>
				<div class="col-md-8">
					<input id="tbl_name" type="text" class="form-control mt-1 mb-1" placeholder="OT Name" readonly>
				</div>
			</div>

            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Rate</label>
				</div>
				<div class="col-md-4">
                    <input type="number" id="tbl_rate" class="form-control">
				</div>
			</div>

			

            

		</div>
		<div class="modal-footer">
			
		  <button type="button" id="ot_tbl_update_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>