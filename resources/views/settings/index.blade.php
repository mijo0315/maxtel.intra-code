@extends('layouts.front-app')

@section('title')

{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Setting

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

					
						

						<div class="col-xl-12 col-sm-12 col-12 ">

							<div class="row">

								<div class="col-xl-6 col-sm-12 col-12">

									<div class="card ">

										<div class="card-header" style="background-color: #2f47ba;">

											<h2 class="card-titles" style="color: white;">Company  Logo </h2>

										</div>

										<div class="card-body">

											<div class="company-logo">

												<label class="logo-upload" for="logo_main">

													<input type="file" id="logo_main" name="logo_main" accept="image/*" />

													<a><i data-feather="edit"></i></a>

												</label>

												<img src="{{ asset_with_env(str_replace('public/', '', Auth::user()->company['logo_main'])) }}" id="logo_main_view" alt="logo" style="max-height: 6vh;" />

											</div>

                                        

                                        </div>











									</div>

								</div>



                                <div class="col-xl-6 col-sm-12 col-12">

									<div class="card ">

										<div class="card-header" style="background-color: #2f47ba;">

											<h2 class="card-titles" style="color: white;">Company icon</h2>

										</div>

										<div class="card-body">

											<div class="company-logo">

												<label class="logo-upload" for="logo_sub">

													<input type="file" id="logo_sub" name="logo_sub" accept="image/*" />

													<a><i data-feather="edit"></i></a>

												</label>

												<img src="{{ asset_with_env(str_replace('public/', '', Auth::user()->company['logo_sub'])) }}" id="logo_sub_view" alt="logo" style="max-height: 6vh;" />

											</div>

                                        

                                        </div>











									</div>

								</div>







								<div class="col-xl-6 col-sm-12 col-12 ">

									<div class="card ">

										<div class="card-header" style="background-color: #2f47ba;">

											<h2 class="card-titles" style="color: white;">Company Details</h2>

										</div>

										<div class="card-body">

											<div class="row">

												<div class="col-xl-12 col-sm-6 col-12 ">

													<div class="form-group">

														<label>Company Name </label>

														<input type="text" name="company_name" id="company_name" value="{{Auth::user()->company['company_name']}}" placeholder="Company Name">

													</div>

												</div>

												<div class="col-xl-12 col-sm-6 col-12 ">

													<div class="form-group">

														<label>Company Address</label>

														<input type="text" name="company_address" id="company_address" value="{{Auth::user()->company['address']}}" placeholder="Company Address">

													</div>

												</div>



												<div class="col-xl-12 col-sm-6 col-12 ">

													<div class="form-group">

														<label>Government Agency</label>

														

														<select name="is_gov" id="is_gov" class="form-control form-select">

															@if(Auth::user()->company['is_government'] == "1")

																@php

																$yes_gov = "selected";

																$no_gov = "";

																@endphp

															@else

																@php

																$yes_gov = "";

																$no_gov = "selected";

																@endphp

															@endif

															<option value="1" {{$yes_gov}}>YES</option>

															<option value="0" {{$no_gov}}>NO</option>



														</select>



													</div>

												</div>



                                                <div class="col-xl-12 col-sm-6 col-12 d-none">

													<div class="form-group">

														<label>Company Url</label>

														<input type="text" name="company_url" id="company_url" value="{{Auth::user()->company['url']}}" placeholder="Company Url">

													</div>

												</div>



											</div>

											

										</div>

									</div>

								</div>









                                <div class="col-xl-6 col-sm-12 col-12 ">

									<div class="card ">

										<div class="card-header" style="background-color: #2f47ba;">

											<h2 class="card-titles" style="color: white;">Company Settings</h2>

										</div>

										<div class="card-body">

											<div class="row">

												<div class="col-xl-12 col-sm-6 col-12 ">

													<div class="form-group">

														<label>Default Work Schedule </label>

                                                        <select class="form-group form-select" id="default_work_settings" name="default_work_settings">

                                                            <option value="0">Select Default Schedule</option>

                                                            @foreach($schedule_list as $schedule)

                                                                @if(Auth::user()->company['default_work_settings'] == $schedule->id)

                                                                <option value="{{$schedule->id}}" selected>{{$schedule->name}}</option>

                                                                @else

                                                                <option value="{{$schedule->id}}">{{$schedule->name}}</option>

                                                                @endif

                                                                

                                                            @endforeach

                                                        </select>

														

													</div>

												</div>

												<div class="col-xl-12 col-sm-6 col-12 ">

													<div class="form-group">

														<label>Default Monthly Divisor / No of Days Per month</label>

														<input type="text" name="divisor" id="divisor" value="{{Auth::user()->company['divisor']}}" placeholder="Divisor">

													</div>

												</div>



												<div class="col-xl-12 col-sm-6 col-12 ">

													<div class="form-group">

														<label>Default Daily Divisor / No of Hours Per Day</label>

														<input type="text" name="daily_divisor" id="daily_divisor" value="{{Auth::user()->company['daily_divisor']}}" placeholder="Divisor">

													</div>

												</div>



                                               



											</div>



                                            <div class="col-xl-12 col-sm-6 col-12 ">

                                                <div class="form-group">

													<label>Update Setting</label>



                                                    <a class="btn btn-apply" onclick="update_settings();">Save Changes</a>

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

@stop



@section("scripts")

    <script>









        function update_settings(){

            main_logo = "";

            if($('#logo_main').val() != ""){

                var main_logo = $("#logo_main_view").attr("src");

                    main_logo = main_logo.split(",")[1];

            }

            sub_logo = "";

            if($('#logo_sub').val() != ""){

                var sub_logo = $("#logo_sub_view").attr("src");

                    sub_logo = sub_logo.split(",")[1];

            }



            var company_name = $("#company_name").val();

			var is_gov = $("#is_gov").val();

			var daily_divisor = $("#daily_divisor").val();

			

			

            var address = $("#company_address").val();

            var url = $("#company_url").val();

            var default_work_settings = $("#default_work_settings").val();

            var divisor = $("#divisor").val();

            

            $.confirm({

                            title: 'Confirmation',

                            content: 'Update Settings?',

                            escapeKey: 'cancelAction',

                            buttons: {

                                confirm: {

                                    btnClass: 'btn-green',

                                    text: 'Update',

                                    action: function(){

                                        HoldOn.open(holdon_option);



                                        $.ajax({

                                                url: "{{route('update_setting')}}",

                                            data: {

                                                _token : "{{csrf_token()}}", 

                                               main_logo:main_logo,

                                               sub_logo:sub_logo,

                                               company_name: company_name,

                                               address:address,

                                               url:url,

                                               default_work_settings:default_work_settings,

                                               divisor:divisor,

											   is_gov:is_gov,

											   daily_divisor:daily_divisor,

                                            },

                                                success: function (data) { 

                                                    if(data){

                                                        $.notify("Settings Updated", {type:"info",icon:"info"}); 

                                                    }else{

                                                        $.notify(data, {type:"info",icon:"info"}); 

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





        $('#logo_main').on("change", function(){

            var fileInput = $('#logo_main')[0];

            var file = fileInput.files[0];

            var reader = new FileReader();

            reader.onload = function(event) {

            var base64Data = event.target.result;

        

            $("#logo_main_view").removeAttr("src");

            $("#logo_main_view").attr("src", base64Data);

            };

            reader.readAsDataURL(file);

        });

           



        $('#logo_sub').on("change", function(){

            var fileInput = $('#logo_sub')[0];

            var file = fileInput.files[0];

            var reader = new FileReader();

            reader.onload = function(event) {

            var base64Data = event.target.result;

        

            $("#logo_sub_view").removeAttr("src");

            $("#logo_sub_view").attr("src", base64Data);

            };

            reader.readAsDataURL(file);

        });





        $("#default_work_settings").select2();

    </script>





@stop