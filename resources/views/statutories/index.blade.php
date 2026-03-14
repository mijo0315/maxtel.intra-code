@extends('layouts.front-app')

@section('title')

{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Statutories

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

                                    @if(Auth::user()->company["is_government"] == "1")

									<li><a class="active mnu_btn" id="mnu_gsis"  onclick="">GSIS</a></li>

                                    @else

									<li><a class="active mnu_btn" id="mnu_sss"  onclick="">SSS</a></li>

                                    @endif



                                    <li><a class="mnu_btn" id="mnu_ph"  onclick="">PhilHealth</a></li>

                                    <li><a class="mnu_btn" id="mnu_hdmf"  onclick="">PAG-IBIG (HDMF)</a></li>

                                    <li><a class="mnu_btn" id="mnu_tax"  onclick="">BIR Tax</a></li>







								</ul>

							</div>

						</div>



                        @include("statutories.gsis")

                        @include("statutories.sss")

                        @include("statutories.ph")

                        @include("statutories.hdmf")

                        @include("statutories.tax")





                    </div>

            </div>

</div>

{{-- $('#sched_list').show('slow'); $('#sched_library').hide('slow');  $('#tab_lib').removeAttr('class'); $('#tab_sched').attr('class','active');  --}}

@endif



@stop



@section("scripts")

    <script>

        $( document ).ready(function() {

            var is_gov = '{{Auth::user()->company["is_government"]}}' ;

            if(is_gov == "1"){

                $("#mnu_gsis").click();

            }else{

                $("#mnu_sss").click();

                // $("#mnu_tax").click();



            }

            sss_tbl();

            ph_tbl();

            hdmf_tbl();

            tax_tbl();

            $("#tax_type").select2();

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



    <script>

        function gsis_update(){

            var emp_rate = $("#gsis_emp_rate").val();

            var com_rate = $("#gsis_com_rate").val();



            $.confirm({

							title: 'Statutory',

							content: 'GSIS Update',

							escapeKey: 'cancelAction',

							buttons: {

								confirm: {

									btnClass: 'btn-green',

									text: 'Update',

									action: function(){

										HoldOn.open(holdon_option);



										$.ajax({

												url: "{{route('update_gsis_rate')}}",

											data: {

												_token : "{{csrf_token()}}", 

												emp_rate: emp_rate,

                                                com_rate: com_rate

											},

												success: function (data) { 

													$.notify("Success", {type:"info",icon:"info"}); 

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



        function sss_tbl(){

           

				$('#sss_tbl').DataTable({

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

                    "url": "{{ route('sss_tbl') }}",

                    "dataType": "json",

                    "type": "POST",

                    "data":{

                        "_token": "{{ csrf_token() }}", 

						"page": "{{Route::current()->action['as']}}"  

                        

                    }

                },

                "columns":[

                  	{'data': 'salary_from'},

                    {'data': 'salary_to'},

                    {'data': 'credit'},

                    {'data': 'com_share'},

                    {'data': 'emp_share'},

                    {'data': 'ec'},

                    

				

					{'data': 'action', 'orderable': false, 'searchable': false},



                ]

            });



			

        }



        $('#sss_modal').on('show.bs.modal', function(e) {

            var sss_salary_from = $(e.relatedTarget).data('salary_from');

            var sss_salary_to = $(e.relatedTarget).data('salary_to');

            var sss_credit = $(e.relatedTarget).data('credit_ec');

            var sss_com_share = $(e.relatedTarget).data('regular_er');

            var sss_emp_share = $(e.relatedTarget).data('regular_ee');

            var sss_ec = $(e.relatedTarget).data('ec');

            var sss_id = $(e.relatedTarget).data('id');

        

                $("#sss_update_btn").val(sss_id);

                $("#sss_salary_from").val(sss_salary_from);

                $("#sss_salary_to").val(sss_salary_to);

                $("#sss_credit").val(sss_credit);

                $("#sss_com_share").val(sss_com_share);

                $("#sss_emp_share").val(sss_emp_share);

                $("#sss_ec").val(sss_ec);

                



        });



       





        $('#tax_modal').on('show.bs.modal', function(e) {

            var tax_id = $(e.relatedTarget).data('id');

            var tax_type = $(e.relatedTarget).data('type');

            var tax_salary_from = $(e.relatedTarget).data('salary_from');

            var tax_salary_to = $(e.relatedTarget).data('salary_to');

            var tax_amount = $(e.relatedTarget).data('fix_amount');

            var tax_rate_over = $(e.relatedTarget).data('rate_over');

            var tax_rate = $(e.relatedTarget).data('rate');

            var tax_year = $(e.relatedTarget).data('year_effect');

            



  



                $("#tax_update_btn").val(tax_id);

                $("#tax_type").val(tax_type).change();

                $("#tax_salary_from").val(tax_salary_from);

                $("#tax_salary_to").val(tax_salary_to);

                $("#tax_amount").val(tax_amount);

                $("#tax_rate_over").val(tax_rate_over);

                $("#tax_rate").val(tax_rate);

                $("#tax_year").val(tax_year);



                



        });





        $("#tax_update_btn").on("click", function(){

           var tax_update_btn = $("#tax_update_btn").val();

           var tax_type = $("#tax_type").val();

           var tax_salary_from = $("#tax_salary_from").val();

           var tax_salary_to = $("#tax_salary_to").val();

           var tax_amount = $("#tax_amount").val();

           var tax_rate_over = $("#tax_rate_over").val();

           var tax_rate = $("#tax_rate").val();

           var tax_year = $("#tax_year").val();



            $.confirm({

							title: 'Statutory',

							content: 'Tax Table Update',

							escapeKey: 'cancelAction',

							buttons: {

								confirm: {

									btnClass: 'btn-green',

									text: 'Update',

									action: function(){

										HoldOn.open(holdon_option);



										$.ajax({

												url: "{{route('update_tax_rate')}}",

											data: {

												_token : "{{csrf_token()}}", 

												id : tax_update_btn,

                                                tax_type: tax_type,

                                                tax_salary_from: tax_salary_from,

                                                tax_salary_to: tax_salary_to,

                                                tax_amount: tax_amount,

                                                tax_rate_over: tax_rate_over,

                                                tax_rate: tax_rate,

                                                tax_year: tax_year,

											},

												success: function (data) { 

													$.notify(data, {type:"info",icon:"info"}); 

                                                    tax_tbl();

                                                    $("#tax_modal").modal("hide");

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



        $('#ph_modal').on('show.bs.modal', function(e) {

            var ph_id = $(e.relatedTarget).data('id');

            var ph_salary_from = $(e.relatedTarget).data('salary_from');

            var ph_salary_to = $(e.relatedTarget).data('salary_to');

            var ph_com_share = $(e.relatedTarget).data('rate_employer');

            var ph_emp_share = $(e.relatedTarget).data('rate_employee');

            var ph_year = $(e.relatedTarget).data('year_effect');



        

                $("#ph_update_btn").val(ph_id);

                $("#ph_salary_from").val(ph_salary_from);

                $("#ph_salary_to").val(ph_salary_to);

                $("#ph_com_share").val(ph_com_share);

                $("#ph_emp_share").val(ph_emp_share);

                $("#ph_year").val(ph_year);

                



        });

        

        $('#hdmf_modal').on('show.bs.modal', function(e) {

            var hdmf_id = $(e.relatedTarget).data('id');

            var hdmf_salary_from = $(e.relatedTarget).data('salary_from');

            var hdmf_salary_to = $(e.relatedTarget).data('salary_to');

            var hdmf_com_share = $(e.relatedTarget).data('rate_employer');

            var hdmf_emp_share = $(e.relatedTarget).data('rate_employee');

            var hdmf_year = $(e.relatedTarget).data('year_effect');



        

                $("#hdmf_update_btn").val(hdmf_id);

                $("#hdmf_salary_from").val(hdmf_salary_from);

                $("#hdmf_salary_to").val(hdmf_salary_to);

                $("#hdmf_com_share").val(hdmf_com_share);

                $("#hdmf_emp_share").val(hdmf_emp_share);

                $("#hdmf_year").val(hdmf_year);

                



        });

        



        $("#sss_update_btn").on("click", function(){



            var id = $("#sss_update_btn").val();

            var sss_salary_from = $("#sss_salary_from").val();

            var sss_salary_to = $("#sss_salary_to").val();

            var sss_credit = $("#sss_credit").val();

            var sss_com_share = $("#sss_com_share").val();

            var sss_emp_share = $("#sss_emp_share").val();

            var sss_ec = $("#sss_ec").val();



            $.confirm({

							title: 'Statutory',

							content: 'SSS Update',

							escapeKey: 'cancelAction',

							buttons: {

								confirm: {

									btnClass: 'btn-green',

									text: 'Update',

									action: function(){

										HoldOn.open(holdon_option);



										$.ajax({

												url: "{{route('update_sss_rate')}}",

											data: {

												_token : "{{csrf_token()}}", 

												id : id,

                                                sss_salary_from : sss_salary_from,

                                                sss_salary_to : sss_salary_to,

                                                sss_credit : sss_credit,

                                                sss_com_share : sss_com_share,

                                                sss_emp_share : sss_emp_share,

                                                sss_ec : sss_ec,

											},

												success: function (data) { 

													$.notify("Success", {type:"info",icon:"info"}); 

                                                    sss_tbl();

                                                    $("#sss_modal").modal("hide");

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



        function ph_tbl(){

           

           $('#ph_tbl').DataTable({

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

                    "url": "{{ route('ph_tbl') }}",

                    "dataType": "json",

                    "type": "POST",

                    "data":{

                        "_token": "{{ csrf_token() }}", 

                        "page": "{{Route::current()->action['as']}}"  

                        

                    }

                },

                "columns":[

                        {'data': 'salary_from'},

                    {'data': 'salary_to'},

                    {'data': 'com_share'},

                    {'data': 'emp_share'},

                    {'data': 'year'},

                    {'data': 'action', 'orderable': false, 'searchable': false},



                ]

            });

        }



        $("#ph_update_btn").on("click", function(){

            var ph_update_btn = $("#ph_update_btn").val();

            var ph_salary_from = $("#ph_salary_from").val();

            var ph_salary_to = $("#ph_salary_to").val();

            var ph_com_share = $("#ph_com_share").val();

            var ph_emp_share = $("#ph_emp_share").val();

            var ph_year = $("#ph_year").val();





            $.confirm({

							title: 'Statutory',

							content: 'Philhealth Update',

							escapeKey: 'cancelAction',

							buttons: {

								confirm: {

									btnClass: 'btn-green',

									text: 'Update',

									action: function(){

										HoldOn.open(holdon_option);



										$.ajax({

												url: "{{route('update_ph_rate')}}",

											data: {

												_token : "{{csrf_token()}}", 

												id : ph_update_btn,

                                                ph_salary_from : ph_salary_from,

                                                ph_salary_to : ph_salary_to,

                                                ph_com_share : ph_com_share,

                                                ph_emp_share : ph_emp_share,

                                                ph_year : ph_year,

											},

												success: function (data) { 

													$.notify(data, {type:"info",icon:"info"}); 

                                                    ph_tbl();

                                                    $("#ph_modal").modal("hide");

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





        $("#hdmf_update_btn").on("click", function(){

            var hdmf_update_btn = $("#hdmf_update_btn").val();

            var hdmf_salary_from = $("#hdmf_salary_from").val();

            var hdmf_salary_to = $("#hdmf_salary_to").val();

            var hdmf_com_share = $("#hdmf_com_share").val();

            var hdmf_emp_share = $("#hdmf_emp_share").val();

            var hdmf_year = $("#hdmf_year").val();





            $.confirm({

							title: 'Statutory',

							content: 'PAG-IBIG Update',

							escapeKey: 'cancelAction',

							buttons: {

								confirm: {

									btnClass: 'btn-green',

									text: 'Update',

									action: function(){

										HoldOn.open(holdon_option);



										$.ajax({

												url: "{{route('update_hdmf_rate')}}",

											data: {

												_token : "{{csrf_token()}}", 

												id : hdmf_update_btn,

                                                hdmf_salary_from : hdmf_salary_from,

                                                hdmf_salary_to : hdmf_salary_to,

                                                hdmf_com_share : hdmf_com_share,

                                                hdmf_emp_share : hdmf_emp_share,

                                                hdmf_year : hdmf_year,

											},

												success: function (data) { 

													$.notify(data, {type:"info",icon:"info"}); 

                                                    hdmf_tbl();

                                                    $("#hdmf_modal").modal("hide");

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





        function hdmf_tbl(){

           

           $('#hdmf_tbl').DataTable({

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

                    "url": "{{ route('hdmf_tbl') }}",

                    "dataType": "json",

                    "type": "POST",

                    "data":{

                        "_token": "{{ csrf_token() }}", 

                        "page": "{{Route::current()->action['as']}}"  

                        

                    }

                },

                "columns":[

                        {'data': 'salary_from'},

                    {'data': 'salary_to'},

                    {'data': 'com_share'},

                    {'data': 'emp_share'},

                    {'data': 'year'},

                    {'data': 'action', 'orderable': false, 'searchable': false},



                ]

            });

        }





        function tax_tbl(){

           

           $('#tax_tbl').DataTable({

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

                    "url": "{{ route('tax_tbl') }}",

                    "dataType": "json",

                    "type": "POST",

                    "data":{

                        "_token": "{{ csrf_token() }}", 

                        "page": "{{Route::current()->action['as']}}"  

                        

                    }

                },

                "columns":[

                    {'data': 'type'},

                    {'data': 'salary_from'},

                    {'data': 'salary_to'},

                    {'data': 'fix_tax'},

                    {'data': 'tax_over'},

                    {'data': 'tax_rate'},

                    {'data': 'year'},

                    {'data': 'action', 'orderable': false, 'searchable': false},



                ]

            });

        }





    </script>





@stop





