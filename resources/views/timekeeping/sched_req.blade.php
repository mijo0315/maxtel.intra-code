<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="sched_req_tab">

    <div class="row" id="sched_req_row">
        <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card ">
            
            <div class="card-body">
                <div class="col-md-12">
                    <div class="card-body">
                        <div class="row" id="datepicker_div">
                            <div class="col-md-4">
                                <input type="text" id="sched_req_date_range" class="form-control date_range" placeholder="Select Date Range" />

                            </div>
                            <div class="col-md-4" id="tk_div">
                                <select class="form-control form-select" id="sched_req_employee">
                                    <option value="0">All Employee</option>
                                    @foreach($tbl_employee as $emp)
                                        <option value="{{$emp->id}}">({{$emp->emp_code}}) {{$emp->last_name}}, {{$emp->first_name}} {{$emp->last_name}} {{$emp->middle_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                         

                        </div>
                        
                        <div class="row">
                            
                                <div class="col-md-4" id="tk_div">
                                    <a class="btn btn-primary m-3 p-2"  
                                        data-toggle="modal" 
                                        data-target="#sched_req_modal" 
                                    >
                                        Request Schedule
                                    </a>   
                                </div>
    
                            
                        </div>
                        <br>
                        
                        
                        
                        <div class="row" id="log_div">
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                                <table class="table table-striped table-bordered table-hover" id="sched_req_tbl">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Target Date</th>
                                            <th>Schedule</th>
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
    </div>


</div>



<div class="modal fade" tabindex="-1" role="dialog" id="sched_req_modal">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeModalLabel">Change Schedule Request </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="mb-3">
                        <label for="target_date" class="form-label">Target Date</label>
                        <input type="date" class="form-control" id="sched_target_date" value="{{date('Y-m-d')}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="target_date" class="form-label">Schedule</label>
                        <select name="sched_req_select" id="sched_req_select" class="form-control form-select" style="width: 100%;">
                            <option value="0">Rest Day</option>
                            @foreach($lib_schedule as $sched)
                            <option value="{{$sched->id}}">{{$sched->name}}</option>

                            @endforeach

                        </select>
                    </div>

                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-50" id="sched_request">Request Schedule</button>
            </div>
        </div>
    </div>
</div>

