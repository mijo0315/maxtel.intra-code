<!DOCTYPE html>

<html lang="en">





<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Intracode IT Solutions



    </title>

 

 <link rel="shortcut icon" href="{{asset_with_env('assets/img/fav.png')}}">

    <link rel="stylesheet" href="{{asset_with_env('assets/css/bootstrap.min.css')}}">

    <link rel="stylesheet" href="{{asset_with_env('assets/plugins/fontawesome/css/fontawesome.min.css')}}">

    <link rel="stylesheet" href="{{asset_with_env('assets/plugins/fontawesome/css/all.min.css')}}">

    <link rel="stylesheet" href="{{asset_with_env('assets/css/style.css')}}">

    <link rel="stylesheet" href="{{asset_with_env('assets/css/style2.css')}}">

    

    <link rel="stylesheet" href="{{asset_with_env('plugins/confirm/jquery-confirm.min.css')}}">

    <link rel="stylesheet" href="{{asset_with_env('plugins/confirm/confirm-js.css')}}">

    <link rel="stylesheet" href="{{asset_with_env('plugins/holdOn/HoldOn.min.css')}}">



    <link href="{{asset_with_env('plugins/captcha/slidercaptcha.min.css')}}" rel="stylesheet" />

    <link href="{{asset_with_env('plugins/notify/notify.css')}}" rel="stylesheet">

    <link href="{{asset_with_env('plugins/notify/prettify.css')}}" rel="stylesheet">



    <link href="{{asset_with_env('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">

    <link rel="stylesheet" href="{{asset_with_env('plugins/datatable/jquery.dataTables.min.css')}}">



    <link rel="stylesheet" href="{{asset_with_env('css/jquery-ui.css')}}">

    

    <link rel="stylesheet" href="{{asset_with_env('plugins/dropzone/dropzone.min.css')}}" type="text/css" />



    <link rel="stylesheet" href="{{asset_with_env('plugins/clock/css/flipTimer.css')}}" type="text/css" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">


    <style>

        .jconfirm-box{

            height: auto;

            width:70%;

            margin-left: 5%; 

        }



        .emp_label{

            font-weight: bold;

        }

        .modal-header{
            background-color: #2f47ba;
        }
        .modal-header h5{
            color: white;
            letter-spacing: 2px;
        }
        .modal-header span{
            color: white;
        }



    </style>





        @yield("styles")



</head>





<body>



    



    <div class="main-wrapper">

        

        <div class="header">

            

            <div class="header-left">

                <a href="javascript:void(0);" class="toggle" id="toggle_btn">

                    <span class="bar-icon">

                        <span></span>

                        <span></span>

                        <span></span>

                    </span>

                </a>

                <a href="index.html" class="logo">

                    <img src="{{asset_with_env(str_replace('public/', '', Auth::user()->company['logo_main'])) }}" alt="Logo" >

                </a>

                <a href="index.html" class="logo logo-small">

                    <img src="{{asset_with_env(str_replace('public/', '', Auth::user()->company['logo_sub'])) }}" alt="Logo" width="30" height="30">

                </a>

            </div>

            

            

            

            

            

            

            

            <a class="mobile_btn" id="mobile_btn">

                <i class="fas fa-bars"></i>

            </a>

            

            

            

            

            

        </div>

        









        

        

        <div class="sidebar" id="sidebar">

            <div class="sidebar-inner slimscroll">

                <div class="sidebar-contents">

                    <div id="sidebar-menu" class="sidebar-menu">

                        <div class="mobile-show">

                            <div class="offcanvas-menu">

                                <div class="user-info align-center bg-theme text-center">

                                    <span class="lnr lnr-cross  text-white" id="mobile_btn_close">X</span>

                                    <a href="javascript:void(0)" class="d-block menu-style text-white">

                                        

                                    </a>

                                </div>

                            </div>

                            

                        </div>

                        <div class="sidebar-profile">

                            <a class="sidebar-profile-img">

                                <img src="{{asset_with_env(str_replace('public', '', Auth::user()->company['logo_sub'])) }}" alt="profile-img">

                                <div class="sidebar-profile-content">

                                    <h3>{{Auth::user()->company["company_name"]}}</h3>

                                    <span>{{Auth::user()->company["address"]}}</span>

                                </div>

                            </a>

                        </div>

                        <ul>



                      

                            

                    



                            @foreach(Auth::user()->access as $access)

                            

                                @if(preg_match("/R/i",$access["access"]))

                                    @if(Route::current()->action["as"] == $access["route"])

                                    <li class="active">

                                    @else

                                        <li>

                                    @endif

                                            

                                        {{-- {{dd($access)}} --}}

                                        <a href="{{route($access['route'])}}"><img src="{{ asset_with_env(str_replace('public/', '', $access['icon'])) }}" alt="sidebar_img"> <span>{{$access['name']}}</span></a>

                                    </li>





                                @endif



                                



                            @endforeach



                            

                            {{-- <li>

                                <a href="apply-leave.html"><img src="{{asset_with_env('assets/img/leave.svg')}}" alt="sidebar_img"> <span>Apply Leave</span></a>

                            </li> --}}

                            {{-- <li>

                                <a href="apply-loan.html"><img src="{{asset_with_env('assets/img/report.svg')}}" alt="sidebar_img"> <span>Apply Loan</span></a>

                            </li> --}}

                        

                            

                            <!--<li>

                                <a href="profile.html"><img src="{{asset_with_env('assets/img/profile.svg')}}" alt="sidebar_img"> <span>Profile</span></a>

                            </li>-->

                            

                        </ul>

                        <ul class="logout">

                            <li>

                                <a href="{{route('logout')}}"><img src="{{asset_with_env('assets/img/logout.svg')}}" alt="sidebar_img"><span>Log out</span></a>

                            </li>

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    



    

        @yield("content")

        

        

 



    </div>











    {{-- SCRIPTS --}}

    <script src="{{asset_with_env('assets/js/jquery-3.6.0.min.js')}}"></script>



    <script src="{{asset_with_env('assets/js/popper.min.js')}}"></script>

    <script src="{{asset_with_env('assets/js/bootstrap.min.js')}}"></script>

    

    <script src="{{asset_with_env('assets/js/feather.min.js')}}"></script>

    

    <script src="{{asset_with_env('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

    

    

    

    <script src="{{asset_with_env('assets/js/script.js')}}"></script>



    <script src="{{asset_with_env('plugins/captcha/longbow.slidercaptcha.min.js')}}"></script>

    <script src="{{asset_with_env('plugins/notify/notify.js')}}"></script>

    <script src="{{asset_with_env('plugins/notify/prettify.js')}}"></script>



    <script src="{{asset_with_env('plugins/confirm/jquery-confirm.min.js')}}"></script>

    <script src="{{asset_with_env('plugins/holdOn/HoldOn.min.js')}}"></script>



    <script src="{{asset_with_env('assets/plugins/select2/js/select2.min.js')}}"></script>



    <script src="{{asset_with_env('plugins/datatable/jquery.dataTables.min.js')}}"></script>

    

    <script src="{{asset_with_env('js/jquery-ui.js')}}"></script>

    

    <script src="{{asset_with_env('plugins/dropzone/dropzone.min.js')}}"></script>

    





    <script src="{{asset_with_env('plugins/calendar-view/index.global.js')}}"></script>





    <script src="{{asset_with_env('plugins/clock/js/jquery.flipTimer.js')}}"></script>





    <script>

        var holdon_option = {

                        theme:"sk-cube-grid",

                        message:'Processing your action please wait',

                        textColor:"white"

                    };



                    $.ajaxSetup({

                    headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                    }

                });





    </script>







    @yield("scripts")





</body>







