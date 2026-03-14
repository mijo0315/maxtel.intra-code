<div class="col-xl-12 col-sm-12 col-12 front_tab" id="oth_library">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card oth_income_card oth_library" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Payroll Management <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                    
                    
                  
                   <div class="col-xl-12 col-sm-12 col-12 mt-2">
                       @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                           <div class="card-title">
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-name=''
                            data-target_month='{{strtoupper(date("M"))}}'
                            data-target_year='{{date("Y")}}'
                            
                            data-date_start='{{date("Y-m-01")}}'
                            data-type_info = '1'
                            data-date_end='{{date("Y-m-d")}}'
                            data-process_type='RP'
                            data-payroll_type='SEMI'
                            data-oth_income=''
                            data-lib_loan=''
                            data-payroll_status='1'
                            data-gsis='0'
                            data-sss='0'
                            data-ph='0'
                            data-hdmf='0'
                            
                            data-target='#payroll_modal'
                            >
                                
                                    Generate Payroll</a>
                            </div>
								  @endif
                    </div>
                    <div class="col-xl-8 col-sm-6 col-6 ">
                        
                     
                        
                    </div>
                  
                    <div class="col-xl-12 col-sm-12 col-12 ">
                       
                    
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="payroll_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Code</th>
                                                    <th >Name</th>
                                                    <th >Info</th>
                                                    <th >Coverage</th>
                                                    <th >Status</th>
                                                   
                                                    
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
<div class="modal fade" id="payroll_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Payroll Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Code</label>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="payroll_code" id="payroll_code" placeholder="Payroll Code">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="payroll_name" id="payroll_name" placeholder="Payroll Name">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Payroll Month</label>
                </div>
                <div class="col-md-4">
                    <select type="text" class="form-control form-select" style="width:40%" name="target_month" id="target_month">
                        <option value="JAN">January</option>
                        <option value="FEB">February</option>
                        <option value="MAR">March</option>
                        <option value="APR">April</option>
                        <option value="MAY">May</option>
                        <option value="JUN">June</option>
                        <option value="JUL">July</option>
                        <option value="AUG">August</option>
                        <option value="SEP">September</option>
                        <option value="OCT">October</option>
                        <option value="NOV">November</option>
                        <option value="DEC">December</option>
                    </select>
                    
                    <select type="text" class="form-control form-select" style="width:40%" name="target_year" id="target_year">
                        @php
                            $cur_year = date("Y");
                            $cur_year -= 3;
                        @endphp
                        @for($x = 0; $x <= 20; $x++)
                        <option value="{{$cur_year + $x}}">{{$cur_year + $x}}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                   
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Date Start</label>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Period From" value='{{date('Y-m-d')}}'>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Date End</label>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" id="end_date" name="end_date" placeholder="Period To" value='{{date('Y-m-d')}}'>
                </div>
            </div>
            <div class="row mt-2">
                
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm">Process Type</label>
                </div>
                <div class="col-md-5">
                    <select name="process_type" id="process_type" style="width:100%" class="form-control form-select">
                            <option value="RP">REGULAR PAYROLL</option>
                            <option value="13">13TH MONTH PAYROLL</option>
                            <option value="BP">BONUS PAYROLL</option>
                            <option value="SP">SPECIAL PAYROLL</option>
                            <option value="LC">LEAVE CREDITS PAYROLL</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm">Payroll Type</label>
                </div>
                <div class="col-md-5">
                    <select name="payroll_type" id="payroll_type" style="width:100%" class="form-control form-select">
                        <option value="WEEKLY">Weekly</option>
                        <option value="SEMI">Semi - Monthly</option>
                        <option value="MONTHLY">Monthly</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm"> Payroll Group</label>
                </div>
                <div class="col-md-5">
                    <select name="hr_group" class="form-control form-select" id="hr_group" style="width:100%">
                        <option value="group_a">GROUP A</option>
                        <option value="group_b">GROUP B</option>
                        <option value="group_c">GROUP C</option>
                        <option value="group_d">GROUP D</option>
                        <option value="group_e">GROUP E</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm">Type Info</label>
                </div>
                <div class="col-md-5">
                    <select name="type_info" id="type_info" style="width:100%" class="form-control form-select">
                        <option value="0">Select Type Info</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm">Other Income Included</label>
                </div>
                <div class="col-md-5">
                    <select name="reg_oth_inc" id="reg_oth_inc" style="width:100%" class="form-control form-select" multiple>
                            @foreach($lib_income as $inc)
                            <option value="{{$inc['id']}}">{{$inc['code']}} - {{$inc['name']}}</option>
                            @endforeach
                    </select>
                </div> 
            </div>
            <div class="row mt-2">
                
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm">Loan Included</label>
                </div>
                <div class="col-md-5">
                    <select name="lib_loan" id="lib_loan" style="width:100%" class="form-control form-select" multiple>
                            @foreach($lib_loan as $loan)
                            <option value="{{$loan['id']}}">{{$loan['code']}} - {{$loan['name']}}</option>
                            @endforeach
                    </select>
                </div> 
            </div>
            <div class="row mt-2">
                
                <div class="col-md-3">
                    <label for="" style="float: right;" class="btn btn-info btn-sm">Statutory</label>
                </div>
                @if(Auth::user()->company['is_government'])
                    <div class="col-md-6">
                        <input type="checkbox" id="gsis" value="gsis" class="statutory" style=" width: 30px; height: 30px;" > <label for="gsis"> <img src="{{asset_with_env('assets/img/statutory/gsis.png')}}" style="width:80px; height:auto; cursor: pointer;" /> </label>
                @else
                    <div class="col-md-6">
                        <input type="checkbox" id="sss" value="sss" class="statutory"  style=" width: 30px; height: 30px;" > <label for="sss"> <img src="{{asset_with_env('assets/img/statutory/sss.png')}}" style="width:80px; height:auto; cursor: pointer;" /> </label>
                @endif
                <input type="checkbox" id="ph" value="ph" class="statutory"  style="  width: 30px; height: 30px;" > <label for="ph"> <img src="{{asset_with_env('assets/img/statutory/ph.png')}}" style="width:80px; height:auto; cursor: pointer;" /> </label>
                <input type="checkbox" id="hdmf" value="hdmf" class="statutory"  style=" width: 30px; height: 30px;" > <label for="hdmf"> <img src="{{asset_with_env('assets/img/statutory/hdmf.jpg')}}" style="width:80px; height:auto; cursor: pointer;" /> </label>
                </div> 
            </div>
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_payroll" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>