@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Other Income
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
            <div class="col-xl-12 col-sm-12 col-12">
                @if(Auth::user()->company['version'] == 1)
                
                @endif
            </div>
            <div class="col-xl-12 col-sm-12 col-12 mb-2">
                <div class="head-link-set">
                    <ul>
                        {{-- <li><a class="menu_tab active"  onclick="" id="oth_income_menu">Employee Other Income</a></li> --}}
                        {{-- <li><a class="menu_tab" id="library_menu"  onclick="">Library</a></li>
                         --}}
                        
                        
                        
                    </ul>
                </div>
            </div>
        
            @include("other_income.library")
            
        </div>
       
    </div>
</div>
 
@endif
@stop
@section("scripts")
<script>
$('#add_edit_employee').on('show.bs.modal', function(e) {
                //DEFAULT 
                $(".btn-group .btn").attr("class", "btn btn-outline-primary ml-1");
                $(".select_employee").removeAttr("checked");
                // $(".select_employee").removeAttr("checked");
                var id = $(e.relatedTarget).data('id');
                var code = $(e.relatedTarget).data('code');
                var name = $(e.relatedTarget).data('name');
                $("#save_oth_income").val(id);
                $("#label_modal").empty().text("("+code+") " +name);
                $("#income_type").val("0").change();
                $("#income_file tbody").empty();
                $("#amount_div").hide("fast");
                $("#amount_div2").hide("fast");
                $("#amount_div3").hide("fast");
                $("#amount_div4").hide("fast");
                $("#amount_div5").hide("fast");
            $.ajax({
                url: "{{route('employee_array')}}",
                data: {
                    _token : "{{csrf_token()}}", 
                
                },
                    success: function (data) { 
                        $("#selected_employee").empty();
                        $.each(data, function( index, value ) {
                            if(value.ext_name == null){
                                value.ext_name = "";
                            }
                            $("#selected_employee").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                            
                            });
                        
//STOPPPPED HERE FOR RETRIEVE OF VALUE
                            $.ajax({
                                    url: "{{route('emp_other_income_data')}}",
                                    data: {
                                        _token : "{{csrf_token()}}", 
                                        id:id
                                    
                                    },
                                        success: function (source) { 
                                            
                                            if(source.length > 0){
                                                
                                                $("#"+source[0]["selected_emp"]+"_label").attr("class","btn btn-outline-primary ml-1 active");
                                                $( "#"+source[0]["selected_emp"]).prop('checked', true);
                                                // $("[name=select_employee]").val(data[0]["selected_emp"]);
                                                if(source[0]["selected_emp"] == "custom_emp"){
                                                    $("#selected_div").show("fast");
                                                    $("#custom_tbl").show("fast");
                                                    $("#include").show("fast");
                                                    $.each(source, function( index, value ) {
                                                        var action = "<button onclick='delete_row("+value.emp_id+")' class='btn btn-warning tbl_emp' id='emp_id_"+value.emp_id+"' value='"+value.emp_id+"' > Delete</button>";
                                                        var emp_name =   $("#selected_employee option[value="+value.emp_id+"]").text();
                                                        var insert = "<tr id='tr_"+value.emp_id+"'>";
                                                            insert = insert + "<td id='td_name_"+value.emp_id+"'>"+emp_name+"</td>";
                                                            
                                                            insert = insert + "<td id='td_type_"+value.emp_id+"'>"+value.income_type+"</td>";
                                                            var amount_data = "";
                                                            if(value.income_type == "DAILY"){
                                                                amount_data = amount_data + "<label class=''>Amount: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount_"+value.emp_id+"'>"+value.amount+"</label>";
                                                            }else if(value.income_type == "MONTHLY"){
                                                                amount_data = amount_data + "<label class=''>Amount: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount_"+value.emp_id+"'>"+value.amount+"</label>";
                                                            }else if(value.income_type == "SEMI"){
                                                                amount_data = amount_data + "<label class=''>1st Half: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount_"+value.emp_id+"'>"+value.amount+"</label> <br>";
                                                                amount_data = amount_data + "<label class=''>2nd half: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount2_"+value.emp_id+"'>"+value.amount_2+"</label>";
                                                            }else if(value.income_type == "WEEKLY"){
                                                                amount_data = amount_data + "<label class=''>1st Week: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount_"+value.emp_id+"'>"+value.amount+"</label> <br>";
                                                                amount_data = amount_data + "<label class=''>2nd Week: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount2_"+value.emp_id+"'>"+value.amount_2+"</label> <br>";
                                                                amount_data = amount_data + "<label class=''>3rd Week: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount3_"+value.emp_id+"'>"+value.amount_3+"</label> <br>";
                                                                amount_data = amount_data + "<label class=''>4th Week: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount4_"+value.emp_id+"'>"+value.amount_4+"</label> <br>";
                                                                amount_data = amount_data + "<label class=''>5th Week: </label>";
                                                                amount_data = amount_data + "<label class='' id='label_amount5_"+value.emp_id+"'>"+value.amount_5+"</label> <br>";
                                                            }
                                                            insert = insert + "<td id='td_amount_"+value.emp_id+"'>"+amount_data+"</td>";
                                                            insert = insert + "<td>" +action + "</td>";
                                                            insert = insert + "</tr>";
                                                            
                                                            // alert(insert);
                                                        $("#income_file tbody").append(insert);
                                                        $("#selected_employee").find('[value="'+value.emp_id+'"]').remove();
                                                    });
                                                }else{
                                                    $("#selected_div").hide("fast");
                                                    $("#custom_tbl").hide("fast");
                                                    $("#include").hide("fast");
                                                    $.each(source, function( index, value ) {
                                                        $("#income_type").val(value.income_type).change();
                                                        $("#amount_val").val(source[0]["amount"]);
                                                        $("#amount_val_2").val(source[0]["amount_2"]);
                                                        $("#amount_val_3").val(source[0]["amount_3"]);
                                                        $("#amount_val_4").val(source[0]["amount_4"]);
                                                        $("#amount_val_5").val(source[0]["amount_5"]);
                                                        
                                                    });
                                                }
                                            }else{
                                                //set checked
                                                $("#all_emp_label").attr("class","btn btn-outline-primary ml-1 active")
                                                $( "#all_emp").prop('checked', true);
                                                $("#selected_div").hide("fast");
                                                $("#custom_tbl").hide("fast");
                                                $("#include").hide("fast");
                                            }
                                              
                                        },
                                        dataType: 'json',
                                        method: 'POST'
                                    });
                    },
                    dataType: 'json',
                    method: 'POST'
                });
               
             
            });
    $("#save_oth_income").on("click", function(){
        var other_income_id = $("#save_oth_income").val();
        var select_emp = $("input[name='select_employee']:checked").val();
    
        var amount = $("#amount_val").val();
        var amount_2 = $("#amount_val_2").val();
        var amount_3 = $("#amount_val_3").val();
        var amount_4 = $("#amount_val_4").val();
        var amount_5 = $("#amount_val_5").val();
        
        var income_type = $("#income_type").val();
        var delimited = "";
        
        if(select_emp == "custom_emp"){
            
            $(".tbl_emp").each(function () {
                var indicator = $(this).val();
                    if(delimited != ""){
                        delimited = delimited + "|";
                    }
                    var label_amount = "";
                    delimited = delimited + indicator + ";" + $("#td_type_"+indicator).text(); 
                    var td_itype =  $("#td_type_"+indicator).text();
                    if(td_itype == "DAILY"){
                         label_amount = label_amount +  ";"+$("#label_amount_"+indicator).text();
                    }else if(td_itype == "MONTHLY"){
                         label_amount = label_amount + ";"+$("#label_amount_"+indicator).text();
                    }else if(td_itype == "SEMI"){
                         label_amount = label_amount +";"+$("#label_amount_"+indicator).text();
                         label_amount = label_amount +";"+$("#label_amount2_"+indicator).text();
                    }else if(td_itype == "WEEKLY"){
                        label_amount = label_amount +";"+$("#label_amount_"+indicator).text();
                        label_amount = label_amount +";"+$("#label_amount2_"+indicator).text();
                        label_amount = label_amount +";"+$("#label_amount3_"+indicator).text();
                        label_amount = label_amount +";"+$("#label_amount4_"+indicator).text();
                        label_amount = label_amount +";"+$("#label_amount5_"+indicator).text();
                    }
                    delimited = delimited + label_amount;
                   
                });
        }else{
            if(amount == "0" || amount == ""){
                $.notify("Amount Cannot be Empty", {type:"info",icon:"info"}); 
                return;
            }
            if(income_type == "0"){
                $.notify("Select Income Type", {type:"info",icon:"info"}); 
                return;
            }
        }
        $.confirm({
							title: 'Save',
							content: 'Add Other Income to employee?',
							escapeKey: 'cancelAction',
							buttons: {
								confirm: {
									btnClass: 'btn-green',
									text: 'Yes',
									action: function(){
                                     
                                        $.ajax({
                                        url: "{{route('save_other_income')}}",
                                        data: {
                                            _token : "{{csrf_token()}}", 
                                            other_income_id: other_income_id,
                                            select_emp: select_emp,
                                            delimited: delimited,
                                            income_type: income_type,
                                            amount: amount,
                                            amount_2: amount_2,
                                            amount_3: amount_3,
                                            amount_4: amount_4,
                                            amount_5: amount_5
                                        },
                                            success: function (data) { 
                                                if(data == "success"){
                                                    $.notify("Success Process", {type:"success",icon:"check"}); 
                                                    $('#add_edit_employee').modal('hide');
                                                }else{
                                                     $.notify("Failed Saving", {type:"danger",icon:"close"}); 
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
    $("input[name='select_employee']").on("click",function(){
        if($(this).attr("id") == "custom_emp"){
            $("#selected_div").show("fast");
            $("#custom_tbl").show("fast");
            $("#include").show("fast");
            
        }else{
            $("#selected_div").hide("fast");
            $("#custom_tbl").hide("fast");
            $("#include").hide("fast");
        }
    });
        $("#oth_income_menu").on("click",function(){
            show_tab("oth_library");
        });
        $(".menu_tab").on("click",function(){
            $(".menu_tab").attr("class", "menu_tab");
            $(this).attr("class", "menu_tab active");
        });
    
        function show_tab(id){
            $(".front_tab").hide("fast");
            $("#"+id).show("fast");
        }
        $( document ).ready(function() {
            show_tab("oth_library");
			var role_id = "{{ Auth::user()->role_id }}";
            // $( "#date_filed" ).datepicker({dateFormat: 'yy-mm-dd' }); 
            $("#status").empty();
            $("#status").append('<option value="">SELECT STATUS HERE</option>');
            $("#status").append('<option value="FILED">FILED</option>');
            if(role_id == "11"){
                $("#status").append('<option value="1st_Approved">APPROVED</option>');
                $("#status").append('<option value="REJECT">REJECT</option>');
            }else if(role_id == "15" || role_id == "1"){
                $("#status").append('<option value="APPROVED">APPROVED</option>');
                $("#status").append('<option value="REJECT">REJECT</option>');
            }
            $(".form-select").select2();
            $("#selected_div").hide("fast");
            $("#custom_tbl").hide("fast");
            $("#include").hide("fast");
            $('#income_file').DataTable({
                    "bDestroy": true,
                    "autoWidth": true,
                    "searchHighlight": true,
                    "searching": true,
                    "orderMulti": true,
                    "pageLength": 10,
                });
            
            oth_library_list();
            allowance_request_tbl();
        });
        $("#income_type").on("change", function(){
            var income_type = $("#income_type").val();
            $("#amount_val").val("0");
            $("#amount_val_2").val("0");
            $("#amount_val_3").val("0");
            $("#amount_val_4").val("0");
            $("#amount_val_5").val("0");
            if(income_type == "DAILY" || income_type == "MONTHLY"){
                $("#amount_label_1").empty().text("Amount");
                $("#amount_div").show("fast");
                $("#amount_div2").hide("fast");
                $("#amount_div3").hide("fast");
                $("#amount_div4").hide("fast");
                $("#amount_div5").hide("fast");
                
            }else if(income_type == "SEMI"){
                $("#amount_label_1").empty().text("First Half");
                $("#amount_label_2").empty().text("Second Half");
               
                $("#amount_div").show("fast");
                $("#amount_div2").show("fast");
                $("#amount_div3").hide("fast");
                $("#amount_div4").hide("fast");
                $("#amount_div5").hide("fast");
            }else if(income_type == "WEEKLY"){
                $("#amount_label_1").empty().text("Week 1 Amount");
                $("#amount_label_2").empty().text("Week 2 Amount");
               
                $("#amount_div").show("fast");
                $("#amount_div2").show("fast");
                $("#amount_div3").show("fast");
                $("#amount_div4").show("fast");
                $("#amount_div5").show("fast");
            }else{
                $("#amount_div").hide("fast");
                $("#amount_div2").hide("fast");
                $("#amount_div3").hide("fast");
                $("#amount_div4").hide("fast");
                $("#amount_div5").hide("fast");
            }
        });
        function delete_row(tr_id){
            var emp_name = $("#td_name_"+tr_id).text();
          
            $("#tr_"+tr_id).remove();
            $("#selected_employee").append("<option value='"+tr_id+"'>"+emp_name+"</option>");
        }
        
        $("#include").on("click", function(){
            var emp_id = $("#selected_employee").val();
            // alert(emp_id);
            // return;
            var amount = $("#amount_val").val();
            var amount_2 = $("#amount_val_2").val();
            var amount_3 = $("#amount_val_3").val();
            var amount_4 = $("#amount_val_4").val();
            var amount_5 = $("#amount_val_5").val();
            var income_type = $("#income_type").val();
        
        //   val = emp_name =   $("#selected_employee option[value="+emp_id+"]").text();
            if(emp_id == ""){
                $.notify("Employee Required", {type:"info",icon:"info"}); 
                return;
            }
            if(amount == "0"){
                $.notify("Amount Cannot be Empty", {type:"info",icon:"info"}); 
                return;
            }
            if(income_type == "0"){
                $.notify("Select Income Type", {type:"info",icon:"info"}); 
                return;
            }
            included = emp_id.toString().split(",");
            // alert(included.length);
            for(var i  = 0; i < included.length; i++)
            {
                var action = "<button onclick='delete_row("+included[i]+")' class='btn btn-warning tbl_emp' id='emp_id_"+included[i]+"' value='"+included[i]+"' > Delete</button>";
                var  emp_name =   $("#selected_employee option[value="+included[i]+"]").text();
                var insert = "<tr id='tr_"+included[i]+"'>";
                    insert = insert + "<td id='td_name_"+included[i]+"'>"+emp_name+"</td>";
                    var amount_data = "";
                    if(income_type == "DAILY"){
                        amount_data = amount_data + "<label class=''>Amount: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount_"+included[i]+"'>"+amount+"</label>";
                    }else if(income_type == "MONTHLY"){
                        amount_data = amount_data + "<label class=''>Amount: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount_"+included[i]+"'>"+amount+"</label>";
                    }else if(income_type == "SEMI"){
                        amount_data = amount_data + "<label class=''>1st Half: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount_"+included[i]+"'>"+amount+"</label> <br>";
                        amount_data = amount_data + "<label class=''>2nd half: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount2_"+included[i]+"'>"+amount_2+"</label>";
                    }else if(income_type == "WEEKLY"){
                        amount_data = amount_data + "<label class=''>1st Week: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount_"+included[i]+"'>"+amount+"</label> <br>";
                        amount_data = amount_data + "<label class=''>2nd Week: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount2_"+included[i]+"'>"+amount_2+"</label> <br>";
                        amount_data = amount_data + "<label class=''>3rd Week: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount3_"+included[i]+"'>"+amount_3+"</label> <br>";
                        amount_data = amount_data + "<label class=''>4th Week: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount4_"+included[i]+"'>"+amount_4+"</label> <br>";
                        amount_data = amount_data + "<label class=''>5th Week: </label>";
                        amount_data = amount_data + "<label class='' id='label_amount5_"+included[i]+"'>"+amount_5+"</label> <br>";
                    }
                    insert = insert + "<td id='td_type_"+included[i]+"'>"+income_type+"</td>";
                    insert = insert + "<td id='td_amount_"+included[i]+"'>"+amount_data+"</td>";
                    insert = insert + "<td>" +action + "</td>";
                    insert = insert + "</tr>";
                    
                    // alert(insert);
                $("#income_file tbody").append(insert);
                $("#selected_employee").find('[value="'+included[i]+'"]').remove();
            }
           
          
            // $("#amount_val").val("0");
        });
        
     
        function oth_library_list(){
                    $('#oth_library_tbl').DataTable({
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
                        "url": "{{ route('oth_library_list') }}",
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
                        {'data': 'is_regular'},
                        
                        {'data': 'tax_type'},
                        // {'data': 'tax_item'},
                        {'data': 'is_active'},
                        {'data': 'action', 'orderable': false, 'searchable': false},
                    ]
                });
			}
            function allowance_request_tbl(){
                    $('#allowance_request_tbl').DataTable({
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
                        "url": "{{ route('allowance_request_tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}", 
                            "page": "{{Route::current()->action['as']}}"  
                            
                        }
                    },
                    "columns":[
                        {'data': 'name'},
                        {'data': 'amount'},
                        {'data': 'date_filed'},
                        {'data': 'remarks'},
                        {'data': 'status'},
                        {'data': 'action', 'orderable': false, 'searchable': false},
                    ]
                });
			}
            $('#oth_library_modal').on('show.bs.modal', function(e) {
                $("#tax_item_TAX").hide("fast");
                $("#tax_item_NON").hide("fast");
                var id = $(e.relatedTarget).data('id');
                var code = $(e.relatedTarget).data('code');
                var name = $(e.relatedTarget).data('name');
                var description = $(e.relatedTarget).data('description');
                var is_regular = $(e.relatedTarget).data('is_regular');
                
                var tax_type = $(e.relatedTarget).data('tax_type');
                var tax_item = $(e.relatedTarget).data('tax_item');
                
                
                var is_active = $(e.relatedTarget).data('is_active');
                $("#save_library").val(id);
                $("#lib_code").val(code);
                $("#lib_name").val(name);
                $("#lib_desc").val(description);
                $("#lib_is_regular").val(is_regular).change();
                
                $("#lib_tax_type").val(tax_type).change();
                $("#tax_item_"+tax_type).show("fast");
                $("#lib_tax_item_"+tax_type).val(tax_item).change();
   
                $("#lib_is_active").val(is_active).change();
            });
            $('#allowance_request_modal').on('show.bs.modal', function(e) {
                $('#allowance_emp_name').select2({ dropdownParent: $('#allowance_request_modal') });
                var id = $(e.relatedTarget).data('id');
                var emp_id = $(e.relatedTarget).data('emp_id');
                var amount = $(e.relatedTarget).data('amount');
                var remarks = $(e.relatedTarget).data('remarks');
                var status = $(e.relatedTarget).data('status');
                var date_filed = $(e.relatedTarget).data('date_filed');
                $("#save_allowance_request").val(id);
                $("#amount").val(amount);
                $("#status").val(status).change();
                $("#date_filed").val(date_filed);
                $("#remarks").val(remarks);

                $.ajax({
                    url: "{{route('get_employee_list')}}",
                    data: {
                    _token : "{{csrf_token()}}", 
                    page: "{{Route::current()->action['as']}}"
                    },
                        success: function (data) { 
                            $("#allowance_emp_name").empty().append("<option value='0'>Select Employee </option> ");
                            $.each(data, function( index, value ) {
                                    if(value.ext_name == null){
                                        value.ext_name = "";
                                    }
                                    $("#allowance_emp_name").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                                    
                                    });
                                    $("#allowance_emp_name").val(emp_id).change();
                        },
                        dataType: 'json',
                        method: 'POST'
                    });
            });
            $("#save_allowance_request").on("click", function(){
                var save_allowance_request =  $("#save_allowance_request").val();
                var allowance_emp_name =  $("#allowance_emp_name").val();
                var amount =  $("#amount").val();
                var status = $("#status").val();
                var date_filed = $("#date_filed").val();
                var remarks = $("#remarks").val();
                
                if(allowance_emp_name == "0"){
                    $.notify("Please select an employee", {type:"danger",icon:"info"}); 
                    return;
                }
                if(amount == ""){
                    $.notify("Please enter amount", {type:"danger",icon:"info"}); 
                    return;
                }
                if(date_filed == ""){
                    $.notify("Please select date filed", {type:"danger",icon:"info"}); 
                    return;
                }
                if(status == ""){
                    $.notify("Please select status", {type:"danger",icon:"info"}); 
                    return;
                }
                $.confirm({
                                title: 'Submit',
                                content: 'Allowance Request',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: status,
                                        action: function(){
                                            HoldOn.open(holdon_option);
                                            $.ajax({
                                                    url: "{{route('save_allowance_request')}}",
                                                data: {
                                                    _token : "{{csrf_token()}}", 
                                                    id: save_allowance_request,
                                                    allowance_emp_name: allowance_emp_name,
                                                    amount: amount,
                                                    status: status,
                                                    date_filed: date_filed,
                                                    remarks: remarks
                                                },
                                                    success: function (data) { 
                                                        if(data == "Filing Success"){
                                                            $.notify(data, {type:"info",icon:"info"}); 
                                                            $('#allowance_request_modal').modal("hide");
                                                            allowance_request_tbl();
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
            $("#lib_tax_type").on("change", function(){
                $("#tax_item_TAX").hide("fast");
                $("#tax_item_NON").hide("fast");
                var tax_type = $("#lib_tax_type").val();
                $("#tax_item_"+tax_type).show("fast");
                $("#lib_tax_item_"+tax_type).val(0).change();
            });
            $("#save_library").on("click", function(){
                    var save_library =  $("#save_library").val();
                    var lib_code =  $("#lib_code").val();
                    var lib_name =  $("#lib_name").val();
                    var lib_desc =  $("#lib_desc").val();
                    var lib_is_regular =  $("#lib_is_regular").val();
                    
                    var tax_type =  $("#lib_tax_type").val();
                    var tax_item_non =  $("#lib_tax_item_NON").val();
                    var tax_item_tax =  $("#lib_tax_item_TAX").val();
                    var is_active =  $("#lib_is_active").val();
                    if(lib_code == ""){ $.notify("Code is Required", {type:"danger",icon:"close"}); return; }
                    if(lib_name == ""){ $.notify("Name is Required", {type:"danger",icon:"close"}); return; }
                    $.confirm({
                                title: 'Save',
                                content: 'Save Other Income Data',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('save_library')}}",
                                                data: {
                                                    save_library : save_library,
                                                    lib_code : lib_code,
                                                    lib_name : lib_name,
                                                    lib_desc: lib_desc,
                                                    lib_is_regular: lib_is_regular,
                                                    tax_type: tax_type,
                                                    tax_item_non: tax_item_non,
                                                    tax_item_tax: tax_item_tax,
                                                    is_active : is_active,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        if(data == "duplicate"){
                                                                $.notify("Code Already Exist", {type:"info",icon:"info"}); return;
                                                        }else if(data == "true"){
                                                            $.notify("Success", {type:"success",icon:"check"}); oth_library_list(); 
                                                            $('#oth_library_modal').modal('hide');
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
      // add delete in income
	function delete_income(id){
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
								url: "{{route('delete_income')}}",
							data: {
								_token : "{{csrf_token()}}", 
								id: id,
							},
								success: function (data) { 
									
									$.notify(data, {type:"info",icon:"info"}); 
									oth_library_list();
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
     //delete allowance request
    function delete_allowance(id){
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
								url: "{{route('delete_allowance')}}",
							data: {
								_token : "{{csrf_token()}}", 
								id: id,
							},
								success: function (data) { 
									
									$.notify(data, {type:"info",icon:"info"}); 
									allowance_request_tbl();
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
