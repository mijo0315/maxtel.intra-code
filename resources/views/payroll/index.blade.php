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
            
            @include("payroll.create")
            @include("payroll.tag_employee")
            @include("payroll.manual_timekeeping")
            
            @include("payroll.payroll_inc_modal")
            @include("payroll.payroll_ded_modal")
            @include("payroll.uploadOtherIncomeModal")
            @include("payroll.uploadDeductionModal")
            @include("payroll.credit_adjustmentModal")
            
        </div>
       
    </div>
</div>
<div id="payroll_loading_overlay" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.65);
    z-index:99999;
    align-items:center;
    justify-content:center;
">
    <div style="
        background:#fff;
        padding:30px 40px;
        border-radius:12px;
        text-align:center;
        min-width:300px;
        box-shadow:0 5px 20px rgba(0,0,0,0.3);
    ">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>

        <h5 id="payroll_loading_text" style="font-weight:bold;color:#222;">
            Processing...
        </h5>

        <small style="color:#666;">
            Please wait. Do not refresh or close this page.
        </small>
    </div>
</div>
 
@endif
@stop
@section("scripts")
<script>
    $( document ).ready(function() {
        var role_id = "{{ Auth::user()->role_id }}";
        $("#hr_group option").prop("disabled", true);
        var roleGroupMap = {
            14: ["group_b"],
            4:  ["group_d"],
            5:  ["group_c", "group_e"]
        };
        if (roleGroupMap[role_id]) {
            roleGroupMap[role_id].forEach(function (group) {
                $("#hr_group option[value='" + group + "']").prop("disabled", false);
            });
            $("#hr_group").val(roleGroupMap[role_id][0]);
        }
        if (role_id == 1) {
            $("#hr_group option").prop("disabled", false);
        }
       
       $(".form-select").select2({
           allowClear: true,
           width: '100%'
       });
       
       // Comprehensive fix for Select2 inside Bootstrap modal
       // Allow all keyboard input to Select2 search field
       $(document).on('keydown', '.select2-search__field', function(e) {
           e.stopPropagation();
       });
       
       // Allow keyboard input and prevent modal from blocking Select2
       $.fn.modal.Constructor.prototype._enforceFocus = function() {};
       
       // Auto-focus search field when Select2 dropdown opens
       $(document).on('select2:open', function() {
           setTimeout(function() {
               $('.select2-search__field').focus();
           }, 100);
       });
       
       $("#timekeeping_entry").hide("fast");
       
       $( "#start_date" ).datepicker({ dateFormat: 'yy-mm-dd' });
       $( "#end_date" ).datepicker({dateFormat: 'yy-mm-dd' });
       
       payroll_list();
   });
   $('#uploadEmployeeDeductionOneTime').on('show.bs.modal', function(e) {
         var pay_id = $(e.relatedTarget).data('pay_id');
        $("#pay_id_upload_deduction").val(pay_id);
   });
    $('#uploadEmployeeOtherIncome').on('show.bs.modal', function(e) {
         var pay_id = $(e.relatedTarget).data('pay_id');
        $("#pay_id_upload_income").val(pay_id);
   });
    $(document).on("change", "#rice_allowance", function () {
        toggleRiceAllowanceAmount();
    });
   function toggleRiceAllowanceAmount() {
        if ($("#rice_allowance").is(":checked")) {
            $("#rice_allowance_amount_div").show();
        } else {
            $("#rice_allowance_amount_div").hide();
            $("#rice_allowance_amount").val(1500);
        }
    }
   function payroll_list(){
                    $('#payroll_tbl').DataTable({
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
                        "url": "{{ route('payroll_list') }}",
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
                        {'data': 'info'},
                        {'data': 'coverage'},
                        {'data': 'payroll_status'},
                        {'data': 'action', 'orderable': false, 'searchable': false},
                    ]
                });
			}
            
            function income_tbl(pay_id){
                    $('#income_tbl').DataTable({
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
                        "url": "{{ route('payroll_income_tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}", 
                            "page": "{{Route::current()->action['as']}}", 
                            "pay_id": pay_id
                        }
                    },
                    "columns":[
                        {'data': 'name'},
                        {'data': 'type'},
                        {'data': 'amount'},
                        {'data': 'action', 'orderable': false, 'searchable': false},
                    ]
                });
			}
            
            function deduction_tbl(pay_id){
                $('#deduction_tbl').DataTable({
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
                    "url": "{{ route('payroll_deduction_tbl') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        "_token": "{{ csrf_token() }}", 
                        "page": "{{Route::current()->action['as']}}", 
                        "pay_id": pay_id
                    }
                },
                "columns":[
                    {'data': 'name'},
                    {'data': 'type'},
                    {'data': 'amount'},
                    {'data': 'action', 'orderable': false, 'searchable': false},
                ]
                });
                }
                function manual_tk_data(pay_id){
             
                        $('#tagged_employee_tk').DataTable({
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
                            "url": "{{ route('tagged_employee_tk') }}",
                            "dataType": "json",
                            "type": "POST",
                            "data":{
                                "_token": "{{ csrf_token() }}", 
                                "page": "{{Route::current()->action['as']}}", 
                                "pay_id": pay_id
                            }
                        },
                        "columns":[
                            {'data': 'name'},
                            {'data': 'regular_work'},
                            {'data': 'lates'},
                            {'data': 'regular_ot'},
                            {'data': 'special_ot'},
                            {'data': 'night_diff'},
                            {'data': 'regular_leave'},
                            {'data': 'sick_leave'},
                            {'data': 'special_leave'},
                            {'data': 'regular_holiday'},
                            {'data': 'special_holiday'},
                            {'data': 'action', 'orderable': false, 'searchable': false},
                        ]
                    });
                }
            function tagged_employee(pay_id){
             
                    $('#tagged_employee').DataTable({
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
                        "url": "{{ route('tagged_employee') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}", 
                            "page": "{{Route::current()->action['as']}}", 
                            "pay_id": pay_id
                        }
                    },
                    "columns":[
                        {'data': 'name'},
                        {'data': 'basic_pay'},
                        {'data': 'action', 'orderable': false, 'searchable': false},
                    ]
                });
			}
            $('#tag_employee').on('hide.bs.modal', function(e) {
                payroll_list();
              
            });
            
        function export_payroll(payid){
            $.notify("Downloading Report...", {type:"info",icon:"info"});
            var url = "{{url('/')}}";
            window.open(url+"/exportPayrollReport/"+payid, "_blank");
        }
        $('#payroll_modal').on('show.bs.modal', function(e) {
            var id = $(e.relatedTarget).data('id');
            var code = $(e.relatedTarget).data('code');
            var name = $(e.relatedTarget).data('name');
            var start = $(e.relatedTarget).data('date_start');
            var end = $(e.relatedTarget).data('date_end');
            var target_month =  $(e.relatedTarget).data('target_month');
            var target_year =  $(e.relatedTarget).data('target_year');
            var process_type = $(e.relatedTarget).data('process_type');
            var payroll_type = $(e.relatedTarget).data('payroll_type');
            var hr_group = $(e.relatedTarget).data('hr_group');
            var oth_income_data = $(e.relatedTarget).data('oth_income');
            var payroll_status = $(e.relatedTarget).data('payroll_status');
            var type_info = $(e.relatedTarget).data('type_info');
            var lib_loan = $(e.relatedTarget).data('lib_loan');
            var gsis = $(e.relatedTarget).data('gsis');
            var sss = $(e.relatedTarget).data('sss');
            var ph = $(e.relatedTarget).data('ph');
            var hdmf = $(e.relatedTarget).data('hdmf');
            var rice_allowance = $(e.relatedTarget).data('rice_allowance');
            var rice_allowance_amount = $(e.relatedTarget).data('rice_allowance_amount');
            var is_government = "{{Auth::user()->company['is_government']}}";
            if(is_government == 1){
                if(gsis == 1){
                    $('#gsis').removeAttr('checked');
                    $('#gsis').attr('checked', true);
                }else{
                    $('#gsis').removeAttr('checked');
                }
            }else{
                if(sss == 1){
                    $('#sss').removeAttr('checked');
                    $('#sss').attr('checked', true);
                }else{
                    $('#sss').removeAttr('checked');
                }
            }
            if(ph == 1){
                $('#ph').removeAttr('checked');
                $('#ph').attr('checked', true);
            }else{
                $('#ph').removeAttr('checked');
            }
            if(hdmf == 1){
                $('#hdmf').removeAttr('checked');
                $('#hdmf').attr('checked', true);
            }else{
                $('#hdmf').removeAttr('checked');
            }
            if(rice_allowance == 1){
                $("#rice_allowance").prop("checked", true);

                $("#rice_allowance_amount").val(
                    rice_allowance_amount && rice_allowance_amount > 0
                        ? rice_allowance_amount
                        : 1500
                );

                $("#rice_allowance_amount_div").show();
            }else{
                $("#rice_allowance").prop("checked", false);
                $("#rice_allowance_amount_div").hide();
                $("#rice_allowance_amount").val(1500);
            }
            $("#payroll_code").val(code);
            $("#payroll_name").val(name);
            $("#target_month").val(target_month).change();
            $("#target_year").val(target_year).change();
            
            $("#start_date").val(start);
            $("#end_date").val(end);
            $("#process_type").val(process_type).change();
            $("#payroll_type").val(payroll_type).change();
            $("#hr_group").val(hr_group).change();
            var selected_income = [];
            $.each(oth_income_data.toString().split(";"), function(i,e){
                selected_income[i] = e;
            });
            $('#reg_oth_inc').val(selected_income).trigger('change');
            var selected_loan = [];
            $.each(lib_loan.toString().split(";"), function(i,e){
                selected_loan[i] = e;
            });
            $('#lib_loan').val(selected_loan).trigger('change');
            $("#save_payroll").val(id);
            
            $("#type_info").val(type_info).change();
            
        });
        function remove_this(emp_id){
            var pay_id =$("#pay_id").val();
          
            $.confirm({
                                title: 'Remove',
                                content: 'Remove Employee',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('remove_tagged_employee')}}",
                                                data: {
                                                    emp_id:emp_id,
                                                    pay_id:pay_id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});  
                                                        tagged_employee(pay_id);
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
        }
        $("#save_payroll").on("click", function(){
            var payroll_code = $("#payroll_code").val();
            var payroll_name = $("#payroll_name").val();
            var target_month = $("#target_month").val();
            var target_year = $("#target_year").val();
            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();
            var process_type = $("#process_type").val();
            var payroll_type = $("#payroll_type").val();
            var hr_group = $("#hr_group").val();
            var reg_oth_inc = $("#reg_oth_inc").val();
            var save_payroll = $("#save_payroll").val();
            var type_info = $("#type_info").val();
            var lib_loan = $("#lib_loan").val();
            var gsis = 0;
            var sss = 0;
            var ph = 0;
            var hdmf = 0;
            var rice_allowance = $("#rice_allowance").is(":checked") ? 1 : 0;
            var rice_allowance_amount =
                rice_allowance == 1
                    ? ($("#rice_allowance_amount").val() || 1500)
                    : 0;
            $( ".statutory" ).each(function(i, value) {
                var stat_val = $(this).val();
                
                if(stat_val == "sss"){
                    if($('#'+stat_val).is(':checked') == true){
                        sss = 1;
                    }
                    
                }
                if(stat_val == "gsis"){
                    if($('#'+stat_val).is(':checked') == true){
                        gsis = 1;
                    }
                    
                }
                if(stat_val == "hdmf"){
                    if($('#'+stat_val).is(':checked') == true){
                        hdmf = 1;
                    }
                }
                if(stat_val == "ph"){
                    if($('#'+stat_val).is(':checked') == true){
                        ph = 1;
                    }
                }
                
                
                
                });
              
            $.confirm({
                                title: 'Save',
                                content: 'Save Payroll Data',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('save_payroll_info')}}",
                                                data: {
                                                    payroll_code: payroll_code,
                                                    payroll_name: payroll_name,
                                                    target_month: target_month,
                                                    target_year: target_year,
                                                    start_date: start_date,
                                                    end_date: end_date,
                                                    process_type: process_type,
                                                    payroll_type: payroll_type,
                                                    hr_group: hr_group,
                                                    reg_oth_inc: reg_oth_inc,
                                                    id: save_payroll,
                                                    lib_loan: lib_loan,
                                                    type_info: type_info,
                                                    gsis: gsis,
                                                    sss: sss,
                                                    ph: ph,
                                                    hdmf: hdmf,
                                                    rice_allowance: rice_allowance,
                                                    rice_allowance_amount: rice_allowance_amount,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});  
                                                        $('#payroll_modal').modal('hide');
                                                        payroll_list();
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
        $("#payroll_type").on("change", function(){
            var payroll_type = $("#payroll_type").val();
            if(payroll_type == "WEEKLY"){
                $("#type_info").empty().append("<option value='1'>First Week</option>");
                $("#type_info").append("<option value='2'>Second Week</option>");
                $("#type_info").append("<option value='3'>Third Week</option>");
                $("#type_info").append("<option value='4'>Fourth Week</option>");
                $("#type_info").append("<option value='5'>Fifth Week</option>");
            }else if(payroll_type == "SEMI"){
                $("#type_info").empty().append("<option value='1'>First Half</option>");
                $("#type_info").append("<option value='2'>Second Half</option>");
            }else if(payroll_type == "MONTHLY"){
                $("#type_info").empty().append("<option value='1'>Month</option>");
            }
        });
        $(".statutory").on("click", function(){
          var stat = $(this).val();
          var current_stat = $(this).attr("class");
          if(current_stat == "btn btn-dark btn-sm statutory"){
            $(this).attr("class", "btn btn-success btn-sm statutory");
            }else{
                $(this).attr("class", "btn btn-dark btn-sm statutory");
            }
        });
        $("#process_type").on("change", function () {
            let selected = $(this).val();
            let disable = (selected === "LC"); // LC = Leave Credits
            // Disable or enable these inputs
            $("#payroll_type").prop("disabled", disable);
            $("#type_info").prop("disabled", disable);
            $("#reg_oth_inc").prop("disabled", disable);
            $("#lib_loan").prop("disabled", disable);
            // Disable checkboxes
            $(".statutory").prop("disabled", disable);
            // Optionally uncheck checkboxes when disabled
            if (disable) {
                $(".statutory").prop("checked", false);
                $("#reg_oth_inc").val("").trigger("change"); // Reset select2/multiple
                $("#lib_loan").val("").trigger("change");
            }
        });
</script>
<script>
        $("#ded_include").on("click", function(){
            var ded_pay_id = $("#pay_ded_id").val();
            var emp_list = $("#emp_list_ded").val();
            var ded_one_time = $("#ded_one_time").val();
            var ded_amount = $("#ded_amount").val();
            
            if(emp_list == ""){
                $.notify("Please Select Employee", {type:"info",icon:"info"});    
                return;
            }else if(ded_one_time == null){
                $.notify("One Time Other Deduction Required", {type:"info",icon:"info"});    
                return;
            }else if(ded_amount <= 0){
                $.notify("Deduction Amount Required", {type:"info",icon:"info"});    
                return;
            }
            
            
            else{
                $.confirm({
                                title: 'Deduction',
                                content: 'Add One Time Deduction',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('add_oth_ded_payroll')}}",
                                                data: {
                                                    ded_pay_id:ded_pay_id,
                                                    emp_list: emp_list,
                                                    ded_one_time:ded_one_time,
                                                    ded_amount: ded_amount,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});
                                                         deduction_tbl(ded_pay_id);
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
            }
        });
        
        $("#inc_include").on("click", function(){
            var inc_pay_id = $("#pay_inc_id").val();
            var emp_list = $("#emp_list_inc").val();
            var inc_one_time = $("#inc_one_time").val();
            var inc_amount = $("#inc_amount").val();
            
            if(emp_list == ""){
                $.notify("Please Select Employee", {type:"info",icon:"info"});    
                return;
            }else if(inc_one_time == null){
                $.notify("One Time Other Income Required", {type:"info",icon:"info"});    
                return;
            }else if(inc_amount <= 0){
                $.notify("Income Amount Required", {type:"info",icon:"info"});    
                return;
            }
            
            
            else{
                $.confirm({
                                title: 'Other Income',
                                content: 'Add One Time Other Income',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('add_oth_inc_payroll')}}",
                                                data: {
                                                    inc_pay_id:inc_pay_id,
                                                    emp_list: emp_list,
                                                    inc_one_time:inc_one_time,
                                                    inc_amount: inc_amount,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});
                                                        income_tbl(inc_pay_id);
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
            }
        });
        function remove_deduction(id){
            var ded_pay_id = $("#pay_ded_id").val();
            $.confirm({
                                title: 'Deductions',
                                content: 'Delete Encoded Deduction',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('delete_deduction_payroll')}}",
                                                data: {
                                                    id:id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});
                                                        deduction_tbl(ded_pay_id);
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
        }
        function remove_oth_inc(id){
            var inc_pay_id = $("#pay_inc_id").val();
            $.confirm({
                                title: 'Other Income',
                                content: 'Delete Encoded Other Income',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('delete_oth_inc_payroll')}}",
                                                data: {
                                                    id:id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});
                                                        income_tbl(inc_pay_id);
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
        }
        $('#payroll_inc_modal').on('hide.bs.modal', function(e) {
            $("#tag_employee").css({ opacity: 1 });
        });
        $('#payroll_inc_modal').on('show.bs.modal', function(e) {
            $("#inc_amount").val("0");
            var inc_pay_id =  $("#pay_inc_id").val();
            var emp_id = $(e.relatedTarget).data('emp_id');
          
            income_tbl(inc_pay_id);
            $.ajax({
                url: "{{route('payroll_tagged_list')}}",
                data: {
                    pay_id: inc_pay_id,
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                    if(data == "Payroll Info Unreachable"){
                        $.notify(data, {type:"danger",icon:"close"});    
                        return;
                    }else{
                        $("#emp_list_inc").empty();
                        $.each(data, function( index, value ) {
                                if(value.ext_name == null){
                                    value.ext_name = "";
                                }
                            $("#emp_list_inc").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                             });
                             if(emp_id != "all"){
                                $("#emp_list_inc").val(emp_id).change();
                             }
                    }
                  
                },
                dataType: 'json',
                method: 'POST'
            });
         
            $.ajax({
                url: "{{route('payroll_one_time_inc')}}",
                data: {
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                        $("#inc_one_time").empty();
                        $.each(data, function( index, value ) {
                             
                        $("#inc_one_time").append("<option value='"+value.id+"'>"+value.code+" - "+value.name +"</option>");
                            });
                },
                dataType: 'json',
                method: 'POST'
            });
            $("#tag_employee").css({ opacity: 0.2 });
        });
        
        $('#payroll_ded_modal').on('hide.bs.modal', function(e) {
            $("#tag_employee").css({ opacity: 1 });
        });
        
        $('#payroll_ded_modal').on('show.bs.modal', function(e) {
            $("#ded_amount").val("0");
            var ded_pay_id =  $("#pay_ded_id").val();
            var emp_id = $(e.relatedTarget).data('emp_id');
          
             deduction_tbl(ded_pay_id);
            $.ajax({
                url: "{{route('payroll_tagged_list')}}",
                data: {
                    pay_id: ded_pay_id,
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                    if(data == "Payroll Info Unreachable"){
                        $.notify(data, {type:"danger",icon:"close"});    
                        return;
                    }else{
                        $("#emp_list_ded").empty();
                        $.each(data, function( index, value ) {
                                if(value.ext_name == null){
                                    value.ext_name = "";
                                }
                            $("#emp_list_ded").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                             });
                             if(emp_id != "all"){
                                $("#emp_list_ded").val(emp_id).change();
                             }
                    }
                  
                },
                dataType: 'json',
                method: 'POST'
            });
         
            $.ajax({
                url: "{{route('payroll_one_time_ded')}}",
                data: {
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                        $("#ded_one_time").empty();
                        $.each(data, function( index, value ) {
                             
                        $("#ded_one_time").append("<option value='"+value.id+"'>"+value.code+" - "+value.name +"</option>");
                            });
                },
                dataType: 'json',
                method: 'POST'
            });
            $("#tag_employee").css({ opacity: 0.2 });
        });
        
        function push_for_approval(id){
            $.confirm({
                                title: 'Payroll',
                                content: 'Post For Approval?',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            
                                            $.ajax({
                                                url: "{{route('payroll_process_for_approval')}}",
                                                data: {
                                                    pay_id: id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                        payroll_list();
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
        }
        function re_open(id){
            $.confirm({
                                title: 'Payroll',
                                content: 'Re-open for Processing?',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            
                                            $.ajax({
                                                url: "{{route('re_open_payroll')}}",
                                                data: {
                                                    pay_id: id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                        payroll_list();
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
        }
        function approve_payroll(id){
            $.confirm({
                                title: 'Payroll',
                                content: 'Close Payroll Process?',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            
                                            $.ajax({
                                                url: "{{route('approve_payroll')}}",
                                                data: {
                                                    pay_id: id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                        payroll_list();
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
        }
        function process_timecard(id){
            $.confirm({
                title: 'Payroll',
                content: 'Proccess Timecard?',
                escapeKey: 'cancelAction',
                buttons: {
                    confirm: {
                        btnClass: 'btn-green',
                        text: 'Yes',
                        action: function(){

                            showPayrollLoading("Processing timecard...");
                            
                            $.ajax({
                                url: "{{route('payroll_process_timecard')}}",
                                data: {
                                    pay_id: id,
                                    _token : "{{csrf_token()}}", 
                                },
                                    success: function (data) { 
                                        $.notify(data, {type:"info",icon:"info"}); 
                                        payroll_list();
                                    },
                                    error: function () {
                                        $.notify("Something went wrong while processing timecard.", {
                                            type:"danger",
                                            icon:"danger"
                                        });
                                    },
                                    complete: function () {
                                        hidePayrollLoading();
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
        }
        function showPayrollLoading(message = "Processing...") {
            $("#payroll_loading_text").text(message);
            $("#payroll_loading_overlay").css("display", "flex");
        }

        function hidePayrollLoading() {
            $("#payroll_loading_overlay").hide();
        }
        $('#manual_timekeeping').on('show.bs.modal', function(e) {
            var pay_id = $(e.relatedTarget).data('pay_id');
       
            $("#select_employee_div_tk").hide("fast");
            $("#filter_tk").empty();
            $("#add_timekeeping").hide("fast");
            $("#pay_id_tk").val(pay_id);
            $("#filter_tk").append("<option value='0'>Select Fiter</option>");
            $("#filter_tk").append("<option value='custom'>Select Employee</option>");
            $("#filter_tk").val("custom").change();
         
            manual_tk_data(pay_id);
        });
        $("#filter_tk").on("change",function(){
            var filter = $(this).val();
            var pay_id = $("#pay_id_tk").val();
            if(filter == "0"){
                $("#select_employee_div_tk").hide("fast");
                $("#timekeeping_entry").hide("fast");
            }else{
                $.ajax({
                url: "{{route('get_employee_list_of_payroll_tk')}}",
                data: {
                    pay_id: pay_id,
                    filter: filter,
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                    
                   if(data=="Payroll Already for Posting"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else if(data=="Payroll Already Close"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else if(data=="Undefine Filter"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else if(data=="Payroll Information Unreachable"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else{
                        $("#select_employee_div_tk").show("fast");
                        $("#add_timekeeping").show("fast");
                  
                        $("#select_data_tk").empty();
                        $("#select_emp_tk").empty();
                        
                        $("#timekeeping_entry").hide("fast");
                        
                        
                        $("#select_data_div_tk").hide("fast")
                        $("#select_emp_div_tk").hide("fast");
                        if(filter == "custom"){
                            $("#select_emp_div_tk").show("fast");
                            $("#timekeeping_entry").show("fast");
                            $.each(data, function( index, value ) {
                                if(value.ext_name == null){
                                    value.ext_name = "";
                                }
                            $("#select_emp_tk").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                             });
                        }else{
                            $("#select_data_div_tk").show("fast");
                            $("#timekeeping_entry").show("fast");
                            if(filter == "department"){
                                $.each(data, function( index, value ) {
                                    $("#select_data_tk").append("<option value='"+value.id+"'>"+value.code+" - "+value.department+"</option>")
                                    });
                            }else if(filter == "branch"){
                                $.each(data, function( index, value ) {
                                    $("#select_data_tk").append("<option value='"+value.id+"'>"+value.code+" - "+value.branch+"</option>")
                                    });
                            }else if(filter == "designation"){
                                $.each(data, function( index, value ) {
                                    $("#select_data_tk").append("<option value='"+value.id+"'>"+value.code+" - "+value.name+"</option>")
                                    });
                                 
                            }else if(filter == "agency"){
                                $.each(data, function( index, value ) {
                                    $("#select_data_tk").append("<option value='"+value.agency_name+"'>"+value.agency_name+"</option>")
                                    });
                                 
                            }else{
                                $.notify("Undefine Filter", {type:"danger",icon:"close"});    
                                return;
                            }
                        }
                      
                   }
                },
                dataType: 'json',
                method: 'POST'
            });
              
                $("#select_employee_div_tk").show("fast");
            }
        });
        $('#tag_employee').on('show.bs.modal', function(e) {
            var pay_id = $(e.relatedTarget).data('pay_id');
            tagged_employee(pay_id);
            $("#select_employee_div").hide("fast");
            $("#filter").empty();
             $("#inc_btn").hide("fast");
            
            
            $("#pay_inc_id").val(pay_id);
            $("#pay_ded_id").val(pay_id);
            
            $("#pay_id").val(pay_id);
            
            $.ajax({
                url: "{{route('tag_emp_modal')}}",
                data: {
                    pay_id: pay_id,
                
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                    
                    if(data == "success"){
                        $("#filter").append("<option value='0'>Select Type</option>");
                        $("#filter").append("<option value='department'>Department</option>");
                        $("#filter").append("<option value='branch'>Site</option>");
                        $("#filter").append("<option value='designation'>Designation</option>");
                        // $("#filter").append("<option value='agency'>Agency Name</option>");
                        $("#filter").append("<option value='custom'>Select Employee</option>");
                        $("#filter").append("<option value='salary_type'>By Salary Type</option>");
                    
                       
                    }else{
                        $.notify(data, {type:"danger",icon:"close"});    
                        return;
                    }
                },
                dataType: 'json',
                method: 'POST'
            });
         
        });
        $("#filter").on("change",function(){
            var filter = $(this).val();
            var pay_id = $("#pay_id").val();
            if(filter == "0"){
                $("#select_employee_div").hide("fast");
            }else{
                $.ajax({
                url: "{{route('get_employee_list_of_payroll')}}",
                data: {
                    pay_id: pay_id,
                    filter: filter,
                    _token : "{{csrf_token()}}", 
                },
                success: function (data) { 
                    
                   if(data=="Payroll Already for Posting"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else if(data=="Payroll Already Close"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else if(data=="Undefine Filter"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else if(data=="Payroll Information Unreachable"){
                         $.notify(data, {type:"danger",icon:"close"});    
                        return;
                   }else{
                        $("#select_employee_div").show("fast");
                        $("#inc_btn").show("fast");
                        $("#include").show("fast");
                        $("#select_data").empty();
                        $("#select_emp").empty();
                        
                        $("#select_data_div").hide("fast")
                        $("#select_emp_div").hide("fast");
                        if(filter == "custom"){
                            $("#select_emp_div").show("fast");
                            $.each(data, function( index, value ) {
                                if(value.ext_name == null){
                                    value.ext_name = "";
                                }
                            $("#select_emp").append("<option value='"+value.id+"'>"+value.emp_code+" - "+value.last_name+", "+value.first_name+" "+value.ext_name+"</option>")
                             });
                            // Destroy existing Select2 and reinitialize with proper search configuration
                            $("#select_emp").select2('destroy');
                            $("#select_emp").select2({
                                allowClear: true,
                                width: '100%'
                            });
                        }else{
                            $("#select_data_div").show("fast");
                            
                            if(filter == "department"){
                                $.each(data, function( index, value ) {
                                    $("#select_data").append("<option value='"+value.id+"'>"+value.code+" - "+value.department+"</option>")
                                    });
                            }else if(filter == "branch"){
                                $.each(data, function( index, value ) {
                                    $("#select_data").append("<option value='"+value.id+"'>"+value.code+" - "+value.branch+"</option>")
                                    });
                            }else if(filter == "designation"){
                                $.each(data, function( index, value ) {
                                    $("#select_data").append("<option value='"+value.id+"'>"+value.code+" - "+value.name+"</option>")
                                    });
                                 
                            }else if(filter == "agency"){
                                $.each(data, function( index, value ) {
                                    $("#select_data").append("<option value='"+value.agency_name+"'>"+value.agency_name+"</option>")
                                    });
                                 
                            }else if(filter == "salary_type"){
                                $.each(data, function( index, value ) {
                                    $("#select_data").append("<option value='"+value.salary_type+"'>"+value.salary_type+"</option>")
                                    });
                                 
                            }else{
                                $.notify("Undefine Filter", {type:"danger",icon:"close"});    
                                return;
                            }
                            // Destroy existing Select2 and reinitialize with proper search configuration
                            $("#select_data").select2('destroy');
                            $("#select_data").select2({
                                allowClear: true,
                                width: '100%'
                            });
                        }
                      
                   }
                },
                dataType: 'json',
                method: 'POST'
            });
                $("#select_employee_div").show("fast");
            }
        });
        function trigger_pay_process(pay_id, jc = null){

            if(jc){
                jc.setContent(`
                    <div class="text-center p-3">
                        <div class="spinner-border text-success mb-3"></div>
                        <h5>Processing payroll...</h5>
                        <small>Please wait. Do not refresh or close this page.</small>
                    </div>
                `);
            }else{
                showPayrollLoading("Processing payroll...");
            }

            $.ajax({
                url: "{{route('payroll_process')}}",
                data: {
                    pay_id: pay_id,
                    _token : "{{csrf_token()}}",
                },
                success: function (data) {
                    $.notify(data, {type:"info", icon:"info"});
                    payroll_list();
                },
                error: function () {
                    $.notify("Something went wrong while processing payroll.", {
                        type:"danger",
                        icon:"danger"
                    });
                },
                complete: function () {
                    if(jc){
                        jc.close();
                    }else{
                        hidePayrollLoading();
                    }
                },
                dataType: 'json',
                method: 'POST'
            });
        }
        function process_payroll(pay_id){
            $.confirm({
                title: 'Payroll',
                content: 'Process Payroll?',
                escapeKey: 'cancelAction',
                buttons: {
                    confirm: {
                        btnClass: 'btn-green',
                        text: 'Yes',
                        action: function(){

                            var jc = this;

                            jc.buttons.confirm.disable();
                            jc.buttons.cancelAction.disable();

                            jc.setContent(`
                                <div class="text-center p-3">
                                    <div class="spinner-border text-primary mb-3"></div>
                                    <h5>Checking payroll data...</h5>
                                    <small>Please wait. Do not refresh or close this page.</small>
                                </div>
                            `);

                            $.ajax({
                                url: "{{route('check_payroll_data')}}",
                                data: {
                                    pay_id: pay_id,
                                    _token : "{{csrf_token()}}",
                                },
                                dataType: 'json',
                                method: 'POST',
                                success: function (data) {

                                    if(data == "timekeeping_404"){

                                        jc.close();

                                        $.confirm({
                                            title: 'Time Keeping Error',
                                            content: 'Process Payroll even without data on Timekeeping?',
                                            buttons: {
                                                confirm: {
                                                    btnClass: 'btn-green',
                                                    text: 'Yes',
                                                    action: function(){
                                                        trigger_pay_process(pay_id);
                                                    }
                                                },
                                                cancelAction: {
                                                    btnClass: 'btn-gray',
                                                    text: 'No'
                                                }
                                            }
                                        });

                                    }else if(data == "success"){

                                        jc.setContent(`
                                            <div class="text-center p-3">
                                                <div class="spinner-border text-success mb-3"></div>
                                                <h5>Processing payroll...</h5>
                                                <small>Please wait. This may take a while.</small>
                                            </div>
                                        `);

                                        trigger_pay_process(pay_id, jc);

                                    }else{

                                        jc.close();
                                        $.notify(data, {type:"danger", icon:"danger"});
                                    }
                                },
                                error: function () {
                                    jc.close();
                                    $.notify("Something went wrong while checking payroll data.", {
                                        type:"danger",
                                        icon:"danger"
                                    });
                                }
                            });

                            return false;
                        }
                    },
                    cancelAction: {
                        btnClass: 'btn-gray',
                        text: 'No'
                    }
                }
            });
        }
        $("#include").on("click", function(){
            var filter = $("#filter").val();
            var pay_id = $("#pay_id").val();
            if(filter == "custom"){
                var selected = $("#select_emp").val();
            }else{
                var selected = $("#select_data").val();
            }
     
                 $.confirm({
                                title: 'Tag Employee',
                                content: 'Add List to Payroll?',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('tag_employee_to_payroll')}}",
                                                data: {
                                                    filter:filter,
                                                    pay_id: pay_id,
                                                    selected:selected,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                 
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                        
                                                        $("#filter").val("0").change();
                                                        $("#include").hide("fast");
                                                        tagged_employee(pay_id);
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
        $("#add_timekeeping").on("click", function(){
            var filter = $("#filter_tk").val();
            var pay_id = $("#pay_id_tk").val();
            var regular_manual = $("#regular_manual").val();
            var lates = $("#lates").val();
            var rot = $("#rot").val();
            var sot = $("#sot").val();
            var nd = $("#nd").val();
            var vl = $("#vl").val();
            var sl = $("#sl").val();
            var spl_leave = $("#spl_leave").val();
            var reg_hol = $("#reg_hol").val();
            var spl_hol = $("#spl_hol").val();
            if(filter == "custom"){
                var selected = $("#select_emp_tk").val();
            }else{
                var selected = $("#select_data_tk").val();
            }
     
                 $.confirm({
                                title: 'Manual Timekeeping',
                                content: 'Add List to Payroll?',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('manual_add_tk_to_payroll')}}",
                                                data: {
                                                    filter:filter,
                                                    pay_id: pay_id,
                                                    selected:selected,
                                                    regular_manual: regular_manual,
                                                    lates: lates,
                                                    rot: rot,
                                                    sot: sot,
                                                    nd: nd,
                                                    vl: vl,
                                                    sl: sl,
                                                    spl_leave: spl_leave,
                                                    reg_hol: reg_hol,
                                                    spl_hol: spl_hol,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                 
                                                        $.notify(data, {type:"info",icon:"info"}); 
                                                        
                                                        $("#filter_tk").val("0").change();
                                                        $("#add_timekeeping").hide("fast");
                                                        // tagged_employee(pay_id);
                                                        manual_tk_data(pay_id);
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
function download_timecard(payid){
            $.notify("Downloading Report...", {type:"info",icon:"info"});
            var url = "{{url('/')}}";
            window.open(url+"/export_tc/"+payid, "_blank");
        }
        function remove_this_tk(tk_id, pay_id){
            $.confirm({
                                title: 'Remove',
                                content: 'Remove Manual Timekeeping',
                                escapeKey: 'cancelAction',
                                buttons: {
                                    confirm: {
                                        btnClass: 'btn-green',
                                        text: 'Yes',
                                        action: function(){
                                            $.ajax({
                                                url: "{{route('remove_tagged_employee_tk')}}",
                                                data: {
                                                    tk_id:tk_id,
                                                    _token : "{{csrf_token()}}", 
                                                },
                                                    success: function (data) { 
                                                        $.notify(data, {type:"info",icon:"info"});  
                                                        manual_tk_data(pay_id);
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
            
        }
        function delete_payroll(id){
            $.confirm({
                title: 'Delete',
                content: 'Are you sure to delete this payroll?',
                escapeKey: 'cancelAction',
                buttons: {
                    confirm: {
                        btnClass: 'btn-danger',
                        text: "Yes",
                        action: function(){
                            HoldOn.open(holdon_option);
                            $.ajax({
                                    url: "{{route('delete_payroll')}}",
                                data: {
                                    _token : "{{csrf_token()}}", 
                                    id: id,
                                },
                                    success: function (data) { 
                                        
                                        $.notify(data, {type:"info",icon:"info"}); 
                                        payroll_list();
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
<script>
let current_payroll_id = 0;

function open_credit_adj_modal(payroll_id){
    current_payroll_id = payroll_id;
    $('#credit_adj_modal').modal('show');
    load_employee_dropdown();
    load_credit_adj();
}

function load_employee_dropdown(selected_emp_id = null){

    $.ajax({
        url: "{{route('load_payroll_employees')}}",
        data: {
            _token : "{{csrf_token()}}", 
            page: "{{Route::current()->action['as']}}",
            payroll_id: current_payroll_id 
        },
        success: function (data) {

            let dropdown = $("#ca_emp_id");

            dropdown.empty().append("<option value='0'>Select Employee</option>");

            $.each(data, function(index, value){

                let ext = value.ext_name ? value.ext_name : "";

                dropdown.append(
                    "<option value='"+value.id+"'>"+
                    value.emp_code+" - "+
                    value.last_name+", "+
                    value.first_name+" "+ext+
                    "</option>"
                );

            });

            // optional preselect
            if(selected_emp_id){
                dropdown.val(selected_emp_id).change();
            }

        },
        dataType: 'json',
        method: 'POST'
    });
}

function save_credit_adj(){

    $.post("{{ route('credit_adj.store') }}", {
        _token: "{{ csrf_token() }}",
        payroll_id: current_payroll_id,
        emp_id: $("#ca_emp_id").val(),
        amount: $("#ca_amount").val()
    }, function(res){

        if(res === "saved"){
            $.notify("Saved", {type:"success"});
            load_credit_adj();
        }else{
            $.notify(res, {type:"danger"});
        }

    });
}

function load_credit_adj(){
    $('#credit_adj_tbl').DataTable({
        destroy: true,
        ajax: {
            url: "{{ route('credit_adj.list') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                payroll_id: current_payroll_id
            }
        },
        columns: [
            {data:'name'},
            {data:'amount'},
            {data:'requested_by'},
            {data:'status'},
            {data:'action'}
        ]
    });
}

function approve_credit(id){
    id = id.replace('pending_','');

    $.post("{{ route('credit_adj.approve') }}", {
        _token: "{{ csrf_token() }}",
        id: id
    }, function(res){
        load_credit_adj();
        payroll_list(); // refresh badge
    });
}

function reject_credit(id){

    id = id.replace('pending_','');

    $.ajax({
        url: "{{ route('credit_adj.reject') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id
        },
        success: function(res){
            if(res === "rejected"){
                $.notify("Rejected", {type:"warning"});
                load_credit_adj();
                payroll_list(); // refresh badge
            }else{
                $.notify(res, {type:"danger"});
            }
        }
    });
}
</script>
@stop