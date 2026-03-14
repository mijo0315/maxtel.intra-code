@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Payroll Management
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
<div class="page-wrapper" id="file_management_page">
    <div class="content container-fluid">
        <div class="col-xl-12 col-sm-12 col-12 ">
            <div class="card oth_income_card oth_library" >
                <div class="card-header" style="background-color: #2f47ba;">
                    <h2 class="card-titles" style="color: white;">File Management <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
                </div>
        
        
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 ">
              
            
                    <div class="card-body">
                        <div class="row">
                           
                                 <!-- update file -->
                                <div class="col-md-3" id="emp_div">
                                 
                                    <select id="emp_list" class="form-control form-select w-100">
                                        <option value="0">Select Employee</option>
                                        @foreach($tbl_employee as $emp)
                                            <option value="{{$emp->id}}">{{$emp->emp_code}} - {{$emp->last_name}}, {{$emp->first_name}} {{$emp->middle_name}} {{$emp->ext_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <!-- update file -->
                                    <button id='modal_show' class="btn btn-success w-100" 
                                    >  Upload File </button>
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
                                <table class="table table-striped table-bordered table-hover" id="tbl_employee_file">
                                    <thead>
                                        <tr>
                                            <th >File</th>
                                            <th >Type</th>
                                            <th >Date Uploaded</th>
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
<div class="modal fade" tabindex="-1" role="dialog" id="file_upload_modal">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title">Employee File Uploading</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
            <input type="hidden" id="emp_id_modal">
                <div class="row">
                    <div class="col-md-4">
                        <label class="btn btn-sm btn-info w-100">Name</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="file_name" class="form-control w-100" placeholder="File Name">
                    </div>
                </div>
    
                <div class="row mt-2">
                    <div class="col-md-4">
                        <label class="btn btn-sm btn-info w-100">Type</label>
                    </div>
                    <div class="col-md-8">
                        <select id="file_type" class="form-control form-select" style="width:100%;">
                            <option value="0">Select File Type</option>
    
                            @foreach($lib_file_type as $file_type)
                                <option value="{{$file_type->id}}"> {{$file_type->name}} </option>
                            @endforeach
                        </select>
    
    
                    </div>
                </div>
            
                <div class="row mt-2">
                   <div class="col-md-12" id="my-dropzone" class="dropzone w-70 m-10" style="border-style:dotted; height:200px; overflow:auto;">
                    <p>Drag and drop files here or click to upload.</p>
                    </div>
                </div>
                
                {{-- <form action="{{route('emp_upload_file')}}" class="dropzone" id="my-great-dropzone"></form> --}}
			
            
        
            
		</div>
		<div class="modal-footer">
			
		  <button type="button" id="submit-btn" class="btn btn-success">Upload</button>
		  <button type="button" id="modal_close" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
 
@endif
@stop
@section("scripts")
<script>
$( document ).ready(function() {
    load_all_files_tbl(); //load all files
    // update file
    var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
    if(user_type == "employee"){
        $("#emp_div").hide();
    }
    var user_count  = {{count($tbl_employee)}};
    if(user_count == 1){
        @if(isset($tbl_employee[0]->id))
            var curr_id = {{$tbl_employee[0]->id}};
        @else
            var curr_id =0;
        @endif
        $("#emp_list").val(curr_id).change();
    }
    $(".form-select").select2();
                Dropzone.autoDiscover = false; 
                var myDropzone = new Dropzone("#my-dropzone", {
                url: "{{ route('emp_upload_file') }}", // Specify the upload URL
                autoProcessQueue: false, // Disable automatic file uploads
                addRemoveLinks: true,
                // maxFilesize: 2,
                params: {
                    "_token": "{{ csrf_token() }}", 
                    "file_name": "",
                    "file_type": "",
                    "emp_id": ""
                    // Add more key-value pairs as needed
                    },
                // Add additional Dropzone.js options as needed
            });
                    // Handle the addedfile event
                myDropzone.on("addedfile", function (file) {
                    // Perform actions when a file is added
                    // You can access the file using the 'file' parameter
                });
            // Handle form submission
            document.getElementById("submit-btn").addEventListener("click", function (event) {
                event.preventDefault(); // Prevent the form from being submitted immediately
                // Check if any files are in the queue
                if (myDropzone.getQueuedFiles().length > 0) {
                    var file_name =   $("#file_name").val();
                    var file_type =   $("#file_type").val();
                    var emp_id =   $("#emp_id_modal").val();
                    
                    
                    if(file_name== "" || file_name == null ){
                        $.notify("File Name Empty", {type:"danger",icon:"info"});
                    }else if(file_type == "0"){
                        $.notify("File Type Empty", {type:"danger",icon:"info"});
                    }else{
                        myDropzone.options.params.file_name = file_name;
                        myDropzone.options.params.file_type = file_type;
                        myDropzone.options.params.emp_id = emp_id;
                        
                        myDropzone.processQueue(); // Process the files and upload them
                    }
                   
              
                } else {
                // Handle the case when no files are selected
                        $.notify("Upload File", {type:"danger",icon:"info"});
                }
            });
            myDropzone.on("success", function (file, response) {
            myDropzone.removeAllFiles();
            $("#file_upload_modal").modal("hide");
            load_file_tbl();
            });
});
$("#emp_list").on("change", function(){
    load_file_tbl();
});
$("#modal_show").on("click", function(){
    var emp_id = $("#emp_list").val();
    
    if(emp_id != "0"){
        $("#emp_id_modal").val(emp_id);
        $("#file_upload_modal").modal("show");
    }
});
function load_file_tbl(){
    var emp_id = $("#emp_list").val();
    $('#tbl_employee_file').DataTable({
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
				"url": "{{ route('load_file_tbl') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}" ,
					"emp_id": emp_id
				}
			},
			"columns":[
				{'data': 'file'},
				{'data': 'type'},
				{'data': 'date_created'},
				{'data': 'action', 'orderable': false, 'searchable': false},
			]
		});
}
//load all files
function load_all_files_tbl(){
    const employeeIds = @json($tbl_employee->pluck('id'));
    $('#tbl_employee_file').DataTable({
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
				"url": "{{ route('load_file_tbl') }}",
				"dataType": "json",
				"type": "POST",
				"data":{
					"_token": "{{ csrf_token() }}", 
					"page": "{{Route::current()->action['as']}}" ,
					"emp_id": employeeIds
				}
			},
			"columns":[
				{'data': 'file'},
				{'data': 'type'},
				{'data': 'date_created'},
				{'data': 'action', 'orderable': false, 'searchable': false},
			]
		});
}
function delete_file(ids){
    $.confirm({
						title: 'Employee Files',
						content: 'Delete Uploaded File?',
						escapeKey: 'cancelAction',
						buttons: {
							confirm: {
								btnClass: 'btn-green',
								text: "Delete",
								action: function(){
					
									$.ajax({
											url: "{{route('delete_emp_file')}}",
										data: {
											_token : "{{csrf_token()}}", 
											id: ids,
											
										},
											success: function (data) { 
												$.notify(data, {type:"info",icon:"info"}); 
												
												load_file_tbl();
											
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