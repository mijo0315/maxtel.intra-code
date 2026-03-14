@extends('layouts.front-app')

@section('title')

{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Loan

@stop

@section("styles")

<style>

	th{

		text-align: center;

	}

    .btn-check{

       display:none;

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

<div class="page-wrapper" id="oth_income_page">

    <div class="content container-fluid">

        <div class="row" >

            @if(Auth::user()->company['version'] == 1)

           

            @endif

            <div class="col-xl-12 col-sm-12 col-12 mb-2">

                <div class="head-link-set">

                    <ul>

                        {{-- <li><a class="menu_tab active"  onclick="" id="oth_income_menu">Employee Loan</a></li> --}}

                        {{-- <li><a class="menu_tab" id="library_menu"  onclick="">Library</a></li>

                         --}}

                        

                        

                        

                    </ul>

                </div>

            </div>

        

            @include("loan.library")

            

        </div>

       

    </div>

</div>

 

@endif

@stop

@section("scripts")

<script>

$( document ).ready(function() {

        // update loan

        var emp_id = $("#emp_id").val();
        var role_id = "{{ Auth::user()->role_id }}";
        $("#loan_status").empty();
        // if(role_id == "11"){
        //     $("#loan_status").append('<option value="4">Approved</option>');
        // }else if(role_id == "15" || role_id == "1"){
        $("#loan_status").append('<option value="1">Approved</option>');
        // }
        $("#loan_status").append('<option value="0">Pending</option>');
        $("#loan_status").append('<option value="2">Denied</option>');

        emp_tbl(emp_id);

       

            $(".form-select").select2();

            loan_library_list();

            $( "#start_date" ).datepicker({ dateFormat: 'yy-mm-dd' });

            $( "#end_date" ).datepicker({dateFormat: 'yy-mm-dd' });

            

        });

        function edit_loan_info(collection){

            var datasets = collection.split(";");

            var loan_id = datasets[0];

            var file_id = datasets[1];

            var emp_id = datasets[2];

            var pay_type = datasets[3];

            var total_amount = datasets[4];

            var amount_to_pay = datasets[5];

            var date_start = datasets[6];

            var date_to = datasets[7];

            var notes = datasets[8];

            var loan_status = datasets[9];

            var variance = datasets[10];

            var balance = datasets[11];

            

            

            

            view_emp_data(emp_id);

            $("#add_edit_tbl").hide("fast");

            $("#selected_employee").attr("readonly", "true");

            $("#payment_type").val(pay_type).change();

            $("#payment_variance").val(variance).change();

            $("#loan_status").val(loan_status).change();

            

            $("#principal_amount").val(total_amount);

            $("#deduction_amount").val(amount_to_pay);

            $("#balance_amount").val(balance);

            

            $("#start_date").val(date_start);

            $("#end_date").val(date_to);

            $("#notes").val(notes);

            $("#add_edit_btn").val(file_id);

            $("#loan_id").val(loan_id);

            $("#delete_btn").val(file_id);

            $("#delete_btn").show("fast");

            $("#cancel_btn").show("fast");

            $("#loan_div").show("fast");

        }

       

        $('#add_edit_employee').on('show.bs.modal', function(e) {

            $('#selected_employee').select2({ dropdownParent: $('#add_edit_employee') });

          

            var loan_id = $(e.relatedTarget).data('id');

            var file_id = $(e.relatedTarget).data('file_id');

            var emp_id = $(e.relatedTarget).data('emp_id');

            var pay_type = $(e.relatedTarget).data('pay_type');

            var total_amount = $(e.relatedTarget).data('total_amount');

            var amount_to_pay = $(e.relatedTarget).data('amount_to_pay');

            var date_start = $(e.relatedTarget).data('date_start');

            var date_to = $(e.relatedTarget).data('date_to');

            var notes = $(e.relatedTarget).data('notes');

            

            $("#delete_btn").val(file_id);

            employee_list(loan_id, emp_id);

            

            $("#payment_type").val(pay_type).change();

            $("#principal_amount").val(total_amount);

            $("#deduction_amount").val(amount_to_pay);

            $("#start_date").val(date_start);

            $("#end_date").val(date_to);

            $("#notes").val(notes);

            $("#add_edit_btn").val(file_id);

            $("#loan_id").val(loan_id);

            $("#add_edit_tbl").show("fast");

            $("#delete_btn").hide("fast");

            $("#delete_btn").val('0');

            $("#cancel_btn").hide("fast");

            $("#loan_div").hide("fast");

            add_edit_tbl(loan_id);

        });

        $("#delete_btn").on("click", function(){

            var file_id = $("#delete_btn").val();

            var loan_id = $("#loan_id").val();

            $.confirm({

                                title: 'Delete',

                                content: 'Delete Loan Info',

                                escapeKey: 'cancelAction',

                                buttons: {

                                    confirm: {

                                        btnClass: 'btn-green',

                                        text: 'Yes',

                                        action: function(){

                                            $.ajax({

                                                url: "{{route('delete_loan_employee')}}",

                                                data: {

                                                    loan_id : loan_id,

                                                    file_id : file_id,

                                                  

                                                    _token : "{{csrf_token()}}", 

                                                },

                                                    success: function (data) { 

                                                        $.notify(data, {type:"info",icon:"info"});  

                                                            

                                                            $("#add_edit_tbl").show("fast");

                                                            $("#delete_btn").hide("fast");

                                                            $("#delete_btn").val('0');

                                                            $("#cancel_btn").hide("fast");

                                                            $("#loan_div").hide("fast");

                                                            employee_list(loan_id, "0");

                                                            

                                                            $("#payment_type").val("0").change();

                                                            $("#principal_amount").val("");

                                                            $("#deduction_amount").val("");

                                                            $("#start_date").val("");

                                                            $("#end_date").val("");

                                                            $("#notes").val("");

                                                            $("#add_edit_btn").val("new");

                                                            $("#loan_id").val(loan_id);

                                                            add_edit_tbl(loan_id);

                                                            return;

                                                      

                                                        

                                                    },

                                                    dataType: 'json',

                                                    method: 'POST'

                                                });

                                        }

                                        

                                    },

                                    cancelAction: {

                                        btnClass: 'btn-gray',

                                        text: 'No',

                                        action: function(){

                                        

                                        }  

                                    }

                                }

                            });

        });

        $("#cancel_btn").on("click", function(){

            var loan_id = $("#loan_id").val();

            add_edit_tbl(loan_id);

            $("#add_edit_tbl").show("fast");

            $("#delete_btn").hide("fast");

            $("#delete_btn").val('0');

            $("#cancel_btn").hide("fast");

            employee_list(loan_id, "0");

            $("#loan_div").hide("fast");

            $("#payment_type").val("0").change();

            $("#principal_amount").val("");

            $("#deduction_amount").val("");

            $("#start_date").val("");

            $("#end_date").val("");

            $("#notes").val("");

            $("#add_edit_btn").val("new");

            $("#loan_id").val(loan_id);

            add_edit_tbl(loan_id);

        });

        $("#payment_type").on("change", function(){

            if($(this).val() == "PARTIAL"){

                $("#variance").show("fast");

            }else{

                $("#variance").hide("fast");

            }

        });

        $("#add_edit_btn").on("click", function(){

            var loan_id = $("#loan_id").val();

            var emp_id = $("#selected_employee").val();

            var payment_type = $("#payment_type").val();

            var payment_variance = $("#payment_variance").val();

            

            var principal_amount = $("#principal_amount").val();

            var deduction_amount = $("#deduction_amount").val();

            var balance_amount = $("#balance_amount").val();

            var start_date = $("#start_date").val();

            var end_date = $("#end_date").val();

            var notes = $("#notes").val();

            var add_edit_btn = $("#add_edit_btn").val();

            

            var loan_status = $("#loan_status").val();

            if(emp_id == "0"){$.notify("Please Select Employee", {type:"danger",icon:"close"}); return; }

            if(payment_type == "0"){$.notify("Please Select Payment Mode ", {type:"danger",icon:"close"}); return; }

            if(principal_amount == "0" || principal_amount == ""){$.notify("Please Input Principal Amount", {type:"danger",icon:"close"}); return; }

            if(deduction_amount == ""){$.notify("Please Input Deduction Amount", {type:"danger",icon:"close"}); return; }

            if(start_date == ""){$.notify("Please Input Start Date", {type:"danger",icon:"close"}); return; }

            if(end_date == ""){$.notify("Please Input End Date", {type:"danger",icon:"close"}); return; }

            

            

                    $.confirm({

                                title: 'Save',

                                content: 'Save Loan Info',

                                escapeKey: 'cancelAction',

                                buttons: {

                                    confirm: {

                                        btnClass: 'btn-green',

                                        text: 'Yes',

                                        action: function(){

                                            $.ajax({

                                                url: "{{route('save_loan_employee')}}",

                                                data: {

                                                    loan_id : loan_id,

                                                    emp_id : emp_id,

                                                    payment_type : payment_type,

                                                    principal_amount : principal_amount,

                                                    deduction_amount : deduction_amount,

                                                    balance_amount:balance_amount,

                                                    payment_variance: payment_variance,

                                                    start_date : start_date,

                                                    end_date : end_date,

                                                    loan_status: loan_status,

                                                    notes : notes,

                                                    id : add_edit_btn,

                                                    _token : "{{csrf_token()}}", 

                                                },

                                                    success: function (data) { 

                                                        if(data == "true"){

                                                            $.notify("Success", {type:"success",icon:"check"}); 

                                                            add_edit_tbl(loan_id);

                                                            $("#add_edit_tbl").show("fast");

                                                            $("#delete_btn").hide("fast");

                                                            $("#delete_btn").val('0');

                                                            $("#cancel_btn").hide("fast");

                                                            $("#loan_div").hide("fast");

                                                            employee_list(loan_id, "0");

                                                            

                                                            $("#payment_type").val("0").change();

                                                            $("#principal_amount").val("");

                                                            $("#deduction_amount").val("");

                                                            $("#balance_amount").val("");

                                                            

                                                            $("#start_date").val("");

                                                            $("#end_date").val("");

                                                            $("#notes").val("");

                                                            $("#add_edit_btn").val("new");

                                                            $("#loan_id").val(loan_id);

                                                            add_edit_tbl(loan_id);

                                                            return;

                                                        }else{

                                                            $.notify("Failed", {type:"danger",icon:"close"}); return; 

                                                        }

                                                        

                                                    },

                                                    dataType: 'json',

                                                    method: 'POST'

                                                });

                                        }

                                        

                                    },

                                    cancelAction: {

                                        btnClass: 'btn-gray',

                                        text: 'No',

                                        action: function(){

                                        

                                        }  

                                    }

                                }

                            });

            

        });

        function view_emp_data(emp_id){

            $.ajax({

            url: "{{route('view_employee_data')}}",

            data: {

                _token : "{{csrf_token()}}", 

                emp_id: emp_id

                

            },

                success: function (data) { 

                    $("#selected_employee").empty();

                    $.each(data, function( index, value ) {

                        if(value.ext_name == null){

                            value.ext_name = "";

                        }

                        $("#selected_employee").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")

                            

                    });

                        $("#selected_employee").val(emp_id).change();

                },

                dataType: 'json',

                method: 'POST'

            });

        }

        

        function employee_list(loan_id,emp_id){

            // update loan

            var user_type = $("#user_type").val();
            var role_id = "{{ Auth::user()->role_id }}";

            $.ajax({

            url: "{{route('employee_loan_array')}}",

            data: {

                _token : "{{csrf_token()}}", 

                loan_id: loan_id,

                

            },

                success: function (data) { 

                    $("#selected_employee").empty().append("<option value='0'>Select Employee</option>");

                    $.each(data, function( index, value ) {

                        if(value.ext_name == null){

                            value.ext_name = "";

                        }

                        // update loan

                        if(user_type == "employee" && role_id != "11"){

                            $("#selected_employee").append("<option value='"+value.id+"' selected>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")

                            $("#selected_employee").prop("disabled", true);

                        }else{

                            $("#selected_employee").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")

                        }

                        

                    });

                        // update loan

                        if(user_type !== "employee" || role_id == "11"){

                            $("#selected_employee").val(emp_id).change();

                        }

                },

                dataType: 'json',

                method: 'POST'

            });

        }

        $('#loan_lib_modal').on('show.bs.modal', function(e) {

            

                var id = $(e.relatedTarget).data('id');

                var type = $(e.relatedTarget).data('type');

                var code = $(e.relatedTarget).data('code');

                var name = $(e.relatedTarget).data('name');

                var description = $(e.relatedTarget).data('description');

                var is_active = $(e.relatedTarget).data('is_active');

                var is_regular = $(e.relatedTarget).data('is_regular');

                

                $("#save_library").val(id);

                $("#loan_type").val(type).change();

                $("#is_regular").val(is_regular).change();

                

                $("#lib_code").val(code);

                $("#lib_name").val(name);

                $("#lib_desc").val(description);

                $("#lib_is_active").val(is_active).change();

            });

        $("#save_library").on("click", function(){

            var id = $("#save_library").val();

            var loan_type = $("#loan_type").val();

            var lib_code = $("#lib_code").val();

            var lib_name = $("#lib_name").val();

            var lib_desc = $("#lib_desc").val();

            var lib_is_active = $("#lib_is_active").val();

            var is_regular = $("#is_regular").val();

            if(lib_code == ""){ $.notify("Code is Required", {type:"danger",icon:"close"}); return; }

            if(lib_name == ""){ $.notify("Name is Required", {type:"danger",icon:"close"}); return; }

            if(lib_desc == ""){ $.notify("Description is Required", {type:"danger",icon:"close"}); return; }

            $.confirm({

                                title: 'Save',

                                content: 'Save Loan Data',

                                escapeKey: 'cancelAction',

                                buttons: {

                                    confirm: {

                                        btnClass: 'btn-green',

                                        text: 'Yes',

                                        action: function(){

                                            $.ajax({

                                                url: "{{route('save_loan_library')}}",

                                                data: {

                                                    id : id,

                                                    loan_type : loan_type,

                                                    lib_code : lib_code,

                                                    lib_name : lib_name,

                                                    lib_desc : lib_desc,

                                                    lib_is_active : lib_is_active,

                                                    is_regular: is_regular,

                                                    _token : "{{csrf_token()}}", 

                                                },

                                                    success: function (data) { 

                                                        if(data == "duplicate"){

                                                                $.notify("Code Already Exist", {type:"info",icon:"info"}); return;

                                                        }else if(data == "true"){

                                                            $.notify("Success", {type:"success",icon:"check"}); 

                                                            loan_library_list();

                                                            $('#loan_lib_modal').modal('hide');

                                                            return;

                                                        }else{

                                                            $.notify("Failed", {type:"danger",icon:"close"}); return; 

                                                        }

                                                        

                                                    },

                                                    dataType: 'json',

                                                    method: 'POST'

                                                });

                                        }

                                        

                                    },

                                    cancelAction: {

                                        btnClass: 'btn-gray',

                                        text: 'No',

                                        action: function(){

                                        

                                        }  

                                    }

                                }

                            });

        });

        function add_edit_tbl(loan_id){

            $('#add_edit_tbl').DataTable({

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

                        "url": "{{ route('loan_files') }}",

                        "dataType": "json",

                        "type": "POST",

                        "data":{

                            "_token": "{{ csrf_token() }}", 

                            "page": "{{Route::current()->action['as']}}", 

                            loan_id:loan_id

                        }

                    },

                    "columns":[

                        {'data': 'emp_name'},

                        {'data': 'total_amount'},

                        {'data': 'amount_to_pay'},

                        {'data': 'balance'},

                        {'data': 'payment_type'},

                      //  {'data': 'dates'},

                        {'data': 'notes'},

                        {'data': 'loan_status'},

                        {'data': 'action', 'orderable': false, 'searchable': false},

                    ]

                });

        }

        // update loan

        function emp_tbl(emp_id){

            $('#emp_tbl').DataTable({

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

                        "url": "{{ route('employee_loan_files') }}",

                        "dataType": "json",

                        "type": "POST",

                        "data":{

                            "_token": "{{ csrf_token() }}", 

                            emp_id:emp_id

                        }

                    },

                    "columns":[

                        {'data': 'emp_name'},

                        {'data': 'total_amount'},

                        {'data': 'amount_to_pay'},

                        {'data': 'balance'},

                        {'data': 'payment_type'},

                        // {'data': 'dates'},

                        {'data': 'notes'},

                        {'data': 'loan_status'}

                    ]

                });

        }

        function loan_library_list(){

                    $('#loan_tbl').DataTable({

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

                        "url": "{{ route('loan_library_list') }}",

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

                        {'data': 'type'},

                        {'data': 'description'},

                        {'data': 'is_active'},

                        {'data': 'action', 'orderable': false, 'searchable': false},

                    ]

                });

			}

    // add delete loan library

	function delete_loan_library(id){

		$.confirm({

			title: 'Loan Library',

			content: 'Delete this loan library?',

			escapeKey: 'cancelAction',

			buttons: {

				confirm: {

					btnClass: 'btn-danger',

					text: "Yes",

					action: function(){

						HoldOn.open(holdon_option);

						$.ajax({

								url: "{{route('delete_loan_library')}}",

							data: {

								_token : "{{csrf_token()}}", 

								id: id,

							},

								success: function (data) { 

									

									$.notify(data, {type:"info",icon:"info"}); 

									loan_library_list();

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

    // add delete in loan files

	function delete_loan_file(id,loan_id){

		$.confirm({

			title: 'Loan File',

			content: 'Delete this filed loan?',

			escapeKey: 'cancelAction',

			buttons: {

				confirm: {

					btnClass: 'btn-danger',

					text: "Yes",

					action: function(){

						HoldOn.open(holdon_option);

						$.ajax({

								url: "{{route('delete_loan_file')}}",

							data: {

								_token : "{{csrf_token()}}", 

								id: id,

							},

								success: function (data) { 

									

									$.notify(data, {type:"info",icon:"info"}); 

									add_edit_tbl(loan_id);

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

