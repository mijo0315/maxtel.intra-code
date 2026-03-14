<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="timekeeping_tab">
    <div class="row" id="prcs_raw_logs">
        <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card ">
            
            <div class="card-body">
                <div class="col-md-12">
                <label class="btn btn-info ">FROM</label>  
                  
                    <input type="text" id="timecard_from" value="{{date("Y-m-01")}}">
                    <label class="btn btn-info ">TO</label> 
                    <input type="text" id="timecard_to" value="{{date("Y-m-t")}}">
                    
        
                    <button id="process_raw_logs" class="btn btn-success ">  Process Raw Logs</button>
                </div>
            </div>
        </div>
        </div>
    </div>


    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card ">
                

                <div class="card-body">
                    <div class="row" id="datepicker_div">
                        <div class="col-md-4">
                            <input type="text" id="date_range" class="form-control date_range" placeholder="Select Date Range" /><br>
                        </div>
                    </div><br>
                    <div class="row">
                    <!-- hide select for employee -->
                        <div class="col-md-4" id="tk_div">
                            <select class="form-control form-select" id="time_keeping_employee">
                                <option value="0">Select Employee</option>
                                @foreach($tbl_employee as $emp)
                                <option value="{{$emp->id}}">({{$emp->emp_code}}) {{$emp->last_name}}, {{$emp->first_name}} {{$emp->last_name}} {{$emp->middle_name}}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <br>

                    <div class="row" id="log_div">
                        <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
                            <button class="btn btn-success" id="export_employee_btn">Export Employee Logs</button>
                            <button class="btn btn-info" id="export_all_btn">Export All Employees</button>
                            <button class="btn btn-warning" id="export_raw_logs_btn">Export All Raw Logs</button>
</br>
                            <br>

                            <table class="table table-striped table-bordered table-hover" id="raw_logs_tbl">
                                <thead>
                                    <tr>
                                        <th>Log State</th>
                                        <th>Date Time (Log)</th>
                                        <th>Location</th>
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

