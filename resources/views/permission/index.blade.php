@extends('layouts.front-app')

@section('title')

{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Permission Management

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



<div class="page-wrapper" id="permission_management_page">

    <div class="content container-fluid">

        <div class="row" >



            @if(Auth::user()->company['version'] == 1)



            @endif



            <div class="col-xl-10 col-sm-10 col-10 mb-2">

                <div class="head-link-set">

                    <ul>

                        {{-- <li><a class="menu_tab active"  onclick="" id="oth_income_menu">Permission Management</a></li> --}}

                        

                    </ul>

                </div>

            </div>

      

            

        </div>



        <div class="col-xl-12 col-sm-12 col-12 ">

            <div class="card oth_income_card oth_library" >

                <div class="card-header" style="background-color: #2f47ba;">

                    <h2 class="card-titles" style="color: white;">Permission Management <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>

                </div>



   

        <div class="row">

            <div class="col-xl-12 col-sm-12 col-12 ">

               

                   

                    <div class="card-body">



                        <div class="row">



                            <div class="col-md-2">

                                <label class="btn btn-info btn-sm w-100">Role</label> 

                            </div>

                          

                            <div class="col-md-3">

                                

                                <select id="role_access" class="form-control form-select w-100">

                                    <option value="0">New Role</option>

                                    @foreach($tbl_role_access as $role)

                                        <option value="{{$role->id}}">{{$role->name}}</option>

                                    @endforeach

                                </select>

                            </div>

                                

                            <div class="col-md-2">

                                <label class="btn btn-info btn-sm w-100">Role Type</label> 

                            </div>

                          

                                <div class="col-md-3">

                                 

                                    <select id="role_type" class="form-control form-select w-100">

                                        <option value="Admin">Admin</option>

                                        <option value="hr">Human Resource</option>

                                        <option value="employee">Employee</option>

                                        

                                        

                                    </select>

                                </div>

                            

                           

                        </div>

                        



                        <div class="row">

                                <div class="col-md-2">

                                    <label class="btn btn-info btn-sm w-100">Role Name</label>

                                </div>

                                <div class="col-md-3">

                                    <input type="text" class="form-control" id="role_name" placeholder="Role Name">

                                </div>

                           

                        



                            <div class="col-md-2">

                                <label class="btn btn-info btn-sm w-100">Status</label>

                            </div>

                            <div class="col-md-3">

                                <select id="role_is_active" class="form-control form-select">

                                    <option value="1">Active</option>

                                    <option value="0">Inactive</option>

                                    

                                </select>

                            </div>

                       

                            <div class="col-md-2">

                                <button class="btn btn-success btn-sm w-100" id="submit_role">Submit</button>

                            </div>



                        </div>





                    </div>

               

            </div>

        </div>

    





        <div class="row">

            <div class="col-xl-12 col-sm-12 col-12 ">

               

            

                    <div class="card-body">

                        <div class="row">

                            

                            <div class="col-xl-12 col-sm-12 col-12 table-responsive">

                                <table class="table table-striped table-bordered table-hover" id="tbl_permission">

                                    <thead>

                                        <tr>

                                            <th >Page</th>

                                            <th >Access</th>

                                          

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











 





@endif



@stop



@section("scripts")

<script>

$( document ).ready(function() {

    $(".form-select").select2();

    load_permission_tbl();

});



$("#role_access").on("change", function(){

    $.ajax({

            url: "{{route('check_role_data')}}",

        data: {

            _token : "{{csrf_token()}}", 

            role: $(this).val(),

            

        },

            success: function (data) { 



                $("#role_type").val(data["type"]).change();

                $("#role_name").val(data["name"]);

                $("#role_is_active").val(data["is_active"]).change();

                $("#submit_role").val(data["id"]);



                

            },

            dataType: 'json',

            method: 'POST'

        });



        load_permission_tbl();

});





$("#submit_role").on("click", function(){



    var role_type = $("#role_type").val();

    var role_name = $("#role_name").val();

    var role_is_active = $("#role_is_active").val();

    var submit_role = $("#submit_role").val();



    $.confirm({

						title: 'Role Permission',

						content: 'Submit Role Data?',

						escapeKey: 'cancelAction',

						buttons: {

							confirm: {

								btnClass: 'btn-green',

								text: "Submit",

								action: function(){

					



									$.ajax({

											url: "{{route('submit_role_data')}}",

										data: {

											_token : "{{csrf_token()}}", 

											role_type: role_type,

                                            role_name: role_name,

                                            role_is_active: role_is_active,

                                            submit_role: submit_role,

											

										},

											success: function (data) { 

                                                if(data != "Success"){

                                                    $("#role_access").append("<option value='"+data["id"]+"' >"+data["name"]+"</option>");

                                                    $("#role_access").val(data["id"]).change();

                                                }

                                                $.notify("Success", {type:"info",icon:"info"}); 

											

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



function permission(id){

    var role_access = $("#role_access").val();

    $("#permission_"+id).attr("style", "border-color:orange");

        $.ajax({

                url: "{{route('update_access_status')}}",

            data: {

                _token : "{{csrf_token()}}", 

                role: role_access,

                page: id,

                access: $("#permission_"+id).val()

                

            },

                success: function (data) { 

                    $("#permission_"+id).attr("style", "border-color:green");

                },

                dataType: 'json',

                method: 'POST'

            });



    



}







function load_permission_tbl(){

    var role_access = $("#role_access").val();

    $('#tbl_permission').DataTable({

			"bDestroy": true,

			"autoWidth": false,

			"searchHighlight": true,

			"searching": true,

			"processing": true,

			"serverSide": true,

			"orderMulti": true,

			"order": [],

			"pageLength": 50,

			"ajax": {

				"url": "{{ route('load_page_access') }}",

				"dataType": "json",

				"type": "POST",

				"data":{

					"_token": "{{ csrf_token() }}", 

					"page": "{{Route::current()->action['as']}}" ,

					"role": role_access

				}

			},

			"columns":[

				{'data': 'page'},

				{'data': 'access'},

				

			]

		});









}







</script>

@stop