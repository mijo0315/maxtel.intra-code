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



<div class="page-wrapper" id="report_page">

    <div class="content container-fluid">

        <div class="row" >

            @if(Auth::user()->company['version'] == 1)

           

            @endif



            <div class="col-xl-10 col-sm-10 col-10 mb-2">

                <div class="head-link-set">

                    <ul>

                        <li><a class="mnu_btn active"  onclick="" id="mnu_payreport">Payroll Report</a></li>

                        <!-- <li><a class="mnu_btn"  onclick="" id="mnu_statureport">Statutory Report</a></li>

                        <li><a class="mnu_btn"  onclick="" id="mnu_othreport">Other Report</a></li> -->

                        

                        

                    </ul>

                </div>

            </div>

      

            

        </div>

        @include("report.payreport")

        <!-- @include("report.statreport")

        @include("report.othreport") -->

        







    </div>

</div>











@endif



@stop



@section("scripts")



<script>

    $("#timekeeping_oth").on("click", function(){

        var date_from = $("#date_from").val();

        var date_to = $("#date_to").val();

        $.notify("Downloading Report...", {type:"info",icon:"info"});

         var url = "{{url('/')}}";

         window.open(url+"/exportTimeKeeping/"+date_from+"/"+date_to, "_blank");

    });

    

    $("#emp_list_oth").on("click", function(){

        var date_from = $("#date_from").val();

        var date_to = $("#date_to").val();

        $.notify("Downloading Report...", {type:"info",icon:"info"});

         var url = "{{url('/')}}";

         window.open(url+"/exporEmpList/"+date_from+"/"+date_to, "_blank");

    });



    $("#reg_incomes_oth").on("click", function(){

        var date_from = $("#date_from").val();

        var date_to = $("#date_to").val();

        $.notify("Downloading Report...", {type:"info",icon:"info"});

         var url = "{{url('/')}}";

         window.open(url+"/exportRegIncome/"+date_from+"/"+date_to, "_blank");

    });



</script>





<script>



    $("#statutory_download").on("click", function(){

        var month_year = $("#stat_month").val();

        var type = $("#statutory_type").val();

        

        $.notify("Downloading Report...", {type:"info",icon:"info"});

         var url = "{{url('/')}}";

         window.open(url+"/exportStatutoryReport/"+month_year+"/"+type, "_blank");

  



    });

    

    



    





</script>





<script>



    function export_payroll(payid){

        $.notify("Downloading Report...", {type:"info",icon:"info"});

        var url = "{{url('/')}}";

        window.open(url+"/exportPayrollReport/"+payid, "_blank");

    }



    function download_payslip(payid){

        $.notify("Downloading Report...", {type:"info",icon:"info"});

        var url = "{{url('/')}}";

        window.open(url+"/payroll_payslip/"+payid, "_blank");

    }





    function payreport_tbl(){

        $('#payreport_tbl').DataTable({

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

				"url": "{{ route('report_pay_list') }}",

				"dataType": "json",

				"type": "POST",

				"data":{

					"_token": "{{ csrf_token() }}", 

					"page": "{{Route::current()->action['as']}}",  

				}

			},

			"columns":[

				{'data': 'name'},

				{'data': 'info'},

                {'data': 'status'},

                {'data': 'action', 'orderable': false, 'searchable': false},



			]

		});

    }





</script>







<script>

$( document ).ready(function() {

    $(".form-select").select2();

    $("#mnu_payreport").click();

    

    $( "#date_from" ).datepicker({ dateFormat: 'yy-mm-dd' });

    $( "#date_to" ).datepicker({dateFormat: 'yy-mm-dd' });



	var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";

    if(user_type == "employee"){

        $("#emp_list_oth").hide("fast");

    }else{

        $("#emp_list_oth").show("fast");

    }

    



});



$(".mnu_btn").on("click", function(){

		  $(".mnu_btn").removeAttr("class");

		  $(".rpt_tab").hide("fast");

		  var mnu_data = $(this).attr("id");

		  $(this).attr("class", "active mnu_btn");

		  mnu_data = mnu_data.replace("mnu_","");

		  $("#"+mnu_data+"_tab").show("fast");



    if(mnu_data == "payreport"){

        payreport_tbl();

    }else if(mnu_data == "statureport"){

        $("#stat_month").val("{{date('Y-m')}}");

    }

    



    



});





</script>

@stop