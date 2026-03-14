@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - User Management
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
            <div class="col-xl-12 col-sm-12 col-12">
				@if(Auth::user()->company['version'] == 1)
               
				@endif
            </div>
           
      
            
        </div>
		<div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card oth_income_card oth_library" >
                <div class="card-header" style="background-color: #2f47ba;">
                    <h2 class="card-titles" style="color: white;">User Management <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
                </div>
		
<div class="row">
	<div class="col-xl-12 col-sm-12 col-12 ">
		<a data-toggle='modal'  id="add_user_access" data-target='#add_modal' class="btn btn-apply btn-lg m-3">   Add User Access </a>
		
	</div>			
	
</div>
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 ">
             
            
                   
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="user_tbl">
                                    <thead>
                                        <tr>
                                            <th >Linked Name</th>
                                            <th >Linked Position</th>
                                            <th >Role Id</th>
                                            <th >Username</th>
                                            <th >Action</th>
                                          
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
<div class="modal fade" tabindex="-1" role="dialog" id="add_modal">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Add User</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">User Name</label>
				</div>
				<div class="col-md-8">
                    <input type="text" id="new_user_name" class="form-control w-100" placeholder="User Name">
				</div>
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Password</label>
				</div>
				<div class="col-md-8">
					<input id="new_password" type="password" class="form-control mt-1 mb-1 w-100" placeholder="Password" autocomplete="new-password">
				</div>
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Confirm Password</label>
				</div>
				<div class="col-md-8">
					<input id="confirm_password" type="password" class="form-control mt-1 mb-1 w-100" placeholder="Password" autocomplete="new-password">
				</div>
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Role</label>
				</div>
				<div class="col-md-8">
                    <select id="new_role_select" class="form-control form-select" style="width:100%">
                        <option value="0">Select Role </option>
                        @foreach($role as $rol)
                            <option value="{{$rol->id}}">{{$rol->name}} - {{$rol->type}}</option>
                        @endforeach
                    </select>
				</div>
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Link Employee</label>
				</div>
				<div class="col-md-8">
                    <select id="new_employee_select" class="form-control form-select" style="width:100%">
                        <option value="0">Select Employee</option>
                    </select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="add_user_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
<div class="modal fade" tabindex="-1" role="dialog" id="credential_modal">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Credential</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">User Name</label>
				</div>
				<div class="col-md-8">
                    <input type="text" id="user_user_name" class="form-control w-100" placeholder="User Name">
				</div>
			</div>
            <div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Password</label>
				</div>
				<div class="col-md-8">
					<input id="user_password" type="password" class="form-control mt-1 mb-1 w-100" placeholder="Password" autocomplete="new-password">
				</div>
			</div>
            
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="credential_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
  <div class="modal fade" tabindex="-1" role="dialog" id="change_role_modal">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Change Role</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Role</label>
				</div>
				<div class="col-md-8">
                    <select id="role_select" class="form-control form-select" style="width:100%">
                        <option value="0">Select Role </option>
                        @foreach($role as $rol)
                            <option value="{{$rol->id}}">{{$rol->name}} - {{$rol->type}}</option>
                        @endforeach
                    </select>
				</div>
			</div>
       
            
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="change_role_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
 
  <div class="modal fade" tabindex="-1" role="dialog" id="link_modal">
	<div class="modal-dialog modal-lg " role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Link Employee</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Employee</label>
				</div>
				<div class="col-md-8">
                    <select id="employee_select" class="form-control form-select" style="width:100%">
                        <option value="0">Select Employee</option>
                    </select>
				</div>
			</div>
       
            
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="link_btn" class="btn btn-success">Submit</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    load_user_tbl();
	var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
    if(user_type == "employee"){
        $("#add_user_access").hide("fast");
    }else{
        $("#add_user_access").show("fast");
    }
});
$("#link_btn").on("click", function(){
   var id = $("#link_btn").val();
   var employee =  $("#employee_select").val();
   if(employee == "0"){
      var text = "Employee Link Is Empty Continue?";
    }else{
      var text = "Link Employee ?"
    }
    $.confirm({
						title: 'User Management',
						content: text,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Submit",
								action: function(){
									$.ajax({
											url: "{{route('update_link_emp')}}",
										data: {
											_token : "{{csrf_token()}}", 
											id: id,
                                            employee: employee,
										},
											success: function (data) { 
                                                $.notify(data, {type:"info",icon:"info"}); 
                                                $("#link_modal").modal("hide");
                                                load_user_tbl();
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
$("#add_user_btn").on("click", function(){
    var user_name = $("#new_user_name").val();
    var new_password = $("#new_password").val();
    var confirm_password = $("#confirm_password").val();
    var employee = $("#new_employee_select").val();
    var role = $("#new_role_select").val();
    var pattern = /@/;
    // if(!pattern.test(user_name))
    // {
    //     $.notify("User name must be email", {type:"info",icon:"info"}); 
    //     return;
    // }
    if(new_password != confirm_password){
        $.notify("Password Mismatched", {type:"info",icon:"info"}); 
        return;
    }
    if(user_name == ""){
        $.notify("User Name is required", {type:"info",icon:"info"}); 
        return;
    }
    if(new_password == ""){
        $.notify("Password is required", {type:"info",icon:"info"}); 
        return;
    }
    var text ="";
    if(role == "0"){
      text = text + "Role Is Empty";
    }
    if(employee == "0"){
        text = text + "\n Employee is not linked";
    }
     text =  text + "\n Create User?";
            
            
            $.confirm({
						title: 'User Management',
						content: text,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Submit",
								action: function(){
									$.ajax({
											url: "{{route('create_user_management')}}",
										data: {
											_token : "{{csrf_token()}}", 
											user_name: user_name,
                                            new_password: new_password,
                                            employee: employee,
                                            role: role,
										},
											success: function (data) { 
                                                if(data != "Username already Exist"){
                                                    $("#add_modal").modal("hide");
                                                }
                                                $.notify(data, {type:"info",icon:"info"}); 
                                                load_user_tbl();
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
$('#add_modal').on('show.bs.modal', function(e) {
    $.ajax({
            url: "{{route('user_get_employee')}}",
        data: {
            _token : "{{csrf_token()}}", 
            id: "new"
        },
            success: function (data) { 
                $("#new_employee_select").empty().append("<option value='0'>Select Employee</option>");
                $.each(data, function( index, value ) {
                if(value.ext_name == null){
                    value.ext_name = "";
                }
                $("#new_employee_select").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                
                });
            },
            dataType: 'json',
            method: 'POST'
        });
  
});
$('#link_modal').on('show.bs.modal', function(e) {
    var id = $(e.relatedTarget).data('id');
    var employee = $(e.relatedTarget).data('employee');
    $("#link_btn").val(id);
    $.ajax({
            url: "{{route('user_get_employee')}}",
        data: {
            _token : "{{csrf_token()}}", 
            employee: employee,
            id: id
        },
            success: function (data) { 
                $("#employee_select").empty().append("<option value='0'>Select Employee</option>");
                $.each(data, function( index, value ) {
                if(value.ext_name == null){
                    value.ext_name = "";
                }
                $("#employee_select").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                
                });
                $("#employee_select").val(employee).change();
            },
            dataType: 'json',
            method: 'POST'
        });
  
});
$('#change_role_modal').on('show.bs.modal', function(e) {
    var id = $(e.relatedTarget).data('id');
    var role_id = $(e.relatedTarget).data('role_id');
    $("#change_role_btn").val(id);
    $("#role_select").val(role_id).change();
});
$("#change_role_btn").on("click", function(){
    var role_select = $("#role_select").val();
    var id =  $("#change_role_btn").val();
    if(role_select == "0"){
      var text = "Role Is Empty Continue?";
    }else{
      var text = "Update Role ?"
    }
    $.confirm({
						title: 'User Management',
						content: text,
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Submit",
								action: function(){
									$.ajax({
											url: "{{route('update_role')}}",
										data: {
											_token : "{{csrf_token()}}", 
											id: id,
                                            role_select: role_select,
										},
											success: function (data) { 
                                                $.notify(data, {type:"info",icon:"info"}); 
                                                $("#change_role_modal").modal("hide");
                                                load_user_tbl();
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
$('#credential_modal').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('id');
        var user_name = $(e.relatedTarget).data('user_name');
        $("#credential_btn").val(id);
        $("#user_user_name").val(user_name);
});
$("#credential_btn").on("click", function(){
   var id =  $("#credential_btn").val();
   var user_name =  $("#user_user_name").val();
    var password =  $("#user_password").val();
    if(user_name == ""){
        $.notify("User name Cannot be Empty", {type:"info",icon:"info"}); 
        return;
    }
    var pattern = /@/;
    // if(!pattern.test(user_name))
    // {
    //     $.notify("User name must be email", {type:"info",icon:"info"}); 
    //     return;
    // }
             $.confirm({
						title: 'User Management',
						content: 'Update Credential?',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Submit",
								action: function(){
									$.ajax({
											url: "{{route('update_credential')}}",
										data: {
											_token : "{{csrf_token()}}", 
											id: id,
                                            user_name: user_name,
                                            password: password
										},
											success: function (data) { 
                                                if(data != "Username already Exist"){
                                                    $("#credential_modal").modal("hide");
                                                    load_user_tbl();
                                                }
                                                $.notify(data, {type:"info",icon:"info"}); 
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
function load_user_tbl(){
    
    $('#user_tbl').DataTable({
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
				"url": "{{ route('load_user_list') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}" ,
					
				}
			},
			"columns":[
				{'data': 'name'},
				{'data': 'position'},
                {'data': 'role'},
                {'data': 'email'},
                {'data': 'action', 'orderable': false, 'searchable': false},
             	
			]
		});
}
	// add delete in user
	function delete_user(id){
		$.confirm({
			title: 'Delete',
			content: 'Are you sure to delete this user?',
			escapeKey: 'cancelAction',
			buttons: {
				confirm: {
					btnClass: 'btn-danger',
					text: "Yes",
					action: function(){
						HoldOn.open(holdon_option);
						$.ajax({
								url: "{{route('delete_user')}}",
							data: {
								_token : "{{csrf_token()}}", 
								id: id,
							},
								success: function (data) { 
									
									$.notify(data, {type:"info",icon:"info"}); 
									load_user_tbl();
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