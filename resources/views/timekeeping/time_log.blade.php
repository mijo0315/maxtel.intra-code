<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="in_out_tab">
    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card ">
            <div class="card-title">
                <label class='badge badge-success m-1'>Employee</label>
            </div>
            <div class="card-body">
              
                    
                        <select id="punch_emp" class="form-control form-select">
                            <option value='0'>Select Employee </option>
                        </select>
                   



            </div>
        </div>
        </div>
    </div>


    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card ">
            <div class="card-title">
                <label class='badge badge-success m-1'>Time Log (Punch IN/OUT)</label>
            </div>
            <div class="card-body">
                <div class="row">
                   
                    <div class="col-md-12">
                        <div class="flipTimer">
                            
                            <div style="float: right; margin-bottom:10px;" class="seconds"></div>
                            <div style="float: right; margin-right:10px;" class="minutes"></div>
                            <div style="float: right; margin-right:10px;" class="hours"></div>
                          </div>
                    </div>

                </div>




                <div id="flexi_schedule">
                    <div class="col-md-12" id="FLEX_IN">
                        <button value="FLEX_IN" class="in_out btn btn-success btn-lg w-100 h-100">
                            <i class="fas fa-sign-in-alt" id="start_time"> START TIME</i> 
                        </button>
                    </div>

                    <div class="col-md-12" id="FLEX_OUT">
                        <button value="FLEX_OUT" class="in_out btn btn-warning btn-lg w-100 h-100">
                            <i class="fas fa-sign-out-alt" id="end_time">BREAK/END TIME</i> 
                        </button>
                    </div>


                    <div class="col-md-4 offset-sm-4">
                       <h3><label class="badge badge-info w-100" id="hours_active"><i class="fas fa-user-clock"></i> 0 Hours Active</label></h3> 
                    </div>
                    

                </div>


                <div id="regular_schedule">
                    <div class="row">
                        <div class="col-md-6">
                            <button value="AM_IN" class="in_out btn btn-success btn-lg w-100 h-100">
                                <i class="fas fa-sign-in-alt" id="am_in_txt"> AM IN</i> 
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button value="AM_OUT" class="in_out btn btn-warning btn-lg w-100 h-100">
                                <i class="fas fa-sign-out-alt" id="am_out_txt"> AM OUT</i> 
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <button value="PM_IN" class="in_out btn btn-success btn-lg w-100 h-100">
                                <i class="fas fa-sign-in-alt" id="pm_in_txt"> PM IN</i> 
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button value="PM_OUT" class="in_out btn btn-warning btn-lg w-100 h-100">
                                <i class="fas fa-sign-out-alt" id="pm_out_txt"> PM OUT</i> 
                            </button>
                        </div>
                    </div>
    
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <button value="OT_IN" class="in_out btn btn-success btn-lg w-100 h-100">
                                <i class="fas fa-sign-in-alt" id="ot_in_txt"> OT IN</i> 
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button value="OT_OUT" class="in_out btn btn-warning btn-lg w-100 h-100">
                                <i class="fas fa-sign-out-alt" id="ot_out_txt"> OT OUT</i> 
                            </button>
                        </div>
                    </div>
                </div>

               

            </div>
        </div>
        </div>
    </div>


   
</div>

