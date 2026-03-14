<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="manual_tab">



    <div class="row" id="ma_in_out">

        <div class="col-xl-12 col-sm-12 col-12 ">

        <div class="card ">

            

            <div class="card-body">

                <div class="col-md-12">

                    <div class="card-body">

                        <div class="row" id="datepicker_div">

                            <input type="hidden" id="filter_type" value="">

                            <!-- Date Range Picker -->

                            <div class="col-md-3 mb-3">

                                <label for="manual_date_range">Date Range</label>

                                <input type="text" id="manual_date_range" class="form-control date_range" placeholder="Select Date Range" />

                            </div>



                            <!-- Selected Dates Dropdown -->

                            <div class="col-md-3 mb-3">

                                <label for="selected_dates">Select Specific Dates</label><br>

                                <select class="form-control form-select selected_dates" id="selected_dates" multiple required style="width: 250px;">

                                    <option disabled value="">Select Date Here</option>

                                </select>

                            </div>



                            <div class="col-md-3 mb-3" >

                                <label for="selected_dates">By Employee</label><br>

                                <select class="form-control form-select" id="manual_time_keeping_employee" style="width: 250px;">

                                    <option value="0">Select Employee</option>

                                    @foreach($tbl_employee as $emp)

                                    <option value="{{$emp->id}}">({{$emp->emp_code}}) {{$emp->last_name}}, {{$emp->first_name}} {{$emp->last_name}} {{$emp->middle_name}}</option>

                                @endforeach

                                </select>

                            </div>



                            <!-- Branch Selector -->

                            <div class="col-md-3 mb-3">

                                <label for="manual_time_keeping_branch">By Branch</label><br>

                                <select class="form-control form-select" id="manual_time_keeping_branch" style="width: 250px;">

                                    <option value="0">Select Branch</option>

                                    @foreach($tbl_branch as $branch)

                                        <option value="{{ $branch->id }}">({{ $branch->branch }})</option>

                                    @endforeach

                                </select>

                            </div>

                        </div>

                        

                        <div class="row">

                            

                                <div class="col-md-4" id="tk_div">

                                    <a class="btn btn-primary m-3 p-2"  

                                        data-toggle="modal" 

                                        data-target="#timeModal" 

                                        id="opentrigger"

                                        data-am_in = ""

                                        data-am_out = ""

                                        data-pm_in = ""

                                        data-pm_out = ""

                                        data-date_target = "{{date('Y-m-d')}}"

                                    

                                    >

                                        Add Manual Punch

                                    </a>   

                                </div>

    

                            

                        </div>

                        <br>

                        

                        

                        

                        <div class="row table-responsive" id="log_div">

                            <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">

                                <table class="table table-striped table-bordered table-hover" id="manual_logs_tbl">

                                    <thead>

                                        <tr>

                                            <th>Name</th>

                                            <th>Position</th>

                                            <th>Date</th>

                                            <th>AM IN (OFFICE IN)</th>

                                            <th>PM OUT (OFFICE OUT)</th>

                                            <th>Schedule</th>

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







<div class="modal fade" tabindex="-1" role="dialog" id="timeModal">



    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="timeModalLabel">Enter Work Hours</h5>

                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                    <input type="hidden" class="form-control" id="emp_id">

                    <div class="mb-3" id="emp_div">

                        <label for="name" class="form-label">Name</label>

                        <input type="text" class="form-control" id="name" readonly>

                    </div>

                    <div class="mb-3" id="emp_list_div">

                        <select class="form-control form-select emp_select" id="emp_select" required style="width: 100%;">

                            <option value="">Select Employee</option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label for="target_date" class="form-label">Target Date</label>

                        <input type="text" id="target_date" class="form-control" placeholder="Target Date">

                    </div>



                    <div class="mb-3">

                        <label for="amTimeIn" class="form-label">AM Time In</label>

                        <input type="datetime-local" class="form-control date_time" id="amTimeIn" required>

                    </div>

                    <!-- <div class="mb-3">

                        <label for="amTimeOut" class="form-label">AM Time Out</label>

                        <input type="datetime-local" class="form-control date_time" id="amTimeOut" required>

                    </div>

                    <div class="mb-3">

                        <label for="pmTimeIn" class="form-label">PM Time In</label>

                        <input type="datetime-local" class="form-control date_time" id="pmTimeIn" required>

                    </div> -->

                    <div class="mb-3">

                        <label for="pmTimeOut" class="form-label">PM Time Out</label>

                        <input type="datetime-local" class="form-control date_time" id="pmTimeOut" required>

                    </div>

                

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-primary w-50" id="add_manual_time">Save</button>

            </div>

        </div>

    </div>

</div>



<div class="modal fade" tabindex="-1" role="dialog" id="requestLogModal">



    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="timeModalLabel">Enter Work Hours</h5>

                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                    <div class="mb-3">

                        <input type="hidden" class="form-control" id="timecard_id">

                        <label for="target_date_req" class="form-label">Target Date</label>

                        <input type="date" class="form-control" id="target_date_req" value="{{date('Y-m-d')}}" required>

                    </div>



                    <div class="mb-3">

                        <label for="amTimeIn_req" class="form-label">AM Time In</label>

                        <input type="datetime-local" class="form-control date_time" id="amTimeIn_req" required>

                    </div>

                    <div class="mb-3">

                        <label for="amTimeOut_req" class="form-label">AM Time Out</label>

                        <input type="datetime-local" class="form-control date_time" id="amTimeOut_req" required>

                    </div>

                    <div class="mb-3">

                        <label for="pmTimeIn_req" class="form-label">PM Time In</label>

                        <input type="datetime-local" class="form-control date_time" id="pmTimeIn_req" required>

                    </div>

                    <div class="mb-3">

                        <label for="pmTimeOut_req" class="form-label">PM Time Out</label>

                        <input type="datetime-local" class="form-control date_time" id="pmTimeOut_req" required>

                    </div>

                

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-primary w-50" id="add_manual_time_request">Submit for Approval</button>

            </div>

        </div>

    </div>

</div>



