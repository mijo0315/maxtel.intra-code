@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Setting
@stop

@section("styles")
<!-- Include Flatpickr CSS add timepicker-->
<link rel="stylesheet" href="{{ asset_with_env('css/flatpickr.min.css')}}">
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
									<li><a class="active" id="tab_sched"  onclick="$('#sched_list').show('slow'); $('#sched_library').hide('slow');  $('#tab_lib').removeAttr('class'); $('#tab_sched').attr('class','active');  ">Schedule List</a></li>
									<li><a id="tab_lib" onclick="$('#sched_library').show('slow'); $('#sched_list').hide('slow'); $('#tab_sched').removeAttr('class'); $('#tab_lib').attr('class','active');">Schedule Library</a></li>

								</ul>
							</div>
						</div>

                        <div class="col-xl-12 col-sm-12 col-12" id="sched_library">
							<div class="col-xl-12 col-sm-12 col-12 ">
								<div class="card oth_income_card oth_library" >
									<div class="card-header" style="background-color: #2f47ba;">
										<h2 class="card-titles" style="color: white;">Schedule Management <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
									</div>



							<div class="row">
								<div class="col-xl-12 col-sm-12 col-12 ">
									
									@if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
									<a class="btn btn-apply btn-md m-3" data-target="#library_add_edit" data-toggle="modal" data-sched_id = "new" >  Add Schedule Library</a>
									@endif



									
								</div>
							</div>


							<div class="row">
								<div class="col-xl-12 col-sm-12 col-12 ">
									
								
										<div class="card-body">
											<div class="row table-responsive">
												<div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
													<table class="table table-striped table-bordered table-hover" id="tbl_sched_library">
														<thead>
															<tr>
																<th rowspan="2">Code</th>
																<th rowspan="2">Name</th>
																<th rowspan="1" colspan="6">Time Schedule</th>
																<th rowspan="2">Grace <br> Period </th>

																<th rowspan="2">Status</th>
																<th rowspan="2">Action</th>
															</tr>
															<tr>
																<th>AM IN</th>
																<th>AM OUT</th>
																<th>PM IN</th>
																<th>PM OUT</th>
																<th>OT IN</th>
																<th>OT OUT</th>
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










						<div class="col-xl-12 col-sm-12 col-12" id="sched_list">

							<div class="col-xl-12 col-sm-12 col-12 ">
								<div class="card oth_income_card oth_library" >
									<div class="card-header" style="background-color: #2f47ba;">
										<h2 class="card-titles" style="color: white;">Schedule Management <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
									</div>


							<div class="row">
								<div class="col-xl-12 col-sm-12 col-12 ">
									@if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
									<a class="btn btn-apply btn-md m-3" data-target="#sched_add_edit" data-toggle="modal" data-sched_id = "new" >  Add Schedule</a>
									@endif
								</div>
							</div>


							<div class="row">
								<div class="col-xl-12 col-sm-12 col-12 ">
								
										<div class="card-body">
											<div class="row table-responsive">
												<div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">
													<table class="table table-striped table-bordered table-hover" id="tbl_sched_list">
														<thead>
															<tr>
																<th rowspan="2">Code</th>
																<th rowspan="2">Name</th>
																<th rowspan="1" colspan="7">Schedule List</th>
																<th rowspan="2">Status</th>
																<th rowspan="2">Action</th>
															</tr>
															<tr>
																<th>Mon</th>
																<th>Tue</th>
																<th>Wed</th>
																<th>Thu</th>
																<th>Fri</th>
																<th>Sat</th>
																<th>Sun</th>
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
				</div>
</div>
@endif


{{-- MODAL FOR LIBRARY --}}
<div class="modal fade" tabindex="-1" role="dialog" id="library_add_edit">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Daily Time Schedule</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label for="code_lib">Code:</label>
				</div>
				<div class="col-md-8">
					<input type="text" class="form-control" placeholder="Schedule Code" id="code_lib">
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label for="name_lib">Name:</label>
				</div>
				<div class="col-md-8">
					<input type="text" class="form-control" placeholder="Schedule Name" id="name_lib">
				</div>
			</div>


			<div class="row mt-2">
				<div class="col-md-4">
					<label for="am_in_edit">SCHEDULE TYPE</label>
				</div>
				<div class="col-md-8">
					<input type="radio"  id="regular" name="sched_type" value="regular_schedule" checked> 
						<label for="regular">Regular</label>
					<input type="radio"  id="flexi" name="sched_type" value="flexi_schedule"> 
						<label for="flexi">Flexible</label>
				</div>
			</div>


			<div id="flexi_schedule">
				<div class="row">
					<div class="col-md-4">
						<label for="required_hours">REQUIRED HOURS:</label>
					</div>
					<div class="col-md-8">
						<input type="number" class="form-control" id="required_hours" placeholder="Required Hours">
					</div>
				</div>
			</div>
			<!-- add timepicker -->
			<div id="regular_schedule">
				<div class="row">
					<div class="col-md-4">
						<label for="am_in_edit">AM IN:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="hh:mm:ss" id="am_in_edit">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<label for="am_out_edit">AM OUT:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="hh:mm:ss" id="am_out_edit">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<label for="pm_in_edit">PM IN:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="hh:mm:ss" id="pm_in_edit">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<label for="pm_out_edit">PM OUT:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="hh:mm:ss" id="pm_out_edit">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<label for="ot_in_edit">OT IN:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="hh:mm:ss" id="ot_in_edit">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<label for="ot_out_edit">OT OUT:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="hh:mm:ss" id="ot_out_edit">
					</div>
				</div>
	
				<div class="row">
					<div class="col-md-4">
						<label for="grace_period_edit">Grace Period:</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" placeholder="Grace Period" id="grace_period_edit">
					</div>
				</div>


			</div>

			
		






		</div>
		<div class="modal-footer">
			
		  <button type="button" id="add_edit_post_lib" value=""  class="btn btn-success">Update</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>


{{-- MODAL FOR SCHEDULE INFO --}}
<div class="modal fade" tabindex="-1" role="dialog" id="sched_info">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Modal title</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-6">
					<label for="am_in"> <strong> Morning IN: </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="am_in"></span>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6">
					<label for="am_out"> <strong>  Morning OUT: </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="am_out"></span>
				
				</div>		
			</div>
			

			<div class="row">
				<div class="col-md-6">
					<label for="pm_in"> <strong>  Afternoon IN: </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="pm_in"></span>
				</div>		
			</div>

			<div class="row">
				<div class="col-md-6">
					<label for="pm_out"> <strong>Afternoon OUT: </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="pm_out"></span>
				
				</div>		
			</div>

			<div class="row">
				<div class="col-md-6">
					<label for="ot_in"> <strong>  Overtime IN: </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="ot_in"></span>
				
				</div>		
			</div>

			<div class="row">
				<div class="col-md-6">
					<label for="ot_out"> <strong> Overtime OUT: </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="ot_out"></span>
				
				</div>		
			</div>

			<div class="row">
				<div class="col-md-6">
					<label for="grace_period"> <strong> Grace Period </strong> </label>
				</div>
				<div class="col-md-6">
					<span id="grace_period"></span>
				</div>		
			</div>




		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>




{{-- MODAL FOR SCHEDULE ADD /EDIT --}}
<div class="modal fade" tabindex="-1" role="dialog" id="sched_add_edit">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Schedule</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label for="code">Code:</label>
				</div>
				<div class="col-md-8">
					<input type="text" class="form-control" placeholder="Schedule Code" id="code">
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label for="name">Name:</label>
				</div>
				<div class="col-md-8">
					<input type="text" class="form-control" placeholder="Schedule Name" id="name">
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label for="monday_edit">Monday:</label>
				</div>
				<div class="col-md-8">
					<select id="monday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label for="tuesday_edit">Tuesday:</label>
				</div>
				<div class="col-md-8">
					<select id="tuesday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="wednesday_edit">Wednesday:</label>
				</div>
				<div class="col-md-8">
					<select id="wednesday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="thursday_edit">Thursday:</label>
				</div>
				<div class="col-md-8">
					<select id="thursday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="friday_edit">Friday:</label>
				</div>
				<div class="col-md-8">
					<select id="friday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="saturday_edit">Saturday:</label>
				</div>
				<div class="col-md-8">
					<select id="saturday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="sunday_edit">Sunday:</label>
				</div>
				<div class="col-md-8">
					<select id="sunday_edit" class="form-control form-select">
						<option value="0">Rest Day</option>
						@foreach($schedule_library as $library)<option value="{{$library->id}}">{{$library->name}} ( {{$library->code}} )</option>@endforeach
					</select>
				</div>
			</div>
		


		</div>
		<div class="modal-footer">
			
		  <button type="button" id="add_edit_post" value=""  class="btn btn-success">Update</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>





  @stop

@section("scripts")
	<!-- Include Flatpickr JS add timepicker-->
	<script src="{{ asset_with_env('js/flatpickr.js')}}"></script>
    <script>

			$( document ).ready(function() {
				// add timepicker
				flatpickr("#am_in_edit,#am_out_edit,#pm_in_edit,#pm_out_edit,#ot_in_edit,#ot_out_edit", {
					enableTime: true,
					noCalendar: true,
					dateFormat: "H:i:S",  // Specify the format you need (hh:mm:ss)
					time_24hr: true,
				});
				$("#sched_list").show('slow');
				$("#sched_library").hide('slow');   

				$("#regular_schedule").show("fast");
				$("#flexi_schedule").hide("fast");
				
				load_schedule_list();
				load_schedule_library();
			});

			$("input[type=radio][name=sched_type]").click(function() { 
				var div_show = $(this).val(); 
				$("#regular_schedule").hide("fast");
				$("#flexi_schedule").hide("fast");
				$("#"+div_show).show("fast");


			}); 
			

			function load_schedule_library(){
				$('#tbl_sched_library').DataTable({
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
                    "url": "{{ route('sched_library') }}",
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
					
					{'data': 'am_in'},
					{'data': 'am_out'},
					{'data': 'pm_in'},
					{'data': 'pm_out'},
					{'data': 'ot_in'},
					{'data': 'ot_out'},
					{'data': 'grace_period'},
					{'data': 'is_active'},
					{'data': 'action', 'orderable': false, 'searchable': false},

                ]
            });

			}

			function set_status_lib(id){

				$.confirm({
							title: 'Change Status',
							content: 'Update Status?',
							escapeKey: 'cancelAction',
							buttons: {
								confirm: {
									btnClass: 'btn-green',
									text: 'Update',
									action: function(){
										HoldOn.open(holdon_option);

										$.ajax({
												url: "{{route('update_status_lib')}}",
											data: {
												_token : "{{csrf_token()}}", 
												id: id
											},
												success: function (data) { 
													if(data){
														$.notify("Success", {type:"info",icon:"info"}); 
													}else{
														$.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
													}
												
													load_schedule_library();
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


				
				$('#library_add_edit').on('show.bs.modal', function(e) {
				var sched_id = $(e.relatedTarget).data('sched_id');

				if(sched_id != 'new'){
					$.ajax({
						url: "{{route('get_sched_lib_info')}}",
					data: {
						_token : "{{csrf_token()}}", 
						id: sched_id
					},
						success: function (data) { 
							if(data != "No Schedule Information"){

								
								if(data.is_flexi == "1"){
									$("#regular_schedule").hide("fast");
									$("#flexi_schedule").show("fast");
								}else{
									$("#regular_schedule").show("fast");
									$("#flexi_schedule").hide("fast");
								}

								$(e.currentTarget).find('#required_hours').val(data.required_hours);
								$(e.currentTarget).find('#code_lib').val(data.code);
								$(e.currentTarget).find('#name_lib').val(data.name);
								$(e.currentTarget).find('#am_in_edit').val(data.am_in);
								$(e.currentTarget).find('#am_out_edit').val(data.am_out);
								$(e.currentTarget).find('#pm_in_edit').val(data.pm_in);
								$(e.currentTarget).find('#pm_out_edit').val(data.pm_out);
								$(e.currentTarget).find('#ot_in_edit').val(data.ot_in);
								$(e.currentTarget).find('#ot_out_edit').val(data.ot_out);
								$(e.currentTarget).find('#grace_period_edit').val(data.grace_period);

								$(e.currentTarget).find('#add_edit_post_lib').val(sched_id);
								$(e.currentTarget).find('#add_edit_post_lib').empty().text("Update");
								
								


							}else{
								$.notify(data, {type:"info",icon:"info"}); 
							}
							
							HoldOn.close();
						},
						dataType: 'json',
						method: 'POST'
					});



				} //EDIT
				else{



								$("#regular_schedule").show("fast");
								$("#flexi_schedule").hide("fast");

								
								$(e.currentTarget).find('#code_lib').val("");
								$(e.currentTarget).find('#name_lib').val("");
								$(e.currentTarget).find('#required_hours').val("");
								$(e.currentTarget).find('#grace_period_edit').val("");
								$(e.currentTarget).find('#am_in_edit').val("");
								$(e.currentTarget).find('#am_out_edit').val("");
								$(e.currentTarget).find('#pm_in_edit').val("");
								$(e.currentTarget).find('#pm_out_edit').val("");
								$(e.currentTarget).find('#ot_in_edit').val("");
								$(e.currentTarget).find('#ot_out_edit').val("");
							
								$(e.currentTarget).find('#add_edit_post_lib').val(sched_id);
								$(e.currentTarget).find('#add_edit_post_lib').empty().text("Add");

				}

			});


			
			$("#add_edit_post_lib").on("click", function(){
				var code = $("#code_lib").val();
				var name = $("#name_lib").val();

				var sched_type = 	$('input:radio[name=sched_type]').filter(":checked").val();
				

				
				regular_schedule
				if(sched_type == "flexi_schedule"){
					var required_hours = $("#required_hours").val();
					var grace_period = 0;
					var am_in_edit =   "00:00:00";
					var am_out_edit =  "00:00:00";
					var pm_in_edit =   "00:00:00";
					var pm_out_edit =  "00:00:00";
					var ot_in_edit =   "00:00:00";
					var ot_out_edit =  "00:00:00";

				var type = $(this).val();

				if(code == ""){ $.notify("Code Cannot Be Empty", {type:"info",icon:"info"}); return;	}
				if(name == ""){ $.notify("Name Cannot Be Empty", {type:"info",icon:"info"}); return;	}
				


		
				if(type != "new"){
					$.confirm({
                            title: 'Schedule Updating',
                            content: 'Update Schedule?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Update',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_schedule_library')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: type,
												code : code ,
												name : name ,
												grace_period : grace_period ,
												am_in : am_in_edit ,
												am_out : am_out_edit ,
												pm_in : pm_in_edit ,
												pm_out : pm_out_edit ,
												ot_in : ot_in_edit ,
												ot_out : ot_out_edit ,
												required_hours: required_hours,
												is_flexi: 0
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_library();
													$("#library_add_edit").modal("hide");
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
				}else{
					$.confirm({
                            title: 'Schedule Management',
                            content: 'Add New Schedule?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Add New',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_schedule_library')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: type,
												code : code ,
												name : name ,
												grace_period : grace_period ,
												am_in : am_in_edit ,
												am_out : am_out_edit ,
												pm_in : pm_in_edit ,
												pm_out : pm_out_edit ,
												ot_in : ot_in_edit ,
												ot_out : ot_out_edit ,
												required_hours: required_hours,
												is_flexi: 1
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_library();
													$("#library_add_edit").modal("hide");
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


				

				
				
				}else{

					// REGULAR SCHEDULE TYPE

					var required_hours = "";
					var grace_period = $("#grace_period_edit").val();
					var am_in_edit = $("#am_in_edit").val();
					var am_out_edit = $("#am_out_edit").val();
					var pm_in_edit = $("#pm_in_edit").val();
					var pm_out_edit = $("#pm_out_edit").val();
					var ot_in_edit = $("#ot_in_edit").val();
					var ot_out_edit = $("#ot_out_edit").val();

				var type = $(this).val();

				if(code == ""){ $.notify("Code Cannot Be Empty", {type:"info",icon:"info"}); return;	}
				if(name == ""){ $.notify("Name Cannot Be Empty", {type:"info",icon:"info"}); return;	}
				


		
				if(type != "new"){
					$.confirm({
                            title: 'Schedule Updating',
                            content: 'Update Schedule?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Update',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_schedule_library')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: type,
												code : code ,
												name : name ,
												grace_period : grace_period ,
												am_in : am_in_edit ,
												am_out : am_out_edit ,
												pm_in : pm_in_edit ,
												pm_out : pm_out_edit ,
												ot_in : ot_in_edit ,
												ot_out : ot_out_edit ,
												required_hours: required_hours,
												is_flexi: 0
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_library();
													$("#library_add_edit").modal("hide");
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
				}else{
					$.confirm({
                            title: 'Schedule Management',
                            content: 'Add New Schedule?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Add New',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_schedule_library')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: type,
												code : code ,
												name : name ,
												grace_period : grace_period ,
												am_in : am_in_edit ,
												am_out : am_out_edit ,
												pm_in : pm_in_edit ,
												pm_out : pm_out_edit ,
												ot_in : ot_in_edit ,
												ot_out : ot_out_edit ,
												required_hours: required_hours,
												is_flexi: 0
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_library();
													$("#library_add_edit").modal("hide");
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


				

				}


				



			});




			//SCHEDULE

			function load_schedule_list(){
				$('#tbl_sched_list').DataTable({
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
                    "url": "{{ route('sched_list') }}",
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
					{'data': 'monday'},
					{'data': 'tuesday'},
					{'data': 'wednesday'},
					{'data': 'thursday'},
					{'data': 'friday'},
					{'data': 'saturday'},
					{'data': 'sunday'},
					{'data': 'is_active'},
					{'data': 'action', 'orderable': false, 'searchable': false},

                ]
            });

			}


			function set_status(id){

				$.confirm({
                            title: 'Change Status',
                            content: 'Update Status?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Update',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_status')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: id
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify("Success", {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_list();
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

			$("#add_edit_post").on("click", function(){
				var code = $("#code").val();
				var name = $("#name").val();
				var monday_edit = $("#monday_edit").val();
				var tuesday_edit = $("#tuesday_edit").val();
				var wednesday_edit = $("#wednesday_edit").val();
				var thursday_edit = $("#thursday_edit").val();
				var friday_edit = $("#friday_edit").val();
				var saturday_edit = $("#saturday_edit").val();
				var sunday_edit = $("#sunday_edit").val();
				var type = $(this).val();

				if(code == ""){ $.notify("Code Cannot Be Empty", {type:"info",icon:"info"}); return;	}
				if(name == ""){ $.notify("Name Cannot Be Empty", {type:"info",icon:"info"}); return;	}
				


		
				if(type != "new"){
					$.confirm({
                            title: 'Schedule Updating',
                            content: 'Update Schedule?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Update',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_schedule')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: type,
												code : code ,
												name : name ,
												monday : monday_edit ,
												tuesday : tuesday_edit ,
												wednesday : wednesday_edit ,
												thursday : thursday_edit ,
												friday : friday_edit ,
												saturday : saturday_edit ,
												sunday : sunday_edit ,
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_list();
													$("#sched_add_edit").modal("hide");
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
				}else{
					$.confirm({
                            title: 'Schedule Management',
                            content: 'Add New Schedule?',
                            escapeKey: 'cancelAction',
                            buttons: {
                                confirm: {
                                    btnClass: 'btn-green',
                                    text: 'Add New',
                                    action: function(){
                                        HoldOn.open(holdon_option);

                                        $.ajax({
                                                url: "{{route('update_schedule')}}",
                                            data: {
                                                _token : "{{csrf_token()}}", 
												id: type,
												code : code ,
												name : name ,
												monday : monday_edit ,
												tuesday : tuesday_edit ,
												wednesday : wednesday_edit ,
												thursday : thursday_edit ,
												friday : friday_edit ,
												saturday : saturday_edit ,
												sunday : sunday_edit ,
                                            },
                                                success: function (data) { 
                                                    if(data){
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                    }else{
                                                        $.notify("System Failed Please Refresh", {type:"info",icon:"info"}); 
                                                    }
                                                   
													load_schedule_list();
													$("#sched_add_edit").modal("hide");
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


				




			});


			$('#sched_add_edit').on('show.bs.modal', function(e) {
				var sched_id = $(e.relatedTarget).data('sched_id');

				if(sched_id != 'new'){
					$.ajax({
						url: "{{route('get_sched_info')}}",
					data: {
						_token : "{{csrf_token()}}", 
						id: sched_id
					},
						success: function (data) { 
							if(data != "No Schedule Information"){
								$(e.currentTarget).find('#code').val(data.code);
								$(e.currentTarget).find('#name').val(data.name);
								$(e.currentTarget).find('#monday_edit').val(data.monday).change();
								$(e.currentTarget).find('#tuesday_edit').val(data.tuesday).change();
								$(e.currentTarget).find('#wednesday_edit').val(data.wednesday).change();
								$(e.currentTarget).find('#thursday_edit').val(data.thursday).change();
								$(e.currentTarget).find('#friday_edit').val(data.friday).change();
								$(e.currentTarget).find('#saturday_edit').val(data.saturday).change();
								$(e.currentTarget).find('#sunday_edit').val(data.sunday).change();
							
								$(e.currentTarget).find('#add_edit_post').val(sched_id);
								$(e.currentTarget).find('#add_edit_post').empty().text("Update");
								
								


							}else{
								$.notify(data, {type:"info",icon:"info"}); 
							}
							
							HoldOn.close();
						},
						dataType: 'json',
						method: 'POST'
					});



				} //EDIT
				else{
								$(e.currentTarget).find('#code').val("");
								$(e.currentTarget).find('#name').val("");
								$(e.currentTarget).find('#monday_edit').val(0).change();
								$(e.currentTarget).find('#tuesday_edit').val(0).change();
								$(e.currentTarget).find('#wednesday_edit').val(0).change();
								$(e.currentTarget).find('#thursday_edit').val(0).change();
								$(e.currentTarget).find('#friday_edit').val(0).change();
								$(e.currentTarget).find('#saturday_edit').val(0).change();
								$(e.currentTarget).find('#sunday_edit').val(0).change();
							
								$(e.currentTarget).find('#add_edit_post').val(sched_id);
								$(e.currentTarget).find('#add_edit_post').empty().text("Add");

				}

			});


		//triggered when modal is about to be shown
			$('#sched_info').on('show.bs.modal', function(e) {
			var code = $(e.relatedTarget).data('code');
			var name = $(e.relatedTarget).data('name');
			var am_in = $(e.relatedTarget).data('am_in');
			var am_out = $(e.relatedTarget).data('am_out');
			var pm_in = $(e.relatedTarget).data('pm_in');
			var pm_out = $(e.relatedTarget).data('pm_out');
			var ot_in = $(e.relatedTarget).data('ot_in');
			var ot_out = $(e.relatedTarget).data('ot_out');
			var grace_period = $(e.relatedTarget).data('grace_period');

			
			$(e.currentTarget).find('#am_in').empty().text( am_in);
			$(e.currentTarget).find('#am_out').empty().text( am_out);
			$(e.currentTarget).find('#pm_in').empty().text( pm_in);
			$(e.currentTarget).find('#pm_out').empty().text( pm_out);
			$(e.currentTarget).find('#ot_in').empty().text( ot_in);
			$(e.currentTarget).find('#ot_out').empty().text( ot_out);
			$(e.currentTarget).find('#grace_period').empty().text( grace_period+" (mins)");
			
			

			//populate the textbox
			$(e.currentTarget).find('.modal-title').empty().text( name+" ("+code+")");

			});

			// add delete in sched
			function delete_sched(id,type){
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
										url: "{{route('delete_sched')}}",
									data: {
										_token : "{{csrf_token()}}", 
										id: id,
										type: type
									},
										success: function (data) { 
											
											$.notify(data, {type:"info",icon:"info"}); 
											if(type == "sched_list"){
												load_schedule_list();
											}else{
												load_schedule_library();
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