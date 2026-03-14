@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Timekeeping
@stop
@section("styles")
<!-- Include Daterangepicker CSS -->
<link rel="stylesheet" type="text/css" href="{{ asset_with_env('assets/css/daterangepicker.css')}}" />
<style>
	th{
		text-align: center;
	}
	.fc-event-time {
		color:black;
	}
	.fc-event-title{
		color:black;
	}
	#clock {
      font-size: 48px;
      text-align: center;
    
    }
	.inline-am-in,
	.inline-pm-out {
		width: 160px;
		padding: 2px 4px;
		font-size: 0.9rem;
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
<div class="page-wrapper">
            <div class="content container-fluid">
					<div class="row">
						@if(Auth::user()->company['version'] == 1)
						
						@endif
						<div class="col-xl-12 col-sm-12 col-12 mb-4">
							<div class="head-link-set">
								<ul>
									<!-- hide time log update -->
									<!-- <li id='mnu_in_out_li'><a class="active mnu_btn" id="mnu_in_out"  onclick="">Time Log</a></li> -->
									
                                    <li id='mnu_timekeeping_li'><a class="active mnu_btn" id="mnu_timekeeping"  onclick="">Raw Logs</a></li>
									<li id='mnu_manual_in_out_li'><a class="mnu_btn" id="mnu_manual"  onclick="">Manual IN/OUT</a></li> 
									<li id='mnu_holiday_li'><a class="mnu_btn" id="mnu_holiday"  onclick='view_holiday("{{date("Y-m-01")}}")'>Holiday Tagging</a></li>
									<li id='mnu_scheduling_li'><a class="active mnu_btn" id="mnu_scheduling"  onclick="">Scheduling</a></li>
									<!--<li id='mnu_sched_req_li'><a class="mnu_btn" id="mnu_sched_req"  onclick="">Schedule Request</a></li> -->
									<li id='mnu_ot_apply_li'><a class="mnu_btn" id="mnu_ot_apply"  onclick="">OT Request</a></li>
									<li id='mnu_ot_table_li'><a class="mnu_btn" id="mnu_ot_table"  onclick="">OT Table</a></li>
								</ul>
							</div>
						</div>
						<!-- @include("timekeeping.time_log") -->
						 @include("timekeeping.manual_in_out")
						@include("timekeeping.time_keeping")
						@include("timekeeping.ot_table")
						@include("timekeeping.scheduling")
						@include("timekeeping.file_ot")
						@include("timekeeping.holiday_tagging")
						<!--@include("timekeeping.sched_req")-->
						
                    </div>
            </div>
</div>
@endif
@stop
@section("scripts")
<!-- Include Moment.js -->
<script src="{{ asset_with_env('assets/js/moment.min.js')}}"></script>
<!-- Include Daterangepicker JS -->
<script src="{{ asset_with_env('assets/js/daterangepicker.min.js')}}"></script>
<script>
	$("#process_raw_logs").on("click", function(){
		var tc_from = $("#timecard_from").val();
		var tc_to = $("#timecard_to").val();
		$.confirm({
						title: 'Process',
						content: 'Process Raw Logs',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Update",
								action: function(){
									// HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('process_raw_logs')}}",
										data: {
											_token : "{{csrf_token()}}", 
											tc_from: tc_from,
											tc_to: tc_to
											
										},
											success: function (data) { 
												$.notify(data, {type:"info",icon:"info"}); 
												
												// HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
	});
	$("#time_keeping_employee").on("change", function(){
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
					"emp_id": $(this).val(),
					"date_range": $('#date_range').val()
				}
			},
			"columns":[
				{'data': 'state'},
				{'data': 'logs'},
				{'data': 'location', 'orderable': false, 'searchable': false}
			]
		});
	});
	$('#export_employee_btn').on('click', function() {
		var emp_id = $('#time_keeping_employee').val();
        var date_range = $('#date_range').val();
        var branch_id = $('#branch_filter_logs').val();
        if (emp_id != 0) {
            var url = "{{ route('export_logs', ['emp_id' => '__emp_id__']) }}".replace('__emp_id__', emp_id);
            if (date_range) {
                url += "?date_range=" + encodeURIComponent(date_range);
            }
            window.location.href = url;
        } else {
            alert("Please select an employee first.");
        }
	});
	$('#export_all_btn').on('click', function() {
		var date_range = $('#date_range').val();
		var branch_id = $('#branch_filter_logs').val();
		var url = "{{ route('export_all_logs') }}";
		if (date_range || branch_id) {
			var params = [];
			if (date_range) {
				params.push("date_range=" + encodeURIComponent(date_range));
			}
			if (branch_id) {
				params.push("branch_id=" + encodeURIComponent(branch_id));
			}
			url += "?" + params.join("&");
		}
		window.location.href = url;
	});
	$('#export_raw_logs_btn').on('click', function() {
		var date_range = $('#date_range').val();
		var url = "{{ route('export_raw_logs') }}";
		if (date_range) {
			url += "?date_range=" + encodeURIComponent(date_range);
		}
		window.location.href = url;
	});
</script>
<script>
view_holiday('{{date("Y-m-01")}}');
		function view_holiday(month_view){
			console.log(month_view);
			// month_view = '2024-01-01';
			var SITE_URL = "{{url('/')}}";
			var calendarEl_holiday = document.getElementById('holiday_calendar');
				var calendar_holiday = new FullCalendar.Calendar(calendarEl_holiday, {
				initialDate: month_view,
				editable: false,
				selectable: false,
				businessHours: true,
				dayMaxEvents: true, // allow "more" link when too many events
				events: function(info, successCallback, failureCallback) {
					var start = info.start;
					$.ajax({
						url:  SITE_URL+"/get_holiday/"+start.toISOString()+"/"+"{{Route::current()->action['as']}}",
						success: function(events) {
							successCallback(events);
						},
						error: function() {
							failureCallback('Error fetching events');
						}
						});
					
				},
					
				dateClick: function(info) {
						var current_user = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
				
					if( current_user != "employee"){
						var is_edit = "1";
					}else{
						var is_edit = "0";
					}
					if (info.event == null) {
						// Call your function here
						var day = info.dateStr;
						set_daily_holiday(day,is_edit,"0","");
					}
					},
					eventClick: function(info) {
						var event = info.event;
						var day = event.start;
						var is_edit = event.extendedProps.is_edit;
						var type = event.extendedProps.type;
						var name = event.extendedProps.name;
						var holiday_id = event.extendedProps.holiday_id;
						if(is_edit == 1){
							set_daily_holiday(day,is_edit,type,name,holiday_id);
						}
						
					}
				
				
				});
				calendar_holiday.render();
		}
		//Delete function for Holiday
		function delete_holiday() {
			var holiday_id = $('#holiday_delete_btn').data('holiday_id');
			if (!holiday_id) {
				alert('No holiday selected');
				return;
			}
			$.ajax({
				url: "{{ route('delete_holiday') }}",
				type: 'POST',
				data: {
					holiday_id: holiday_id,
					_token: "{{ csrf_token() }}"
				},
				success: function(response) {
					if (response.success) {
						alert('Holiday deleted successfully');
						$('#holiday_modal').modal('hide');
						view_holiday('{{date("Y-m-01")}}');  // Refresh the calendar
					} else {
						alert('Failed to delete holiday');
					}
				},
				error: function() {
					alert('Error while deleting the holiday');
				}
			});
		}
</script>
<script>
		$("#sched_emp").on("change", function(){
			var emp_id = $("#sched_emp").val();
			if(emp_id == "0"){
				// Clear calendar and disable date range
				$("#sched_emp_calendar").empty();
				$("#sched_emp_date_range").val("").prop("disabled", true);
				return;
			}
			// Enable date range picker
			$("#sched_emp_date_range").prop("disabled", false);
				//get default employee schedule
				$.ajax({
					url: "{{route('get_emp_default_schedule')}}",
				data: {
					_token : "{{csrf_token()}}", 
					emp_id: emp_id
					
				},
					success: function (data) { 
						$("#emp_def_sched").val(data).change();
					
					},
					dataType: 'json',
					method: 'POST'
				});
			
				view_emp_schedule(emp_id, '{{date("Y-m-01")}}');	
		});
		// Date range picker for employee schedule
		$('#sched_emp_date_range').daterangepicker({
			locale: {
				format: 'YYYY-MM-DD',
			},
			autoUpdateInput: false,
		});
		$('#sched_emp_date_range').on('apply.daterangepicker', function (ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
			var emp_id = $("#sched_emp").val();
			// Store the date range in global variable for calendar filtering
			window.sched_emp_date_range = {
				start: picker.startDate.format('YYYY-MM-DD'),
				end: picker.endDate.format('YYYY-MM-DD')
			};
			if(emp_id != "0"){
				view_emp_schedule(emp_id, picker.startDate.format('YYYY-MM-01'));
				// Open bulk edit modal
				setTimeout(function(){
					open_bulk_edit_modal(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
				}, 500);
			}
		});
		$('#sched_emp_date_range').on('cancel.daterangepicker', function () {
			$(this).val('');
			var emp_id = $("#sched_emp").val();
			// Clear the date range filter
			window.sched_emp_date_range = null;
			if(emp_id != "0"){
				view_emp_schedule(emp_id, '{{date("Y-m-01")}}');
			}
		});
		function set_daily_holiday(day,is_edit,type,name, holiday_id = null){
		
		var day = new Date(day);
		const m = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		var format_day =  (m[day.getMonth()]) + ' ' + day.getDate() + ', ' + day.getFullYear();
		
		$("#holiday_target_day").empty().text(format_day);
		$("#holiday_update_btn").val(day);
		$("#holiday_type").val(type).change();
		$("#holiday_name").val(name);
		if (holiday_id) {
			$('#delete_button_row').show();
			$('#holiday_delete_btn').data('holiday_id', holiday_id); // Store the holiday ID in the delete button
		} else {
			$('#delete_button_row').hide();
		}
		
		if(is_edit == "1"){
			$("#holiday_modal").modal("show");
		}
	}
	function set_holiday(){
			var holiday_name = $("#holiday_name").val();
			var holiday_target_day = $("#holiday_target_day").text();
			var holiday_type = $("#holiday_type").val();
				if(holiday_name == ""){
					$.notify("Please indicate Holiday Name", {type:"danger",icon:"info"}); 
					return;
				}
				if(holiday_target_day == "DAY"){
					$.notify("Failure on setting day", {type:"danger",icon:"info"}); 
					return;
				}
			
			$.confirm({
						title: 'Holiday',
						content: 'Set Holiday for '+holiday_target_day,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Set Holiday",
								action: function(){
									HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('set_holiday')}}",
										data: {
											_token : "{{csrf_token()}}", 
											holiday_target_day: holiday_target_day,
											holiday_name: holiday_name,
											holiday_type: holiday_type
										},
											success: function (data) { 
												if(data != "failed"){
													$.notify("Success", {type:"info",icon:"info"}); 
													view_holiday(data);
													$("#holiday_modal").modal("hide");
												}else{
													$.notify("Failed", {type:"info",icon:"info"}); 
												}
											
												HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
		}
		function set_emp_sched(){
			var emp_def_sched = $("#emp_def_sched").val();
			var emp_id = $("#sched_emp").val();
				if(emp_id == "0"){
					$.notify("Please Select Employee", {type:"danger",icon:"info"}); 
					return;
				}
			
			$.confirm({
						title: 'Scheduling',
						content: 'Set Employee Default Schedule',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Update",
								action: function(){
									HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('update_emp_def_schedule')}}",
										data: {
											_token : "{{csrf_token()}}", 
											emp_id: emp_id,
											emp_def_sched: emp_def_sched
											
										},
											success: function (data) { 
												$.notify(data, {type:"info",icon:"info"}); 
												view_emp_schedule(emp_id, '{{date("Y-m-01")}}');
												HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
		}
		
		
		
		function view_emp_schedule(emp_id, month_view){
			var SITE_URL = "{{url('/')}}";
			var calendarEl = document.getElementById('sched_emp_calendar');
				var calendar = new FullCalendar.Calendar(calendarEl, {
				initialDate: month_view,
				editable: false,
				selectable: true,
				businessHours: true,
				dayMaxEvents: true, // allow "more" link when too many events
				events: function(info, successCallback, failureCallback) {
					var start = info.start;
					$.ajax({
						url:  SITE_URL+"/get_schedule/"+emp_id+"/"+start.toISOString(),
						success: function(events) {
							successCallback(events);
						},
						error: function() {
							failureCallback('Error fetching events');
						}
						});
					
				},
					
				
					eventClick: function(info) {
						var event = info.event;
						var sched_id = event.extendedProps.sched_id;
						var branch_id = event.extendedProps.branch_id;
						var is_assigned = event.extendedProps.dailyAssigned;
						var day = event.start;
						if(is_assigned != null){
							set_daily_sched(emp_id,day,sched_id, is_assigned, branch_id);
						}
						
					}
				
				
				});
				calendar.render();
		}
				
</script>
<script>
	$("#by_emp_lib_schedule").on("change", function(){
		var sched_id = $(this).val();
		HoldOn.open(holdon_option);
		$.ajax({
			url: "{{route('get_daily_sched_info')}}",
		data: {
			_token : "{{csrf_token()}}", 
			id: sched_id
			
		},
			success: function (data) { 
					if(data["is_flexi"] == 1){
						$("#flexi_schedule").show("fast");
						$("#regular_schedule").hide("fast");
					}else{
						$("#flexi_schedule").hide("fast");
						$("#regular_schedule").show("fast");
					}
					$("#by_emp_am_in").val(data["am_in"]);
					$("#by_emp_am_out").val(data["am_out"]);
					$("#by_emp_pm_in").val(data["pm_in"]);
					$("#by_emp_pm_out").val(data["pm_out"]);
					$("#by_emp_ot_in").val(data["ot_in"]);
					$("#by_emp_ot_out").val(data["ot_out"]);
					$("#by_emp_grace_period").val(data["grace_period"]);
					$("#by_emp_required_hours").val(data["required_hours"]);
					
				HoldOn.close();
			},
			dataType: 'json',
			method: 'POST'
		});
	});
	$("#sched_daily_emp_delete").on("click", function(){
		var target_day = $("#by_emp_target_day").text();
		var emp_id = $("#sched_emp").val();
		if(emp_id == "0"){
			$.notify("Please Select Employee", {type:"danger",icon:"info"}); 
			return;
		}
		if(target_day == "DAY"){
			$.notify("Failure on setting day", {type:"danger",icon:"info"}); 
			return;
		}
			$.confirm({
						title: 'Scheduling',
						content: 'Delete Schedule for '+target_day,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-red',
								text: "Delete",
								action: function(){
									HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('delete_daily_sched')}}",
										data: {
											_token : "{{csrf_token()}}", 
											target_day: target_day,
											emp_id: emp_id,
											
										},
											success: function (data) { 
												$.notify("SUCCESS", {type:"info",icon:"info"}); 
												$("#sched_by_employee_modal").modal("hide");
												view_emp_schedule(emp_id, data);
												HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
		
	});
	$("#sched_daily_emp_success").on("click", function(){
		var by_emp_lib_schedule =  $("#by_emp_lib_schedule").val();
		var target_day = $("#by_emp_target_day").text();
		var emp_id = $("#sched_emp").val();
		var branch_id = $("#by_emp_site").val();
		if(by_emp_lib_schedule == "0"){
			// $.notify("Please Select Schedule", {type:"danger",icon:"info"}); 
			// return;
		}
		if(emp_id == "0"){
			$.notify("Please Select Employee", {type:"danger",icon:"info"}); 
			return;
		}
		if(target_day == "DAY"){
			$.notify("Failure on setting day", {type:"danger",icon:"info"}); 
			return;
		}
			$.confirm({
						title: 'Scheduling',
						content: 'Set Schedule for '+target_day,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Submit",
								action: function(){
									HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('set_daily_sched')}}",
										data: {
											_token : "{{csrf_token()}}", 
											by_emp_lib_schedule: by_emp_lib_schedule,
											target_day: target_day,
											emp_id: emp_id,
											branch_id: branch_id
											
										},
											success: function (data) { 
												$.notify("Success", {type:"info",icon:"info"}); 
												$("#sched_by_employee_modal").modal("hide");
												view_emp_schedule(emp_id, data);
												HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
		
	});
	function set_daily_sched(emp_id,day,sched_id, is_assigned, branch_id){
		var role_id = "{{ Auth::user()->role_id }}";
		var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
		if(user_type == "employee"){
			if(role_id == "2"){
				return;
			}
		}
		// Check if date range is set and if selected day is within range
		if(window.sched_emp_date_range){
			var selectedDay = moment(day).format('YYYY-MM-DD');
			var rangeStart = moment(window.sched_emp_date_range.start).format('YYYY-MM-DD');
			var rangeEnd = moment(window.sched_emp_date_range.end).format('YYYY-MM-DD');
			if(selectedDay < rangeStart || selectedDay > rangeEnd){
				$.notify("Selected date is outside the date range filter", {type:"warning",icon:"info"}); 
				return;
			}
		}
		if(is_assigned == "1"){
			$("#sched_daily_emp_delete").show("fast");
			$("#sched_daily_emp_delete").val(day);
		}else{
			$("#sched_daily_emp_delete").hide("fast");
		}
		var day = new Date(day);
		const m = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		var format_day =  (m[day.getMonth()]) + ' ' + day.getDate() + ', ' + day.getFullYear();
		
		$("#by_emp_target_day").empty().text(format_day);
		$("#by_emp_lib_schedule").val(sched_id).change();
		$("#by_emp_site").val(branch_id).change();
		$("#sched_daily_emp_success").val(day);
		
		$("#sched_by_employee_modal").modal("show");
	}
	// Bulk edit modal functions
	function open_bulk_edit_modal(start_date, end_date){
		$("#bulk_edit_date_range").text(start_date + ' to ' + end_date);
		$("#bulk_emp_lib_schedule").val(0).change();
		$("#sched_bulk_edit_modal").modal("show");
	}
	$("#bulk_emp_lib_schedule").on("change", function(){
		var sched_id = $(this).val();
		if(sched_id == "0"){
			$("#bulk_flexi_schedule").hide();
			$("#bulk_regular_schedule").hide();
			return;
		}
		HoldOn.open(holdon_option);
		$.ajax({
			url: "{{route('get_daily_sched_info')}}",
			data: {
				_token : "{{csrf_token()}}", 
				id: sched_id
			},
			success: function (data) { 
				if(data["is_flexi"] == 1){
					$("#bulk_flexi_schedule").show("fast");
					$("#bulk_regular_schedule").hide("fast");
				}else{
					$("#bulk_flexi_schedule").hide("fast");
					$("#bulk_regular_schedule").show("fast");
				}
				$("#bulk_emp_am_in").val(data["am_in"]);
				$("#bulk_emp_am_out").val(data["am_out"]);
				$("#bulk_emp_pm_in").val(data["pm_in"]);
				$("#bulk_emp_pm_out").val(data["pm_out"]);
				$("#bulk_emp_ot_in").val(data["ot_in"]);
				$("#bulk_emp_ot_out").val(data["ot_out"]);
				$("#bulk_emp_grace_period").val(data["grace_period"]);
				$("#bulk_emp_required_hours").val(data["required_hours"]);
				HoldOn.close();
			},
			dataType: 'json',
			method: 'POST'
		});
	});
	$("#bulk_sched_emp_submit").on("click", function(){
		var bulk_emp_lib_schedule = $("#bulk_emp_lib_schedule").val();
		var emp_id = $("#sched_emp").val();
		var branch_id = $("#bulk_emp_site").val();
		if(emp_id == "0"){
			$.notify("Please Select Employee", {type:"danger",icon:"info"}); 
			return;
		}
		if(!window.sched_emp_date_range){
			$.notify("Date range not selected", {type:"danger",icon:"info"}); 
			return;
		}
		var start_date = window.sched_emp_date_range.start;
		var end_date = window.sched_emp_date_range.end;
		$.confirm({
			title: 'Bulk Edit Schedule',
			content: 'Apply schedule to all dates from ' + start_date + ' to ' + end_date + '?',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: 'btn-green',
					text: "Apply",
					action: function(){
						HoldOn.open(holdon_option);
						var current_date = moment(start_date);
						var end = moment(end_date);
						var count = 0;
						// Loop through each date in range and apply schedule
						while(current_date.isSameOrBefore(end)){
							var target_day = current_date.format('YYYY-MM-DD');
							$.ajax({
								url: "{{route('set_daily_sched')}}",
								data: {
									_token : "{{csrf_token()}}", 
									by_emp_lib_schedule: bulk_emp_lib_schedule,
									target_day: target_day,
									emp_id: emp_id,
									branch_id: branch_id
								},
								method: 'POST'
							});
							current_date.add(1, 'days');
							count++;
						}
						setTimeout(function(){
							$.notify("Applied schedule to " + count + " dates", {type:"success",icon:"info"}); 
							$("#sched_bulk_edit_modal").modal("hide");
							var emp_id = $("#sched_emp").val();
							view_emp_schedule(emp_id, moment(start_date).format('YYYY-MM-01'));
							HoldOn.close();
						}, 1000);
					}
				},
				cancelAction: {
					btnClass: 'btn-gray',
					text: 'Cancel',
					action: function(){}
				}
			}
		});
	});
	$( document ).ready(function() {
		//add date range picker
		// Initialize the daterangepicker
		$('.date_range').daterangepicker({
			locale: {
				format: 'YYYY-MM-DD',
			},
			autoUpdateInput: false,
		});
		$("#opentrigger").hide("fast");
		
		// Update table when the user selects a date range
		$('#date_range').on('apply.daterangepicker', function (ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
			load_logs_tbl();
		});
		//cancel date range
		$('#date_range').on('cancel.daterangepicker', function () {
			$(this).val('');
			load_logs_tbl();
		});
		$('#date_range_ot').on('apply.daterangepicker', function (ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
			applied_ot_tbl();
		});
		//cancel date range
		$('#date_range_ot').on('cancel.daterangepicker', function () {
			$(this).val('');
			applied_ot_tbl();
		});
		$('#sched_req_date_range').on('apply.daterangepicker', function (ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
			sched_req_tbl();
		});
		//cancel date range
		$('#sched_req_date_range').on('cancel.daterangepicker', function () {
			$(this).val('');
			sched_req_tbl();
		});
		$('#manual_date_range').on('apply.daterangepicker', function (ev, picker) {
			const startDate = picker.startDate.clone();
			const endDate = picker.endDate.clone();
			$(this).val(startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('YYYY-MM-DD'));
			appendDatesToSelect(startDate, endDate);
			load_processed_tbl();
		});
		//cancel date range
		$('#manual_date_range').on('cancel.daterangepicker', function () {
			$(this).val('');
			$('#selected_dates').empty().append('<option value="">Select Date</option>');
			load_processed_tbl();
		});
		function appendDatesToSelect(start, end) {
			$('#selected_dates').empty().append('<option value="">Select Date</option>');
			const current = start.clone();
			while (current.isSameOrBefore(end)) {
				const formatted = current.format('YYYY-MM-DD');
				$('#selected_dates').append('<option value="' + formatted + '">' + formatted + '</option>');
				current.add(1, 'days');
			}
		}
		$("#manual_time_keeping_employee").on("change", function(){
			$('#manual_time_keeping_branch').val(null).trigger('change.select2');
			$("#filter_type").val("by_employee");
			if($(this).val()!=="0"){
				$("#opentrigger").show("fast");
			}else{
				$("#opentrigger").hide("fast");
			}
			load_processed_tbl();
			var branch_id = "0";
    		load_employee(branch_id);
		});
		$("#manual_time_keeping_branch").on("change", function(){
			$('#manual_time_keeping_employee').val(null).trigger('change.select2');
			$("#filter_type").val("by_branch");
			if($(this).val()!=="0"){
				$("#opentrigger").show("fast");
			}else{
				$("#opentrigger").hide("fast");
			}
			load_processed_tbl();
			var branch_id = $(this).val();
    		load_employee(branch_id);
		});
		$(".selected_dates").on("change", function(){
			load_processed_tbl();
			// console.log($(this).val())
		});
		$(".emp_select").on("change", function(){
			var emp_id = $(this).val();
			$("#emp_id").val(emp_id);
		});
	
		$("#add_manual_time").on("click", function(){
		var emp_id = $("#emp_id").val();
		var amTimeIn = $("#amTimeIn").val();
		// var amTimeOut = $("#amTimeOut").val();
		// var pmTimeIn = $("#pmTimeIn").val();
		var pmTimeOut = $("#pmTimeOut").val();
		var date_target = $("#target_date").val();
		$.confirm({
						title: 'INSERT MANUAL IN/OUT',
						content: 'Update IN/OUT',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Update",
								action: function(){
									
									$.ajax({
											url: "{{route('manual_in_out')}}",
										data: {
											_token : "{{csrf_token()}}", 
											amTimeIn: amTimeIn,
											// amTimeOut: amTimeOut,
											// pmTimeIn: pmTimeIn,
											pmTimeOut: pmTimeOut,
											date_target: date_target,
											emp_id:emp_id
										},
											success: function (data) { 											
												load_processed_tbl();
												$.notify(data, {type:"info",icon:"info"}); 
												$('#timeModal').modal("hide");
												
												
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
	});
	$("#add_manual_time_request").on("click", function(){
		var submitBtnVal = this.textContent;
		var amTimeIn = $("#amTimeIn_req").val();
		var amTimeOut = $("#amTimeOut_req").val();
		var pmTimeIn = $("#pmTimeIn_req").val();
		var pmTimeOut = $("#pmTimeOut_req").val();
		var date_target = $("#target_date_req").val();
		var timecard_id = $("#timecard_id").val();
		var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
		var swal_text = '';
		if(user_type == "employee"){
			swal_text = 'SUBMIT';
		}else{
			swal_text = 'APPROVED';
		}
		$.confirm({
			title: swal_text,
			content: 'REQUEST IN/OUT',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: 'btn-green',
					text: "Submit",
					action: function(){
						
						$.ajax({
								url: "{{route('manual_in_out_request')}}",
							data: {
								_token : "{{csrf_token()}}", 
								amTimeIn: amTimeIn,
								amTimeOut: amTimeOut,
								pmTimeIn: pmTimeIn,
								pmTimeOut: pmTimeOut,
								date_target: date_target,
								timecard_id:timecard_id,
								submitBtnVal: submitBtnVal
							},
								success: function (data) { 											
									load_processed_tbl();
									$.notify(data, {type:"info",icon:"info"}); 
									$('#requestLogModal').modal("hide");
									
									
								},
								dataType: 'json',
								method: 'POST'
							});
					}
					
				},
				cancelAction: {
					btnClass: 'btn-gray',
					text: 'Cancel',
					action: function(){
					
					}  
				}
			}
		});
	});
		const defaultStart = moment("{{ date('Y-m-01') }}");
    	const defaultEnd = moment("{{ date('Y-m-d') }}");
    	$("#manual_date_range").val(defaultStart.format('YYYY-MM-DD') + ' - ' + defaultEnd.format('YYYY-MM-DD'));
    	// Append default range dates to #selected_dates
    	appendDatesToSelect(defaultStart, defaultEnd);
		//$("#manual_date_range").val('{{date("Y-m-01")}}' + ' - ' + '{{date("Y-m-d")}}');
		$("#date_range").val('{{date("Y-m-01")}}' + ' - ' + '{{date("Y-m-d")}}');
		$("#date_range_ot").val('{{date("Y-m-01")}}' + ' - ' + '{{date("Y-m-d")}}');
		$("#sched_req_date_range").val('{{date("Y-m-01")}}' + ' - ' + '{{date("Y-m-d")}}');
		//reload raw logs tbl
		function load_processed_tbl() {
			var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
			var emp_id = "";
			if(user_type == "employee"){
				emp_id = "{{Auth::user()->company['linked_employee']['id']}}";
				// $('#manual_logs_tbl thead th:last').hide(); 
			}else{
				emp_id = $("#manual_time_keeping_employee").val();
				// $('#manual_logs_tbl thead th:last').show(); 
			}
			var type = $("#filter_type").val();
			var branch_id = $("#manual_time_keeping_branch").val();
			var columns = [
				{ 'data': 'name' },
				{ 'data': 'position' },
				{ 'data': 'target_date' },
				{ 'data': 'AM_IN' },
				{ 'data': 'PM_OUT' },
				{ 'data': 'schedule' },
				{ 'data': 'action' }
			];
			// If the user_type is not "employee", add the action column
			// if (user_type !== "employee") {
			// 	columns.push({ 'data': 'action' });
			// }
			$("#manual_logs_tbl").DataTable({
				"bDestroy": true,
				"autoWidth": false,
				"searchHighlight": true,
				"searching": true,
				"processing": true,
				"serverSide": true,
				"orderMulti": true,
				"order": [[1, "asc"], [2, "asc"]],
				"pageLength": 10,
				"ajax": {
					"url": "{{ route('timecard_logs_tbl') }}",
					"dataType": "json",
					"type": "POST",
					"data": function (d) {
						d._token = "{{ csrf_token() }}";
						d.page = "{{Route::current()->action['as']}}";
						d.branch_id = branch_id; //date range admin
						d.emp_id = emp_id;
						d.date_range = $('#manual_date_range').val();
						d.type = type;
						let selectedDates = $('#selected_dates').val(); 
						if (selectedDates && !Array.isArray(selectedDates)) {
							selectedDates = [selectedDates]; 
						}
						d.selected_dates = selectedDates;
					}
				},
			    "columns": columns
			});
		}
		function load_employee(branch_id) {
			var type = $("#filter_type").val();
			var emp_id = $("#manual_time_keeping_employee").val();
			$.ajax({
				url: '/get-employees-by-branch',
				type: 'GET',
				data: { branch_id: branch_id },
				success: function(response) {
					const $empSelect = $('.emp_select');
					$empSelect.empty();
					if (type === 'by_employee') {
						$.each(response, function(index, employee) {
							let empCode = employee.emp_code ? '(' + employee.emp_code + ') ' : '';
							let middleInitial = employee.middle_name ? ' ' + employee.middle_name.charAt(0) + '.' : '';
							let extName = employee.ext_name ? ', ' + employee.ext_name : '';
							let fullName = empCode + employee.last_name + ', ' + employee.first_name + middleInitial + extName;
							// check if this employee should be selected
							let isSelected = String(employee.id) === emp_id ? 'selected' : '';
							$empSelect.append(`<option value="${employee.id}" ${isSelected}>${fullName}</option>`);
						});
					} else {
						$empSelect.append('<option value="">Select Employee</option>');
						$.each(response, function(index, employee) {
							let empCode = employee.emp_code ? '(' + employee.emp_code + ') ' : '';
							let middleInitial = employee.middle_name ? ' ' + employee.middle_name.charAt(0) + '.' : '';
							let extName = employee.ext_name ? ', ' + employee.ext_name : '';
							let fullName = empCode + employee.last_name + ', ' + employee.first_name + middleInitial + extName;
							$empSelect.append(`<option value="${employee.id}">${fullName}</option>`);
						});
						$empSelect.prop('disabled', false);
					}
				},
				error: function(xhr) {
					console.log("Error fetching employees:", xhr.responseText);
				}
			});
		}
		//reload raw logs tbl
		function load_logs_tbl() {
			var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
			var emp_id = "";
			if(user_type == "employee"){
				emp_id = "{{Auth::user()->company['linked_employee']['id']}}";
			}else{
				emp_id = $("#time_keeping_employee").val();
			}
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
					"data": function (d) {
						d._token = "{{ csrf_token() }}";
						d.page = "{{Route::current()->action['as']}}";
						d.emp_id = emp_id; //date range admin
						d.date_range = $('#date_range').val();
					}
				},
				"columns": [
					{ 'data': 'state' },
					{ 'data': 'logs' },
					{'data': 'location', 'orderable': false, 'searchable': false}
				]
			});
		}
		//end here date range picker
		$('#manual_logs_tbl').on('change', '.inline-am-in, .inline-pm-out', function () {
			let input = $(this);
			let timeType = input.hasClass('inline-am-in') ? 'AM_IN' : 'PM_OUT';
			let id = input.data('id');
			let value = input.val();
			$.ajax({
				url: "{{ route('manual_in_out_inline') }}", // create this route
				method: 'POST',
				data: {
					_token: "{{ csrf_token() }}",
					id: id,
					time_type: timeType,
					time_value: value
				},
				success: function (response) {
					$.notify("Updated successfully", { type: "success" });
				},
				error: function () {
					$.notify("Update failed", { type: "danger" });
				}
			});
		});
		$('#manual_logs_tbl').on('change', '.inline-schedule', function () {
			let select = $(this);
			let emp_id = select.data('emp-id');
			let schedule_date = select.data('target-date');
			let schedule_id = select.val();
			$.ajax({
				url: "{{ route('manual_update_schedule') }}", // You'll create this route
				method: 'POST',
				data: {
					_token: "{{ csrf_token() }}",
					emp_id: emp_id,
					schedule_date: schedule_date,
					schedule_id: schedule_id
				},
				success: function (response) {
					$.notify("Schedule updated successfully", { type: "success" });
				},
				error: function () {
					$.notify("Failed to update schedule", { type: "danger" });
				}
			});
		});
		
		// update sched tab
		var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
		var role_id = "{{ Auth::user()->role_id }}";
		if(user_type == "employee"){
			if(role_id == "2"){
				$('#sched_emp').prop('selectedIndex', 1);
				$('#sched_emp').prop('disabled', true); 
				var emp_id = $('#sched_emp').val();
				view_emp_schedule(emp_id, '{{date("Y-m-01")}}');
				$("#scheduling_tab").hide();
			}else{
				$("#scheduling_tab").show();
				$("#mnu_scheduling").click();
			}
			
			$("#emp_div").hide(); // hide tab & select
			$("#tk_div").hide(); // hide select for employee
			$("#export_all_btn").hide();
			$("#mnu_timekeeping_li").hide();
			$("#mnu_manual_in_out_li").hide();
			$("#timekeeping_tab").hide();
			$("#scheduling_tab").show();
		}
		if (user_type != "employee") {
			document.getElementById("add_manual_time_request").textContent = "Approved";
			$("#scheduling_tab").hide();
			$("#mnu_scheduling").removeClass('active');
			if(role_id == "14" || role_id == "15"){
				$("#emp_div").hide(); // hide tab & select
				$("#tk_div").hide(); // hide select for employee
				$("#export_all_btn").hide();
				$("#mnu_timekeeping_li").hide();
				$("#mnu_manual_in_out_li").hide();
				$("#timekeeping_tab").hide();
				$("#scheduling_tab").show();
				$("#mnu_scheduling").click();
			}
		}
		// hide time log update
		$("#holiday_tab").hide();
		$("#manual_tab").hide();
		// $("#scheduling_tab").hide();
		$("#ot_apply_tab").hide();
		$("#ot_table_tab").hide();
	    $( "#ot_date" ).datepicker({ dateFormat: 'yy-mm-dd' });
	    $( "#target_date" ).datepicker({ dateFormat: 'yy-mm-dd' });
	  
		  $("#mnu_in_out").click();
		// $("#mnu_ot_apply").click();
		  $("#sub_sched_by_employee").click();
		  $( "#timecard_from" ).datepicker({ dateFormat: 'yy-mm-dd' });
		  $( "#timecard_to" ).datepicker({dateFormat: 'yy-mm-dd' });
			var linked_employee = "{{Auth::user()->company['linked_employee']['id']}}";
			$("#profile_picture").val(linked_employee).change();
			
			if(user_type == "employee"){
				// $("#datepicker_div").show("fast");
				$("#prcs_raw_logs").hide("fast");
				$("#mnu_ot_table_li").hide("fast");
				$("#mnu_holiday_li").hide("fast");
				if(role_id == "2"){
					$("#set_def_emp_label").hide("fast");
					$("#emp_def_div").hide("fast");
					$("#set_emp_sched").hide("fast");
				}
				$("#sub_sched_by_employee_li").hide("fast"); // hide tab & select
				$("#sub_sched_by_position_li").hide("fast");
				$("#sub_sched_by_designation_li").hide("fast");
				$("#sub_sched_by_department_li").hide("fast");
				$("#sub_sched_by_branch_li").hide("fast");
				$("#ot_status").empty();
				$("#ot_status").append('<option value="FILED">Filed</option>');
				// All approvers dito
				var approverIds = ["6", "7", "8", "9", "10", "11", "12","13"];
				// 1st approvers dito
				var firstApproverIds = ["6", "7", "8", "9"];
				var finalApproverIds = ["10", "11", "12","13"];
				if (approverIds.includes(role_id)) {
					if (firstApproverIds.includes(role_id)) {
						//first approver
						$("#ot_status").append('<option value="1st_Approved">Approved</option>');
					} else if (finalApproverIds.includes(role_id)) {
						//final approver
						$("#ot_status").append('<option value="APPROVED">Approved</option>');
					}
					$("#ot_status").append('<option value="REJECTED">Rejected</option>');
				}
				
			}else{
				// $("#datepicker_div").hide("fast");
				$("#prcs_raw_logs").show("fast");
				$("#mnu_ot_table_li").show("fast");
				$("#mnu_holiday_li").show("fast");
				// $("#mnu_manual_in_out_li").show("fast");
				
				$("#sub_sched_by_position_li").show("fast");
				$("#sub_sched_by_designation_li").show("fast");
				$("#sub_sched_by_department_li").show("fast");
				$("#sub_sched_by_branch_li").show("fast");
				$("#set_def_emp_label").show("fast");
				$("#emp_def_div").show("fast");
				$("#set_emp_sched").show("fast");
				$("#ot_status").empty();
				$("#ot_status").append('<option value="FILED">Filed</option>');
				$("#ot_status").append('<option value="APPROVED">Approved</option>');
				$("#ot_status").append('<option value="REJECTED">Rejected</option>');
				if(role_id == "14" || role_id == "15"){
					$("#prcs_raw_logs").hide("fast");
					$("#mnu_ot_table_li").hide("fast");
					$("#mnu_holiday_li").hide("fast");
					$("#sub_sched_by_employee_li").hide("fast"); // hide tab & select
					$("#sub_sched_by_position_li").hide("fast");
					$("#sub_sched_by_designation_li").hide("fast");
					$("#sub_sched_by_department_li").hide("fast");
					$("#sub_sched_by_branch_li").hide("fast");
					$("#set_def_emp_label").hide("fast");
					$("#emp_def_div").hide("fast");
					$("#set_emp_sched").hide("fast");
				}
				
				
			}
	
                      
                        
                        
                 
			var user_count  = {{count($tbl_employee)}};
			if(user_count == 1){
				@if(isset($tbl_employee[0]->id))
					var curr_id = {{$tbl_employee[0]->id}};
				@else
					var curr_id =0;
				@endif
				
				$("#time_keeping_employee").val(curr_id).change();
				$("#manual_time_keeping_employee").val(curr_id).change();
		
			}
		  $(".form-select").select2();
		  ot_table();
		  applied_ot_tbl();
		
			$('.flipTimer').flipTimer({ direction: 'up' });
           
			
	  });
	$("#punch_emp").on("change", function(){
		get_temp_log();
	});
	function get_temp_log(){
		var punch_emp = $("#punch_emp").val();
		if(punch_emp == "0"){
			$.notify("Please Select Employee", {type:"info",icon:"info"}); 
		}
		$.ajax({
			url: "{{route('get_temp_log')}}",
			data: {
				_token : "{{csrf_token()}}", 
				"page": "{{Route::current()->action['as']}}",  
				"punch_emp" : punch_emp
			},
				success: function (data) { 
					if(data == "No Biometric ID"){
						$.notify(data, {type:"info",icon:"info"}); 
						$("#am_in_txt").empty().append("AM IN");
						$("#am_out_txt").empty().append("AM OUT");
						$("#pm_in_txt").empty().append("PM IN");
						$("#pm_out_txt").empty().append("PM OUT");
						$("#ot_in_txt").empty().append("OT IN");
						$("#ot_out_txt").empty().append("OT OUT");
						return;
					}
					
					if(data["flexi"] == 1){
						$("#flexi_schedule").show("fast");
						$("#regular_schedule").hide("fast");
							if(data["flex_state"] == "FLEX_IN"){
								$("#FLEX_IN").hide("fast");
								$("#FLEX_OUT").show("fast");
							}else{
								$("#FLEX_IN").show("fast");
								$("#FLEX_OUT").hide("fast");
							}
						
						  var active_data =	'<i class="fas fa-user-clock"></i> '+data["consumed"]+' Hours Active';
							$("#hours_active").empty().append(active_data);
					}else{
						$("#regular_schedule").show("fast");
						$("#flexi_schedule").hide("fast");
						
						if(data["AM_IN"] != ""){$("#am_in_txt").empty().append("AM IN ("+data["AM_IN"]+")");}else{$("#am_in_txt").empty().append("AM IN");}
						if(data["AM_OUT"] != ""){$("#am_out_txt").empty().append("AM OUT ("+data["AM_OUT"]+")");}else{$("#am_out_txt").empty().append("AM OUT");}
						if(data["PM_IN"] != ""){$("#pm_in_txt").empty().append("PM IN ("+data["PM_IN"]+")");}else{$("#pm_in_txt").empty().append("PM IN");}
						if(data["PM_OUT"] != ""){$("#pm_out_txt").empty().append("PM OUT ("+data["PM_OUT"]+")");}else{$("#pm_out_txt").empty().append("PM OUT");}
						if(data["OT_IN"] != ""){$("#ot_in_txt").empty().append("OT IN ("+data["OT_IN"]+")");}else{$("#ot_in_txt").empty().append("OT IN");}
						if(data["OT_OUT"] != ""){$("#ot_out_txt").empty().append("OT OUT ("+data["OT_OUT"]+")");}else{$("#ot_out_txt").empty().append("OT OUT");}
					}
						
			
				},
				dataType: 'json',
				method: 'POST'
			});
	}
	  $(".in_out").on("click", function(){
		var state = $(this).val();
		var emp_id = $("#punch_emp").val();
		$.confirm({
						title: 'Punch IN/OUT',
						content: 'Punch '+state,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Apply",
								action: function(){
									// HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('punch_in_out_ins')}}",
										data: {
											_token : "{{csrf_token()}}", 
											state: state,
											emp_id: emp_id
										},
											success: function (data) { 
												if(data == "No Biometric ID"){
													$.notify(data, {type:"info",icon:"info"}); 
													return;
												}else{
													$.notify(data, {type:"info",icon:"info"}); 
												}
												
												get_temp_log();
												// HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
				
	  });
	  $(".mnu_btn").on("click", function(){
		  $(".mnu_btn").removeAttr("class");
		  $(".stat_tab").hide("fast");
		  var mnu_data = $(this).attr("id");
		  $(this).attr("class", "active mnu_btn");
		  mnu_data = mnu_data.replace("mnu_","");
		
		if(mnu_data == "scheduling"){
			var role_id = "{{ Auth::user()->role_id }}";
			var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
			if(user_type == "employee"){
				if(role_id == "2"){
					$('#sched_emp').prop('selectedIndex', 1);
					$('#sched_emp').prop('disabled', true); 
					var emp_id = $('#sched_emp').val();				
					view_emp_schedule(emp_id, '{{date("Y-m-01")}}');
				}
			}
		}
		  
		  
		  $("#"+mnu_data+"_tab").show("fast");
		if(mnu_data == "in_out"){
			$.ajax({
			url: "{{route('get_punch_in_out_emp')}}",
			data: {
				_token : "{{csrf_token()}}", 
				"page": "{{Route::current()->action['as']}}",  
			},
				success: function (data) { 
					$("#punch_emp").empty();
					$.each(data, function( index, value ) {
						if(value.ext_name == null){
                                value.ext_name = "";
                            }
                            $("#punch_emp").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                            
                    });
					get_temp_log();
			
				},
				dataType: 'json',
				method: 'POST'
			});
		}
	  });
	  $(".sub_mnu").on("click", function(){
		  $(".sub_mnu").removeAttr("class");
		  $(".sched_tab").hide("fast");
		  var mnu_data = $(this).attr("id");
		  $(this).attr("class", "active sub_mnu");
		  mnu_data = mnu_data.replace("sub_","");
		  $("#"+mnu_data).show("fast");
		  if(mnu_data != "sched_by_employee"){
			sched_by(mnu_data);
		  }
	
	
	  });
</script>
<script>
	function change_sched(by_id,sched_id,mnu_data){
	HoldOn.open(holdon_option);
	var select_sched = "schedule_"+mnu_data+"_"+by_id;
		$("#"+select_sched).attr("style", "border-color:orange;box-shadow: 5px 10px orange;");
		$.ajax({
			url: "{{route('update_schedule_by')}}",
		data: {
			_token : "{{csrf_token()}}", 
			id: by_id,
			sched_id: sched_id,
			sched_by: mnu_data
			
		},
			success: function (data) { 
				if(data == "true"){
					$("#"+select_sched).attr("style", "border-color:green;");
				}else{
					$.notify(data, {type:"info",icon:"info"}); 
					$("#"+select_sched).attr("style", "border-color:red;box-shadow: 5px 10px red;");
				}
				HoldOn.close();
			},
			dataType: 'json',
			method: 'POST'
		});
	}
	function sched_by(mnu_data){
	   
	   $('#'+mnu_data+"_tbl").DataTable({
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
				"url": "{{ route('sched_by') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}",  
					"sched_by": mnu_data
				}
			},
			"columns":[
				{'data': 'name'},
				{'data': 'schedule'}
			]
		});
	}
	function applied_ot_tbl(){
	
	   $('#ot_apply_tbl').DataTable({
			"bDestroy": true,
			"autoWidth": false,
			"searchHighlight": true,
			"stateSave": true,
			"searching": true,
			"processing": true,
			"serverSide": true,
			"orderMulti": true,
			"order": [],
			"pageLength": 10,
			"ajax": {
				"url": "{{ route('applied_ot_tbl') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}",  
					"date_range": $('#date_range_ot').val()
				}
			},
			"columns":[
				{'data': 'name'},
				{'data': 'ot_site'},
				{'data': 'ot_type'},
				{'data': 'ot_date'},
				{'data': 'ot_time'},
				{'data': 'ot_reason'},
				{'data': 'ot_status'},
				{'data': 'action'},
				
			]
		});
	}
	$("#ot_update_btn").on("click", function(){
		var role_id = "{{Auth::user()->role_id}}";
		var branch_id = "{{Auth::user()->company['linked_employee']['branch_id']}}";
		var ot_id = $("#ot_update_btn").val();
		var ot_emp_name = $("#ot_emp_name").val();
		var ot_type = $("#ot_type").val();
		var ot_date = $("#ot_date").val();
		var ot_from = $("#ot_from").val();
		var ot_to = $("#ot_to").val();
		var ot_reason = $("#ot_reason").val();
		var ot_site = $("#ot_site").val();
		var ot_status = $("#ot_status").val();
		var approverIds = ["11", "12"];//managers dorcas at ajes
		if (approverIds.includes(role_id)) { //emp id 102, 21
			if(ot_emp_name === 21 || ot_emp_name === 102){
				ot_status = "1st_Approved";
			}
		}
		if(role_id == "2" && (branch_id == "75" || branch_id == "76" || branch_id == "82")){ //for employees final app only dorcas at ajes
			ot_status = "1st_Approved";
		}
		if(ot_emp_name == "0"){
			$.notify("Please select employee", {type:"danger",icon:"info"}); 
			return;
		}
		if(ot_type == "0"){
			$.notify("Please select Over Time Type", {type:"danger",icon:"info"}); 
			return;
		}
		if(ot_reason == ""){
			$.notify("Please input reason", {type:"danger",icon:"info"}); 
			return;
		}
		$.confirm({
						title: 'Over Time',
						content: 'Apply Over Time',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Apply",
								action: function(){
									// HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('apply_ot')}}",
										data: {
											_token : "{{csrf_token()}}", 
											ot_id: ot_id,
											ot_emp_name: ot_emp_name,
											ot_type: ot_type,
											ot_date: ot_date,
											ot_from: ot_from,
											ot_to: ot_to,
											ot_reason: ot_reason,
											ot_site: ot_site,
											ot_status: ot_status,
											
										},
											success: function (data) { 
												if(data == "Success"){
													$.notify(data, {type:"info",icon:"info"}); 
													$('#ot_apply_modal').modal("hide");
													applied_ot_tbl();
												}else{
													$.notify(data, {type:"info",icon:"info"}); 
												}
												
												
												// HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
	});
	$('#ot_apply_modal').on('show.bs.modal', function(e) {
	    $('#ot_emp_name').select2({ dropdownParent: $('#ot_apply_modal') });
		$('#ot_site').select2({ dropdownParent: $('#ot_apply_modal') });
	
		var id = $(e.relatedTarget).data('id');
		var emp_id = $(e.relatedTarget).data('emp_id');
		var ot_type = $(e.relatedTarget).data('ot_type');
		var ot_date = $(e.relatedTarget).data('ot_date');
		var ot_from = $(e.relatedTarget).data('ot_from');
		var ot_to = $(e.relatedTarget).data('ot_to');
		var reason = $(e.relatedTarget).data('reason');
		var ot_site = $(e.relatedTarget).data('ot_site');
		var ot_status = $(e.relatedTarget).data('ot_status');
		$.ajax({
				url: "{{route('leave_employee_list')}}",
			data: {
				_token : "{{csrf_token()}}", 
				page: "{{Route::current()->action['as']}}"
			},
				success: function (data) { 
					$("#ot_emp_name").empty().append("<option value='0'>Select Employee </option> ");
					$.each(data, function( index, value ) {
                            if(value.ext_name == null){
                                value.ext_name = "";
                            }
                            $("#ot_emp_name").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                            
                            });
							$("#ot_emp_name").val(emp_id).change();
							$("#ot_type").val(ot_type).change();
				},
				dataType: 'json',
				method: 'POST'
			});
			
			$("#ot_date").val(ot_date);
			$("#ot_from").val(ot_from);
			$("#ot_to").val(ot_to);
			$("#ot_reason").val(reason);
			$("#ot_site").val(ot_site).change();
			$("#ot_status").val(ot_status).change();
			
			$("#ot_update_btn").val(id);
			
	});
	$("#sched_request").on("click", function(){
		var sched_target_date = $("#sched_target_date").val();
		var sched_req_select = $("#sched_req_select").val();
		$.confirm({
			title: "Request Change Schedule?",
			content: 'Are you sure?',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: "btn-green",
					text: "Yes",
					action: function(){
						HoldOn.open(holdon_option);
						$.ajax({
								url: "{{route('req_sched_add')}}",
							data: {
								_token : "{{csrf_token()}}", 
								sched_target_date: sched_target_date,
								sched_req_select: sched_req_select
							},
								success: function (data) { 
									$.notify(data, {type:"info",icon:"info"}); 
									sched_req_tbl();
									HoldOn.close();
								},
								dataType: 'json',
								method: 'POST'
							});
					}
					
				},
				cancelAction: {
					btnClass: 'btn-gray',
					text: 'Cancel',
					action: function(){
					
					}  
				}
			}
		});
 
	});
	$("#sched_req_employee").on("change", function(){
		sched_req_tbl();
	});
	sched_req_tbl();
	function sched_req_tbl(){
		var sched_req_employee = $("#sched_req_employee").val();
		var sched_req_date_range = $("#sched_req_date_range").val();
		
		$('#sched_req_tbl').DataTable({
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
				"url": "{{ route('sched_req_tbl') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}",
					sched_req_employee:sched_req_employee,
					sched_range_date:sched_req_date_range
				}
			},
			"columns":[
				{'data': 'name'},
				{'data': 'target_date'},
				{'data': 'schedule'},
				{'data': 'status'},
				{'data': 'action', 'orderable': false, 'searchable': false},
			]
		});
	}
	function ot_table(){
	   $('#ot_table_tbl').DataTable({
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
				"url": "{{ route('ot_table_tbl') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}"  
					
				}
			},
			"columns":[
				{'data': 'code'},
				{'data': 'name'},
				{'data': 'rate'},
				{'data': 'action', 'orderable': false, 'searchable': false},
			]
		});
	}
	$('#timeModal').on('show.bs.modal', function (e) {
   		var emp_id = $(e.relatedTarget).data('emp_id');
		var name = $(e.relatedTarget).data('name');
		var amTimeIn =  $(e.relatedTarget).data('am_in');
		var	amTimeOut =  $(e.relatedTarget).data('am_out');
		var	pmTimeIn =  $(e.relatedTarget).data('pm_in');
		var pmTimeOut =  $(e.relatedTarget).data('pm_out');
		var date_target = $(e.relatedTarget).data('date_target');
		$('#emp_select').select2({ dropdownParent: $('#timeModal') });
		if(name){
			$("#emp_div").show("fast");
			$("#emp_list_div").hide("fast");
		}else{
			emp_id = $("#emp_select").val();
			$("#emp_div").hide("fast");
			$("#emp_list_div").show("fast");
			$("#emp_id").val(emp_id);
		}
		$("#name").val(name);
		$("#emp_id").val(emp_id);
		$("#amTimeIn").val(amTimeIn);
		$("#amTimeOut").val(amTimeOut);
		$("#pmTimeIn").val(pmTimeIn);
		$("#pmTimeOut").val(pmTimeOut);
		$("#target_date").val(date_target);
	});
	$('#requestLogModal').on('show.bs.modal', function (e) {
    var timecard_id = $(e.relatedTarget).data('timecard_id');
	var amTimeIn =  $(e.relatedTarget).data('am_in');
	var	amTimeOut =  $(e.relatedTarget).data('am_out');
	var	pmTimeIn =  $(e.relatedTarget).data('pm_in');
	var pmTimeOut =  $(e.relatedTarget).data('pm_out');
    var date_target = $(e.relatedTarget).data('date_target');
	$("#timecard_id").val(timecard_id);
	$("#amTimeIn_req").val(amTimeIn);
	$("#amTimeOut_req").val(amTimeOut);
	$("#pmTimeIn_req").val(pmTimeIn);
	$("#pmTimeOut_req").val(pmTimeOut);
	$("#target_date_req").val(date_target);
	});
	
	$('#ot_table_modal').on('show.bs.modal', function(e) {
		var id = $(e.relatedTarget).data('id');
		var code = $(e.relatedTarget).data('code');
		var name = $(e.relatedTarget).data('name');
		var rate = $(e.relatedTarget).data('rate');
	
			$("#ot_tbl_update_btn").val(id);
			$("#tbl_code").val(code);
			$("#tbl_name").val(name);
			$("#tbl_rate").val(rate);
	});
	$("#ot_tbl_update_btn").on("click", function(){
		var id = $("#ot_tbl_update_btn").val();
		var rate = $("#tbl_rate").val();
		$.confirm({
						title: 'Over Time',
						content: 'Update Over Time',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Update",
								action: function(){
									HoldOn.open(holdon_option);
									$.ajax({
											url: "{{route('update_ot_rate')}}",
										data: {
											_token : "{{csrf_token()}}", 
											id: id,
											rate: rate
											
										},
											success: function (data) { 
												$.notify(data, {type:"info",icon:"info"}); 
												$('#ot_table_modal').modal("hide");
												ot_table();
												HoldOn.close();
											},
											dataType: 'json',
											method: 'POST'
										});
								}
								
							},
							cancelAction: {
								btnClass: 'btn-gray',
								text: 'Cancel',
								action: function(){
								
								}  
							}
						}
					});
	});
	// add delete in tk
	function delete_manual_log(id){
		$.confirm({
			title: 'Delete',
			content: 'Delete Logs?',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: 'btn-danger',
					text: "Yes",
					action: function(){
						HoldOn.open(holdon_option);
						$.ajax({
								url: "{{route('delete_manual_log')}}",
							data: {
								_token : "{{csrf_token()}}", 
								id: id,
							},
								success: function (data) { 
									$.notify(data, {type:"info",icon:"info"}); 
									load_processed_tbl_for_delete();
									HoldOn.close();
									
								
									
								},
								dataType: 'json',
								method: 'POST'
							});
					}
					
				},
				cancelAction: {
					btnClass: 'btn-gray',
					text: 'Cancel',
					action: function(){
					
					}  
				}
			}
		});
	} 
	
	//reload raw logs tbl
	function load_processed_tbl_for_delete() {
			var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
			var emp_id = "";
			if(user_type == "employee"){
				emp_id = "{{Auth::user()->company['linked_employee']['id']}}";
			}else{
				emp_id = $("#manual_time_keeping_employee").val();
			}
			var branch_id = $("#manual_time_keeping_branch").val();
			$("#manual_logs_tbl").DataTable({
				"bDestroy": true,
				"autoWidth": false,
				"searchHighlight": true,
				"searching": true,
				"processing": true,
				"serverSide": true,
				"orderMulti": true,
				"order": [[1, "asc"], [2, "asc"]],
				"pageLength": 10,
				"ajax": {
					"url": "{{ route('timecard_logs_tbl') }}",
					"dataType": "json",
					"type": "POST",
					"data": function (d) {
						d._token = "{{ csrf_token() }}";
						d.page = "{{Route::current()->action['as']}}";
						d.branch_id = branch_id; //date range admin
						d.emp_id = emp_id;
						d.date_range = $('#manual_date_range').val();
						let selectedDates = $('#selected_dates').val();
						if (selectedDates && !Array.isArray(selectedDates)) {
							selectedDates = [selectedDates]; 
						}
						d.selected_dates = selectedDates;
					}
				},
				"columns": [
					{ 'data': 'name' },
					{ 'data': 'position' },
					{ 'data': 'target_date' },
					{ 'data': 'AM_IN' },
					{ 'data': 'PM_OUT' },
					{ 'data': 'schedule' },
					{ 'data': 'action' },
					
				]
			});
		}
	function req_sched_action(id, status){
		
			var titles = "Process Request";
			var btnData = "btn-info";
		
		if(status === 3){
			 titles = "Cancel Request?";
			 btnData = "btn-danger";
		}
		if(status === 2){
			 titles = "Decline Request?";
			 btnData = "btn-danger";
		}
		if(status === 1){
			 title = "Approve Request?";
			 btnData = "btn-success";
		}
		$.confirm({
			title: titles,
			content: 'Are you sure?',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: btnData,
					text: "Yes",
					action: function(){
						HoldOn.open(holdon_option);
						$.ajax({
								url: "{{route('req_sched_action')}}",
							data: {
								_token : "{{csrf_token()}}", 
								id: id,
								status: status
							},
								success: function (data) { 
									$.notify(data, {type:"info",icon:"info"}); 
									sched_req_tbl();
									HoldOn.close();
								},
								dataType: 'json',
								method: 'POST'
							});
					}
					
				},
				cancelAction: {
					btnClass: 'btn-gray',
					text: 'Cancel',
					action: function(){
					
					}  
				}
			}
		});
	}
	function delete_ot(id,type){
		$.confirm({
			title: 'Delete',
			content: 'Are you sure?',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: 'btn-danger',
					text: "Yes",
					action: function(){
						HoldOn.open(holdon_option);
						$.ajax({
								url: "{{route('delete_ot')}}",
							data: {
								_token : "{{csrf_token()}}", 
								id: id,
								type: type
							},
								success: function (data) { 
									
									$.notify(data, {type:"info",icon:"info"}); 
									if(type == "ot_request"){
										applied_ot_tbl();
									}else{
										ot_table();
									}
									HoldOn.close();
									
								},
								dataType: 'json',
								method: 'POST'
							});
					}
					
				},
				cancelAction: {
					btnClass: 'btn-gray',
					text: 'Cancel',
					action: function(){
					
					}  
				}
			}
		});
	}
</script>
@stop