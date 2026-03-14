<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="gsis_tab">
    
    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card ">
                <div class="card-title">
                   <h3 class="btn btn-info btn-lg w-100"> GSIS </h3>
                </div>

                <div class="card-body">
                    <div class="row">
                            <div class="col-md-2">
                                <label class="btn btn-info btn-md w-100">Employee Rate</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control text-right" id="gsis_emp_rate" placeholder="Employee Rate" value="{{Auth::user()->company['gsis_contribution']}}">
                            </div>
                        
                            <div class="col-md-2">
                                <label class="btn btn-info btn-md w-100">Company Rate</label>
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control text-right" id="gsis_com_rate" placeholder="Company Rate" value="{{Auth::user()->company['gsis_company']}}">
                            </div>
                       
                    </div>

                  
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-success btn-md float-right" onclick="gsis_update();">Update</button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>