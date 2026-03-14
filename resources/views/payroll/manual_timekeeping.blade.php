<div class="modal fade" id="manual_timekeeping" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="label_modal">ADD MANUAL TIME KEEPING</h5> 
       
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
            <input type="hidden" name='pay_id_tk' id="pay_id_tk"> 

            <div class="row">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Filter</label>
                </div>
                <div class="col-md-5">
                    <select name="filter_tk" style="width:100%" id="filter_tk" class="form-control form-select">
                    </select>
                </div>
            </div>


            <div class="row" id="select_employee_div_tk">
                <div class="col-md-3">
                    <label for="" id="select_label" class="btn btn-info btn-sm" style="float: right;">Select Tagging</label>
                </div>
                <div class="col-md-8" id="select_data_div_tk">
                    <select name="select_data_tk" style="width:100%" id="select_data_tk" class="form-control form-select">
                    </select>

                </div>

                <div class="col-md-8" id="select_emp_div_tk">
                    <select name="select_emp_tk" style="width:100%" id="select_emp_tk" class="form-control form-select" multiple>
                    </select>
                </div>
               
            </div>


            <div class="div" id="timekeeping_entry">                
                <div class="row mt-2">
                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Regular Hours Work (Hours)</label>
                    </div>
                    <div class="col-md-4" >
                        <input type="number" name="regular_manual" style="width:100%" id="regular_manual" class="form-control" placeholder="Regular Hours">
                    </div>

                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm w-100" style="float: right; cursor: default;">Lates (Minutes)</label>
                    </div>
                    <div class="col-md-3" >
                        <input type="number" name="lates" id="lates" style="width:100%"  class="form-control" placeholder="Lates (minutes)" >
                    </div>
                </div>

             
                <div class="row mt-2">
                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Regular Holiday (Hours)</label>
                    </div>
                    <div class="col-md-4" >
                        <input type="number" name="reg_hol" id="reg_hol" style="width:100%"  class="form-control" placeholder="Regular Holiday (hours)">
                    </div>

                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Special Holiday (Hours)</label>
                    </div>
                    <div class="col-md-3" >
                        <input type="number" name="spl_hol" id="spl_hol" style="width:100%"  class="form-control" placeholder="Special Holiday (hours)">
                    </div>

                  
                </div>


                <div class="row mt-2">
                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm w-100" style="float: right; cursor: default;">Regular OT (Hours)</label>
                    </div>
                    <div class="col-md-2" >
                        <input type="number" name="rot" id="rot" style="width:100%"  class="form-control" placeholder="Regular OT (hours)">
                    </div>

                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Special OT (Hours)</label>
                    </div>
                    <div class="col-md-2" >
                        <input type="number" name="sot" id="sot" style="width:100%"  class="form-control" placeholder="Special OT (hours)">
                    </div>

                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Night Diff (Hours)</label>
                    </div>
                    <div class="col-md-2" >
                        <input type="number" name="nd" id="nd" style="width:100%"  class="form-control" placeholder="Night Differential (hours)">
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Regular Leave (Hours)</label>
                    </div>
                    <div class="col-md-2" >
                        <input type="number" name="vl" id="vl" style="width:100%"  class="form-control" placeholder="Regular Leave (hours)">
                    </div>

                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Sick Leave (Hours)</label>
                    </div>
                    <div class="col-md-2" >
                        <input type="number" name="sl" id="sl" style="width:100%"  class="form-control" placeholder="Sick Leave (hours)">
                    </div>

                    <div class="col-md-2">
                        <label for="" class="btn btn-success btn-sm" style="float: right; cursor: default;">Special Leave (Hours)</label>
                    </div>
                    <div class="col-md-2" >
                        <input type="number" name="spl_leave" id="spl_leave" style="width:100%"  class="form-control" placeholder="Special Leave (hours)">
                    </div>
                </div>

                
            
            </div>

        



            <div class="row" id="inc_btn">
                <div class="col-md-3">

                </div>
                <div class="col-md-3 mt-1">
                    <button  type="button" id="add_timekeeping" class="btn btn-success btn-lg"> <i class="fas fa-plus-square"></i> Add Timekeeping</button>
                </div>
            </div>

            <div class="row" id="custom_tbl" class="ml-1">
                <div class="col-md-12">

                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="tagged_employee_tk">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Reg Hours</th>
                                            <th>Lates</th>
                                            <th>Reg OT</th>
                                            <th>Spl OT</th>
                                            <th>ND</th>

                                            <th>Reg Leave</th>
                                            <th>Sick Leave</th>
                                            <th>Spl Leave</th>
                                            
                                            <th>Reg Holiday</th>
                                            <th>Spl Holiday</th>
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
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>