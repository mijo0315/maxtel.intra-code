@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Dashboard
@stop
@section("styles")
<style>
	th{
		text-align: center;
	}
    .btn-check{
       display:none;
    }
    .dz-success-mark{
        display: none;
    }
    .dz-error-mark{
        display: none;
    }
    .card a {
        color: inherit;
    }
   
</style>
@stop
@section("content")
@if(preg_match("/R/i", Auth::user()->access[Route::current()->action["as"]]["access"])=="0")
                            
	{{Auth::user()->access[Route::current()->action["as"]]["access"]}}
	<div class="page-wrapper">
		<div class="content container-fluid">
			<div class="row">
				<div class="col-xl-12 col-sm-12 col-12 mb-4">
					<div class="row">
						<div class="col-xl-10 col-sm-8 col-12 ">
							<label >YOU HAVE NO PRIVILEDGE ON THIS PAGE </label>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@else
<div class="page-wrapper" id="dashboard_page">
    <div class="content container-fluid">
        <div class="page-name 	mb-4">
            <h4 class="m-0">Dashboard</h4>
            <label> {{date('D, d M Y')}}</label>
            
            
        </div>
        <div class="row mb-4">
            <div class="col-xl-9 col-sm-12 col-12" id="statistics_container">
                
                
                <div class="row mb-4">
                    <div class="col-xl-4 col-sm-12 col-12">
                        <a href="{{route('employees_management')}}" style="text-decoration: none;">
                            <div class="card board1 fill1 ">
                                <div class="card-body">
                                    <div class="card_widget_header">
                                        <label>Employees</label>
                                        <h4>{{$tbl_employee}}</h4>
                                    </div>
                                    <div class="card_widget_img">
                                        <img src="{{asset_with_env('assets/img/dash1.png')}}" alt="card-img" />
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-4 col-sm-12 col-12">
                        <a href="{{route('employees_management')}}?tab=profiling" style="text-decoration: none;">
                            <div class="card board1 fill1 ">
                                <div class="card-body">
                                    <div class="card_widget_header">
                                        <label>Departments</label>
                                        <h4>{{$department}}</h4>
                                    </div>
                                    <div class="card_widget_img">
                                        <img src="{{asset_with_env('assets/img/dash2.png')}}" alt="card-img" />
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-4 col-sm-12 col-12">
                        <div class="card board1 fill1 ">
                            <a href="{{route('leave_management')}}" style="text-decoration: none;">
                                <div class="card-body">
                                    <div class="card_widget_header">
                                        <label>Leaves</label>
                                        <h4>{{$leave_count}}</h4>
                                    </div>
                                    <div class="card_widget_img">
                                        <img src="{{asset_with_env('assets/img/dash3.png')}}" alt="card-img" />
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
						<div class="row mb-4">
							<div class="col-xl-4 col-sm-12 col-12">
								<div class="card board1 fill1 ">
                                    <a href="{{route('file_management')}}" style="text-decoration: none;">
                                        <div class="card-body">
                                            <div class="card_widget_header">
                                                <label>Document Files</label>
                                                <h4>{{$files}}</h4>
                                            </div>
                                            <div class="card_widget_img">
                                                <img src="{{asset_with_env('assets/img/dash4.png')}}" alt="card-img" />
                                            </div>
                                        </div>
                                    </a>
								</div>
							</div>
							<div class="col-xl-4 col-sm-12 col-12">
								<div class="card board1 fill1 ">
                                    <a href="{{route('loan_management')}}" style="text-decoration: none;">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Cash Advance</label>
											<h4>{{$loans}}</h4>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash5.png')}}" alt="card-img" />
										</div>
									</div>
                                    </a>
								</div>
							</div>
							<div class="col-xl-4 col-sm-12 col-12">
								<div class="card board1 fill1 ">
                                    <a href="{{route('employees_management')}}" style="text-decoration: none;">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Sites</label>
											<h4>{{$branches}}</h4>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash2.png')}}" alt="card-img" />
										</div>
                                        
									</div>
                                    </a>
								</div>
							</div>
						</div>

                        {{-- OT Approvals and Filed Leave for all roles except staff (role_id = 2) --}}
                        @php
                            $hide_approval_cards = Auth::user()->role_id == 2;
                        @endphp
                        @if(!$hide_approval_cards)
                        <div class="row mt-2">
                            <div class="col-xl-12 col-sm-12 col-12">
								<div class="row">
									<div class="col-xl-6 col-sm-12 col-12">
										<a href="{{route('timekeeping_management')}}" style="text-decoration: none;">
											<div class="card board1 fill1 ">
												<div class="card-body">
													<div class="card_widget_header">
														<label>OT Approvals</label>
														<h5 style="color:white; font-size: 24px; margin: 10px 0;"><i class="fas fa-clock mr-2"></i><span id="ot_approval_count">0</span></h5>
													</div>
													<div class="card_widget_img">
														<img src="{{asset_with_env('assets/img/dash5.png')}}" alt="card-img" />
													</div>
												</div>
											</div>
										</a>
									</div>
									<div class="col-xl-6 col-sm-12 col-12">
										<a href="{{route('leave_management')}}" style="text-decoration: none;">
											<div class="card board1 fill1 ">
												<div class="card-body">
													<div class="card_widget_header">
														<label>Filed Leave Approvals</label>
														<h5 style="color:white; font-size: 24px; margin: 10px 0;"><i class="fas fa-calendar-check mr-2"></i><span id="filed_leave_count">0</span></h5>
													</div>
													<div class="card_widget_img">
														<img src="{{asset_with_env('assets/img/dash3.png')}}" alt="card-img" />
													</div>
												</div>
											</div>
										</a>
									</div>
								</div>
							</div>
                        </div>
                        @endif
            </div>
            <div class="col-xl-9 col-sm-12 col-12" id="emp_info_container">
                        
                        <div class="row">
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Position</label>
											<h5 style="color:white;">{{Auth::user()->company["linked_employee"]["position"]}}</h5>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash6.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Designation</label>
											<h5 style="color:white;">{{Auth::user()->company["linked_employee"]["designation"]}}</h5>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash4.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                        </div>
							
                        <div class="row mt-2">
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Department</label>
                                            <h5 style="color:white;">{{Auth::user()->company["linked_employee"]["department"]}}</h5>
											
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash1.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Site</label>
											<h5 style="color:white;">{{Auth::user()->company["linked_employee"]["branch"]}}</h5>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash2.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                        </div>
                   
                        <div class="row mt-2">
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Leave Data</label>
											<h5 style="color:white;">{{$leave_count}} - Leave Used </h5>
                                            <h5 style="color:white;">{{$leave_total}} - Leave Credits</h5>
                                            
										</div>
										<div class="card_widget_img">
											<a href="{{route('leave_management')}}"> <img src="{{asset_with_env('assets/img/dash3.png')}}" alt="card-img" /> </a>
										</div>
									</div>
								</div>
							</div>
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Processed Payroll</label>
											<h5 style="color:white;">{{$payroll_processing}} on process </h5>
                                            <h5 style="color:white;"> {{$payroll_done}} Proccesed </h5>
                                            
										</div>
										<div class="card_widget_img">
										   <a href="{{route('report_management')}}">	<img src="{{asset_with_env('assets/img/dash5.png')}}" alt="card-img" /> </a>
										</div>
									</div>
								</div>
							</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-xl-12 col-sm-12 col-12">
								<div class="row">
									<div class="col-xl-4 col-sm-12 col-12">
										<a href="{{route('nte_management')}}" style="text-decoration: none;">
											<div class="card board1 fill1 ">
												<div class="card-body">
													<div class="card_widget_header">
														<label>NTE</label>
														<h5 style="color:white; font-size: 24px; margin: 10px 0;"><i class="fas fa-exclamation-circle mr-2"></i><span id="nte_count">0</span></h5>
													</div>
													<div class="card_widget_img">
														<img src="{{asset_with_env('assets/img/dash6.png')}}" alt="card-img" />
													</div>
												</div>
											</div>
										</a>
									</div>
									<div class="col-xl-4 col-sm-12 col-12">
										<a href="{{route('disciplinary')}}" style="text-decoration: none;">
											<div class="card board1 fill1 ">
												<div class="card-body">
													<div class="card_widget_header">
														<label>Disciplinary</label>
														<h5 style="color:white; font-size: 24px; margin: 10px 0;"><i class="fas fa-gavel mr-2"></i><span id="disciplinary_count">0</span></h5>
													</div>
													<div class="card_widget_img">
														<img src="{{asset_with_env('assets/img/dash1.png')}}" alt="card-img" />
													</div>
												</div>
											</div>
										</a>
									</div>
									<div class="col-xl-4 col-sm-12 col-12">
										<a href="{{route('performance_improvement')}}" style="text-decoration: none;">
											<div class="card board1 fill1 ">
												<div class="card-body">
													<div class="card_widget_header">
														<label>Performance</label>
														<h5 style="color:white; font-size: 24px; margin: 10px 0;"><i class="fas fa-chart-line mr-2"></i><span id="performance_count">0</span></h5>
													</div>
													<div class="card_widget_img">
														<img src="{{asset_with_env('assets/img/dash4.png')}}" alt="card-img" />
													</div>
												</div>
											</div>
										</a>
									</div>
								</div>
							</div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-xl-12 col-sm-12 col-12">
								<div class="card fill4 ">
									<div class="card-body">
                                        <h4 class="text-center" id="logTitle">Today's Log</h4><br>
                                        <table class="table table-striped table-bordered table-hover" id="raw_logs_tbl">
                                            <thead>
                                                <tr>
                                                    <th>Log State</th>
                                                    <th style="width:40%;">Date Time (Log)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
										<!-- <div class="card_widget_header">
											{{-- <label>Leave Data</label> --}}
                                            @if($logs > 0)
                                            <h4> Logged</h4>
                                            @else
                                            <h4>No Current Logs</h4>
                                            @endif
                                            
                                            
										</div>
										<div class="card_widget_img">
											<a href="{{route('timekeeping_management')}}"> <img width="100vw" src="{{asset_with_env('assets/img/profiles/timeIN.png')}}" alt="card-img" /> </a>
										</div> -->
									</div>
								</div>
							</div>
                        </div>
                   
            </div>
            <div class="col-xl-3 col-sm-12 col-12 d-flex">
                <div class="card flex-fill">
                    <div class="dashboard-profile">
                        <div class="dash-imgs text-center" style="background-color:transparent;">
                            <img src="{{ asset_with_env(str_replace('public/', '', Auth::user()->company['linked_employee']['profile_picture'])) }}" alt="profile" onerror="this.onerror=null;this.src='{{ asset_with_env(str_replace('public/', '', Auth::user()->company['logo_sub'])) }}'" />
                            @if(Auth::user()->company["linked_employee"]["id"] != "0")
                            
                            <label>Welcome {{Auth::user()->company["linked_employee"]["name"]}}</label>
                            <span>{{Auth::user()->company["linked_employee"]["position"]}}</span>
                            @else
                            
                            <label>Welcome Admin</label>
                            <span>Administrator</span>
                            @endif
                        </div>
                        <div class="dash-btns">
                            <a id="system_setting" class="btn btn-dashboard" href="{{route('system_management')}}"><i data-feather="settings"
                                    class="mr-1"></i>Settings</a>
                            <a class="btn btn-dashboard" href="{{route('log-out')}}"> <i data-feather="log-out"
                                    class="mr-1"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($can_view_graphs)
        <div class="row" id="graph_container">
            <div class="col-md-6 ">
                
                <div id="container"></div>
                
            </div>
             <div class="col-md-6">
                
                <div id="container_2"></div>
                
            </div> 
        </div>
        @endif

        @if($can_view_employee_status)
        <!-- Employee Status Tables -->
        <div class="row mt-4" id="employee_status_container">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Probationary Employees</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-hover" id="probationary_tbl">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Trainee Employees</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-hover" id="trainee_tbl">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Project Employees</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-hover" id="project_tbl">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Regular Employees</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-hover" id="regular_tbl">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> -->
        
    </div>
</div>
@endif
@stop
@section("scripts")
<script src="{{asset_with_env('plugins/highcharts/highcharts.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/variable-pie.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/exporting.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/export-data.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/accessibility.js')}}"></script>
    <script>
                function getRandomColor() {
                    // Generate random values for red, green, and blue channels
                    const red = Math.floor(Math.random() * 256);
                    const green = Math.floor(Math.random() * 256);
                    const blue = Math.floor(Math.random() * 256);
                    // Create the color string in hexadecimal format
                    const color = '#' + red.toString(16) + green.toString(16) + blue.toString(16);
                    return color;
                }
            $( document ).ready(function() {
                var emp_id = "{{Auth::user()->company['linked_employee']['id']}}";
                var today = new Date().toISOString().split('T')[0]; // Get today's date in 'YYYY-MM-DD' format
                
                // Initialize Employee Status Tables
                function initializeStatusTable(tableId, status) {
                    $("#" + tableId).DataTable({
                        "destroy": true,
                        "autoWidth": false,
                        "searching": true,
                        "paging": true,
                        "pageLength": 5,
                        "processing": true,
                        "ajax": {
                            "url": "{{ route('get_employees_by_status') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data": {
                                "_token": "{{ csrf_token() }}",
                                "status": status
                            },
                            "error": function(xhr, error, thrown) {
                                console.error("AJAX Error for " + tableId + ":", error);
                                console.error("Response:", xhr.responseText);
                            }
                        },
                        "columns": [
                            {'data': 'name'},
                            {
                                'data': 'id',
                                'render': function(data, type, row) {
                                    return '<button class="btn btn-sm btn-success pan-action-btn" data-employee-id="' + data + '" data-employee-name="' + row.name + '"><i class=""></i>PAN</button>';
                                }
                            }
                        ]
                    });
                }
                
                // Initialize status tables only if user can view them
                var can_view_employee_status = "{{ $can_view_employee_status ? 'true' : 'false' }}";
                if(can_view_employee_status === 'true') {
                    initializeStatusTable('probationary_tbl', 'Probationary');
                    initializeStatusTable('trainee_tbl', 'Trainee');
                    initializeStatusTable('project_tbl', 'Project Employee');
                    initializeStatusTable('regular_tbl', 'Regular');
                }
                
                // Handle PAN Action button click
                $(document).on('click', '.pan-action-btn', function() {
                    var employeeId = $(this).data('employee-id');
                    var employeeName = $(this).data('employee-name');
                    
                    // Redirect to Personnel Action Form with employee ID
                    window.location.href = "{{ route('personnel_action_form') }}?employee_id=" + employeeId + "&action=pan";
                });
                
                $("#raw_logs_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ route('raw_logs_tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}", 
                            "page": "{{Route::current()->action['as']}}",  
                            "emp_id": emp_id
                        },
                        "dataSrc": function (json) {
                            // Filter logs to only include today's logs
                            json.data = json.data.filter(function (log) {
                                return log.logs.startsWith(today); // Check if the log date starts with today's date
                            });
                            return json.data;
                        }
                    },
                    "columns":[
                        {'data': 'state'},
                        {'data': 'logs'}
                    ]
                });

                function getCurrentDay() {
                    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    const today = new Date();
                    return days[today.getDay()];
                }

                document.getElementById("logTitle").innerText = `Today's Log (${getCurrentDay()})`;
                
                var canViewGraphs = "{{ $can_view_graphs ? 'true' : 'false' }}";
                var canViewEmployeeStatus = "{{ $can_view_employee_status ? 'true' : 'false' }}";
                var roleId = "{{Auth::user()->role_id}}";
                
                // Hide/Show based on permissions
                if(canViewGraphs === 'false'){
                    $("#graph_container").hide("fast");
                }
                
                if(canViewEmployeeStatus === 'false'){
                    $("#employee_status_container").hide("fast");
                }
                
                // Original logic for role_id = 2 (employee info)
                if(roleId == 2){
                    $("#system_setting").hide("fast");
                    $("#statistics_container").hide("fast");
                    $("#emp_info_container").show("fast");
                }else{
                    $("#emp_info_container").hide("fast");
                    $("#statistics_container").show("fast");
                    $("#system_setting").show("fast");
                }
                
                // Only load charts if user can view graphs
                if(canViewGraphs === 'true') {
                    $.ajax({
                        url: "{{route('branch_per_emp')}}",
                        data: {
                         
                            _token : "{{csrf_token()}}", 
                        },
                            success: function (source) { 
                                var data_arr = [];
                                var color_arr = [];
                                $.each(source, function( index, value ) {
                                    const newItem = {
                                        name: value.name,
                                        y: Number(value.y),
                                    };
                                    data_arr.push(newItem);
                                    color_arr.push(getRandomColor());
                                });
                                        Highcharts.chart('container', {
                                        chart: {
                                            type: 'variablepie'
                                        },
                                        title: {
                                            text: 'Deployed Employee Per Site',
                                            align: 'left'
                                        },
                                        tooltip: {
                                            headerFormat: '',
                                            pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name}</b><br/>' +
                                                'Number of Employees: <b>{point.y}</b><br/>'
                                        },
                                        series: [{
                                            minPointSize: 10,
                                            innerSize: '80%',
                                            zMin: 0,
                                            name: 'countries',
                                            borderRadius: 5,
                                            data:data_arr ,
                                            colors: color_arr
                                        }]
                                        });
                                
                            },
                            dataType: 'json',
                            method: 'POST'
                        });
                        $.ajax({
                        url: "{{route('count_mwe')}}",
                        data: {
                         
                            _token : "{{csrf_token()}}", 
                        },
                            success: function (source) { 
                                var data_arr = [];
                                var color_arr = [];
                                $.each(source, function( index, value ) {
                                    const newItem = {
                                        name: value.name,
                                        y: Number(value.y),
                                    };
                                    data_arr.push(newItem);
                                    color_arr.push(getRandomColor());
                                });
                                        Highcharts.chart('container_2', {
                                            chart: {
                                            type: 'column'
                                        },
                                        title: {
                                            text: 'Minimum Wage Earners',
                                            align: 'left'
                                        },
                                        xAxis: {
                                            type: 'category',
                                            title: {
                                                text: 'Categories'
                                            }
                                        },
                                        yAxis: {
                                            title: {
                                                text: 'Values'
                                            }
                                        },
                                        tooltip: {
                                            headerFormat: '',
                                            pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name}</b><br/>' +
                                                'Number of Employees: <b>{point.y}</b><br/>'
                                        },
                                        series: [{
                                            name: 'Number of Employees',
                                            colorByPoint: true, 
                                            data:data_arr,
                                            colors: color_arr 
                                        }]
                                        });
                                
                            },
                            dataType: 'json',
                            method: 'POST'
                        });
                }

                    // Load employee management records count
                    var emp_id = "{{Auth::user()->company['linked_employee']['id']}}";
                    if(emp_id != "0") {
                        $.ajax({
                            url: "{{ route('get_employee_management_records_count') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                employee_id: emp_id
                            },
                            success: function(response) {
                                $('#nte_count').text(response.nte || 0);
                                $('#disciplinary_count').text(response.disciplinary || 0);
                                $('#performance_count').text(response.performance || 0);
                            },
                            dataType: 'json',
                            method: 'POST'
                        });
                        
                        // Fetch OT Approvals and Filed Leave counts for non-staff users
                        var roleId = "{{Auth::user()->role_id}}";
                        if(roleId != 2) {
                            $.ajax({
                                url: "{{ route('get_approvals_count') }}",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    $('#ot_approval_count').text(response.ot_approvals || 0);
                                    $('#filed_leave_count').text(response.filed_leaves || 0);
                                },
                                dataType: 'json',
                                method: 'POST'
                            });
                        }
                    }
            });
  
           
    </script>
@stop