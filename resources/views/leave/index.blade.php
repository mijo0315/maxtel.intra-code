@extends('layouts.front-app')

@section('title')

{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Leave Management

@stop

@section("styles")

<style>

	th{

		text-align: center;

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

                                   

                                    <li id="mnu_leave_file_li"><a class="active mnu_btn" id="mnu_leave_file"  onclick="">Leave Filling</a></li>

                                    <li id="mnu_credit_table_li"><a class="mnu_btn" id="mnu_credit_table"  onclick="">Leave Credits</a></li>

                                    <li id="mnu_leave_table_li"><a class="mnu_btn" id="mnu_leave_table"  onclick="">Leave Type</a></li>

									<li id="mnu_leave_calendar_li"><a class="mnu_btn" id="mnu_leave_calendar"  onclick='view_leave_calendar("{{date("Y-m-01")}}")'>Leave Calendar</a></li>

								</ul>

							</div>

						</div>

						@include("leave.file_leave")

						@include("leave.leave_type")

						@include("leave.leave_credit")

						@include("leave.leave_calendar")

                    </div>

            </div>

</div>

@endif

@stop

@section("scripts")

<script>

	  $( document ).ready(function() {

		$( "#file_from" ).datepicker({ dateFormat: 'yy-mm-dd' });

       $( "#file_to" ).datepicker({dateFormat: 'yy-mm-dd' });

	   $( "#rejoin_duty" ).datepicker({dateFormat: 'yy-mm-dd' }); 

			$("#mnu_leave_file").click();

            

            $(".form-select").select2();

			leave_type_tbl();

			leave_credit_tbl();

			file_leave_tbl();

			var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";

			var role_id = "{{ Auth::user()->role_id }}";

			if(user_type == "employee"){

				$("#mnu_leave_table_li").hide("fast");

				$("#mnu_leave_calendar_li").hide("fast");

				$("#add_leave_credit").hide("fast");

				$("#leave_status").empty();

				$("#leave_status").append('<option value="FILED">Filed</option>');

				// All approvers dito

				var approverIds = ["6", "7", "8", "9", "10", "11", "12","13"];

				// 1st approvers dito

				var firstApproverIds = ["6", "7", "8", "9"];

				var finalApproverIds = ["10", "11", "12","13"];

				if (approverIds.includes(role_id)) {

					if (firstApproverIds.includes(role_id)) {

						//first approver

						$("#leave_status").append('<option value="1st_Approved">Approved</option>');

					} else if (finalApproverIds.includes(role_id)) {

						//final approver

						$("#leave_status").append('<option value="APPROVED">Approved</option>');

					}

					$("#leave_status").append('<option value="REJECTED">Rejected</option>');

				}

				

			}else{

				$("#mnu_leave_table_li").show("fast");

				$("#mnu_leave_calendar_li").show("fast");

				$("#add_leave_credit").show("fast");

				$("#leave_status").empty();

				$("#leave_status").append('<option value="FILED">Filed</option>');

				$("#leave_status").append('<option value="APPROVED">Approved</option>');

				$("#leave_status").append('<option value="REJECTED">Rejected</option>');

				if(role_id == "14" || role_id == "15"){
					$("#mnu_leave_table_li").hide("fast");
					$("#mnu_leave_calendar_li").hide("fast");
					$("#add_leave_credit").hide("fast");
				}

				

			}

			

		});

		$(".mnu_btn").on("click", function(){

            $(".mnu_btn").removeAttr("class");

            $(".stat_tab").hide("fast");

            var mnu_data = $(this).attr("id");

            $(this).attr("class", "active mnu_btn");

            mnu_data = mnu_data.replace("mnu_","");

            $("#"+mnu_data+"_tab").show("fast");

        });

</script>

{{-- LEAVE TYPE FORM --}}

<script>

		function leave_type_tbl(){

           $('#leave_type_tbl').DataTable({

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

                    "url": "{{ route('leave_type_tbl') }}",

                    "dataType": "json",

                    "type": "POST",

                    "data":{

                        "_token": "{{ csrf_token() }}", 

                        "page": "{{Route::current()->action['as']}}"  

                        

                    }

                },

                "columns":[

                    {'data': 'leave_type'},

                    {'data': 'leave_name'},

                    {'data': 'is_with_credits'},

                    {'data': 'action', 'orderable': false, 'searchable': false},

                ]

            });

        }

	$('#leave_table_modal').on('show.bs.modal', function(e) {

            var id = $(e.relatedTarget).data('id');

            var type = $(e.relatedTarget).data('type');

            var name = $(e.relatedTarget).data('name');

            var require = $(e.relatedTarget).data('require');

        

                $("#table_update_btn").val(id);

                $("#table_leave_type").val(type).change();

                $("#table_leave_name").val(name);

                $("#is_require").val(require).change();

                

        });

	$("#table_update_btn").on("click", function(){

		var id = $("#table_update_btn").val();

			var leave_type = $("#table_leave_type").val();

			var leave_name = $("#table_leave_name").val();

			var require = $("#is_require").val();

				if(leave_type == "0"){

					$.notify("Please Select Leave Type", {type:"danger",icon:"danger"}); 

					return ;

				}

				if(leave_name == ""){

					$.notify("Leave Name Required", {type:"danger",icon:"danger"}); 

					return ;

				}

			if(id == "new"){

				var text = "Add";

			}else{

				var text = "Update";

			}

			$.confirm({

							title: 'Leave',

							content: text+' Leave Type',

							escapeKey: 'cancelAction',

							buttons: {

								confirm: {

									btnClass: 'btn-green',

									text: text,

									action: function(){

										HoldOn.open(holdon_option);

										$.ajax({

												url: "{{route('store_leave_type')}}",

											data: {

												_token : "{{csrf_token()}}", 

												id: id,

                                                leave_type: leave_type,

												leave_name: leave_name,

												require: require

											},

												success: function (data) { 

													$.notify(data, {type:"info",icon:"info"}); 

													$('#leave_table_modal').modal("hide");

													leave_type_tbl();

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

		

	

</script>

{{-- LEAVE CREDITS FORM --}}

<script>

	function leave_credit_tbl(){

		var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";

	   $('#credit_type_tbl').DataTable({

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

				"url": "{{ route('leave_credit_tbl') }}",

				"dataType": "json",

				"type": "POST",

				"data":{

					"_token": "{{ csrf_token() }}", 

					"page": "{{Route::current()->action['as']}}"  

					

				}

			},

			"columns":[

				{'data': 'emp_name'},

				{'data': 'leave_type'},

				{'data': 'leave_name'},

				{'data': 'credit', 'className':"text-center"},

				{'data': 'balance', 'className':"text-center"},

				// {'data': 'action', 'orderable': false, 'searchable': false},

				{

					'data': 'action',

					'orderable': false,

					'searchable': false,

					'render': function(data, type, row) {

						// Hide "Action" column if user is employee

						if (user_type === 'employee') {

							return '';  

						}

						return data; 

					}

				}

			],

			// Hide "Action" column if user is employee

			"columnDefs": [

				{

					"targets": [5], 

					"visible": user_type !== 'employee' 

				}

			]

		});

	}

	$('#leave_credit_modal').on('show.bs.modal', function(e) {

		var id = $(e.relatedTarget).data('id');

		var emp_id = $(e.relatedTarget).data('emp_id');

		var leave_id = $(e.relatedTarget).data('leave_id');

		var leave_count = $(e.relatedTarget).data('leave_count');

	

			$("#credit_update_btn").val(id);

			$("#leave_type").val(leave_id).change();

			$("#leave_credit").val(leave_count);

			$("#emp_name").val(emp_id).change();

		

	});

	$("#credit_update_btn").on("click", function(){

	var credit_update_btn =$("#credit_update_btn").val();

	var leave_type =$("#leave_type").val();

	var leave_credit =$("#leave_credit").val();

	var emp_id = $("#emp_name").val();

	

			if(leave_type == "0"){

				$.notify("Please Select Leave Type", {type:"danger",icon:"danger"}); 

				return ;

			}

			if(emp_id == ""){

				$.notify("Please Select Employee", {type:"danger",icon:"danger"}); 

				return ;

			}

			if(leave_credit <= 0){

				$.notify("Leave Credit Must be higher than 0", {type:"danger",icon:"danger"}); 

				return ;

			}

		if(credit_update_btn == "new"){

			var text = "Add";

		}else{

			var text = "Update";

		}

		$.confirm({

						title: 'Leave',

						content: text+' Leave Type',

						escapeKey: 'cancelAction',

						buttons: {

							confirm: {

								btnClass: 'btn-green',

								text: text,

								action: function(){

									HoldOn.open(holdon_option);

									$.ajax({

											url: "{{route('store_leave_credit')}}",

										data: {

											_token : "{{csrf_token()}}", 

											id: credit_update_btn,

											leave_type: leave_type,

											leave_credit: leave_credit,

											emp_id: emp_id

										},

											success: function (data) { 

												$.notify(data, {type:"info",icon:"info"}); 

												$('#leave_credit_modal').modal("hide");

												leave_credit_tbl();

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

</script>

{{-- LEAVE FILLING --}}

<script>

	function file_leave_tbl(){

	   $('#leave_file_tbl').DataTable({

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

				"url": "{{ route('file_leave_tbl') }}",

				"dataType": "json",

				"type": "POST",

				"data":{

					"_token": "{{ csrf_token() }}", 

					"page": "{{Route::current()->action['as']}}"  

					

				}

			},

			"columns":[

				{'data': 'emp_name'},

				{'data': 'date_filed'},

				

				{'data': 'leave_type'},

				{'data': 'dates'},

				{'data': 'rejoin_duty_on'}, 

				{'data': 'is_half_day'}, 

				{'data': 'leave_count', 'className':"text-center"},

				{'data': 'leave_status', 'className':"text-center"},

				

				{'data': 'action', 'orderable': false, 'searchable': false},

			]

		});

	}

	$('#leave_file_modal').on('show.bs.modal', function(e) {

		$('#file_emp_name').select2({ dropdownParent: $('#leave_file_modal') });

		var id = $(e.relatedTarget).data('id');

		var emp_id = $(e.relatedTarget).data('emp_id');

		var leave_id = $(e.relatedTarget).data('leave_id');

		var leave_from = $(e.relatedTarget).data('leave_from');

		var leave_to = $(e.relatedTarget).data('leave_to');

		var rejoin_duty = $(e.relatedTarget).data('rejoin_duty'); 

		var reason = $(e.relatedTarget).data('reason');

		var leave_status = $(e.relatedTarget).data('leave_status');

		var half_day = $(e.relatedTarget).data('half_day');

		

			$("#file_update_btn").val(id);

			

			$("#file_from").val(leave_from);

			$("#file_to").val(leave_to);

			$("#rejoin_duty").val(rejoin_duty);

			$("#leave_status").val(leave_status).change();

			$("#half_day").val(half_day).change();

			

			$("#file_reason").val(reason);

	

			$.ajax({

				url: "{{route('leave_employee_list')}}",

			data: {

				_token : "{{csrf_token()}}", 

				page: "{{Route::current()->action['as']}}"

			},

				success: function (data) { 

					$("#file_emp_name").empty().append("<option value='0'>Select Employee </option> ");

					$.each(data, function( index, value ) {

                            if(value.ext_name == null){

                                value.ext_name = "";

                            }

                            $("#file_emp_name").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")

                            

                            });

							$("#file_emp_name").val(emp_id).change();

							$("#file_leave_type").val(leave_id).change();

				},

				dataType: 'json',

				method: 'POST'

			});

			

	});

	$("#file_leave_type").on("change", function(){

		var file_emp_name =  $("#file_emp_name").val();

		var file_leave_type =  $("#file_leave_type").val();

		if(file_emp_name != "0" || file_leave_type != "0"){

			$.ajax({

				url: "{{route('get_leave_balance')}}",

			data: {

				_token : "{{csrf_token()}}", 

				file_leave_type: file_leave_type,

				file_emp_name: file_emp_name,

			},

				success: function (data) { 

					$("#leave_balance").val(data);

				},

				dataType: 'json',

				method: 'POST'

			});

		}

	

	});





$("#file_emp_name").on("change", function(){

		var file_emp_name =  $("#file_emp_name").val();

		var file_leave_type =  $("#file_leave_type").val();

		if(file_emp_name != "0" || file_leave_type != "0"){

			$.ajax({

				url: "{{route('get_leave_balance')}}",

			data: {

				_token : "{{csrf_token()}}", 

				file_leave_type: file_leave_type,

				file_emp_name: file_emp_name,

			},

				success: function (data) { 

					$("#leave_balance").val(data);

				},

				dataType: 'json',

				method: 'POST'

			});

		}

	

	});



	$("#file_update_btn").on("click", function(){

		var role_id = "{{Auth::user()->role_id}}";

		var branch_id = "{{Auth::user()->company['linked_employee']['branch_id']}}";

		var file_update_btn =  $("#file_update_btn").val();

		var file_leave_type =  $("#file_leave_type").val();

		var file_from =  $("#file_from").val();

		var file_to =  $("#file_to").val();

		var file_reason =  $("#file_reason").val();

		var file_emp_name =  $("#file_emp_name").val();

		var leave_balance =  $("#leave_balance").val();

		var leave_status = $("#leave_status").val();

		var rejoin_duty =  $("#rejoin_duty").val();

		var half_day =  $("#half_day").val();

		var approverIds = ["11", "12"];//managers dorcas at ajes

		if (approverIds.includes(role_id)) { //emp id 102, 21

			if(file_emp_name === 21 || file_emp_name === 102){

				leave_status = "1st_Approved";

			}

		}

		if(role_id == "2" && (branch_id == "75" || branch_id == "76" || branch_id == "82")){ //for employees final app only dorcas at ajes

			leave_status = "1st_Approved";

		}



		var new_leave_status = 'Submit';



		if(leave_status == 'FILED'){

			new_leave_status = 'File';

		}

		if(leave_status == 'APPROVED'){

			new_leave_status = 'Aprrove';

		}

		if(leave_status == 'REJECTED'){

			new_leave_status = 'Reject';

		}

		

		if(leave_balance != "not required"){

			if(parseInt(leave_balance) <= 0){

				$.notify("Leave Balance Not Enough", {type:"danger",icon:"info"}); 

				return;

			}

		}

		if(file_emp_name == "0"){

			$.notify("Please select an employee", {type:"danger",icon:"info"}); 

			return;

		}

		if(file_leave_type == "0"){

			$.notify("Please select Leave", {type:"danger",icon:"info"}); 

			return;

		}

		if(file_reason == ""){

			$.notify("Please state the reason", {type:"danger",icon:"info"}); 

			return;

		}

		var file_from_date = new Date(file_from);

		var file_to_date = new Date(file_to);

		if(file_to_date < file_from_date){

			$.notify("File To Date Must be higher than From Date", {type:"danger",icon:"info"}); 

			return;

		}

		$.confirm({

						title: 'Leave',

						content: new_leave_status+' Leave ',

						escapeKey: 'cancelAction',

						buttons: {

							confirm: {

								btnClass: 'btn-green',

								text: new_leave_status,

								action: function(){

									HoldOn.open(holdon_option);

									$.ajax({

											url: "{{route('store_filed_leave')}}",

										data: {

											_token : "{{csrf_token()}}", 

											id: file_update_btn,

											file_leave_type: file_leave_type,

											file_from: file_from,

											file_to: file_to,

											rejoin_duty: rejoin_duty,

											file_reason: file_reason,

											file_emp_name: file_emp_name,

											leave_status: leave_status,

											half_day: half_day,

										},

											success: function (data) { 

												if(data == "Filling Success"){

													$.notify(data, {type:"info",icon:"info"}); 

													 $('#leave_file_modal').modal("hide");

													 file_leave_tbl();

												}else{

													$.notify(data, {type:"danger",icon:"info"}); 

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

	});

	

	function delete_file_leave(id){

		$.confirm({

						title: 'Leave',

						content: 'Delete File Leave ',

						escapeKey: 'cancelAction',

						buttons: {

							confirm: {

								btnClass: 'btn-green',

								text: "File",

								action: function(){

									HoldOn.open(holdon_option);

									$.ajax({

											url: "{{route('delete_filed_leave')}}",

										data: {

											_token : "{{csrf_token()}}", 

											id: id,

										},

											success: function (data) { 

												

												$.notify(data, {type:"info",icon:"info"}); 

												file_leave_tbl();

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

	// add delete in leave

	function delete_leave(id,type){

		$.confirm({

			title: 'Leave',

			content: 'Delete Leave '+type,

			escapeKey: 'cancelAction',

			buttons: {

				confirm: {

					btnClass: 'btn-danger',

					text: "Yes",

					action: function(){

						HoldOn.open(holdon_option);

						$.ajax({

								url: "{{route('delete_leave')}}",

							data: {

								_token : "{{csrf_token()}}", 

								id: id,

								type: type

							},

								success: function (data) { 

									

									$.notify(data, {type:"info",icon:"info"}); 

									if(type == "credit"){

										leave_credit_tbl();

									}else{

										leave_type_tbl();

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

	// Add Calendar for Leave 

	view_leave_calendar('{{date("Y-m-01")}}');

	function view_leave_calendar(month_view){

		// month_view = '2024-01-01';

		var SITE_URL = "{{url('/')}}";

		var calendarEl_holiday = document.getElementById('leave_calendar');

			var calendar_holiday = new FullCalendar.Calendar(calendarEl_holiday, {

			initialDate: month_view,

			editable: false,

			selectable: false,

			businessHours: true,

			dayMaxEvents: true, // allow "more" link when too many events

			events: function(info, successCallback, failureCallback) {

				var start = info.start;

				$.ajax({

					url:  SITE_URL+"/get_leaves/"+start.toISOString()+"/"+"{{Route::current()->action['as']}}",

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

				}

				},

				eventClick: function(info) {

					var event = info.event;

					var day = event.start;

					var is_edit = event.extendedProps.is_edit;

					var type = event.extendedProps.type;

					var name = event.extendedProps.name;

					

				}

			

			

			});

			calendar_holiday.render();

	}

</script>

@stop