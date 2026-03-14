<div class="col-xl-12 col-sm-12 col-12 front_tab" id="oth_library">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card oth_income_card oth_library" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Cash Advance<i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                @if(Auth::user()->company['version'] == 1)
                        <!-- update loan -->
                        @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] == "employee" && Auth::user()->role_id != 11)
                        <div class="col-xl-12 col-sm-12 col-12">
                            <a class='btn btn-apply' 
                                data-toggle='modal' 
                                data-id='Auth::id()'
                                data-file_id='new'
                                data-emp_id='0'
                                data-pay_type='0'
                                data-total_amount=''
                                data-amount_to_pay=''
                                data-date_start=''
                                data-date_to=''
                                data-notes=''
    
                                data-target='#add_edit_employee'
                                
                                > Apply Cash Advance </a>
                        </div>
                        <div class="col-xl-12 col-sm-12 col-12 ">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                            <input type="hidden" id="emp_id" value="{{ Auth::id() }}">
                                            <table class="table table-striped table-bordered table-hover" id="emp_tbl">
                                                <thead>
                                                    <tr>
                                                        <th >Cash Advance Type</th>
                                                        <th >Principal <br>Cash Advance</th>
                                                        <th >Deduction <br> Amount</th>
                                                        <th >Balance</th>
                                                        <th >Type</th>
                                                        <th >Note</th>
                                                        <th >Status</th>
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
                        @endif
                    @endif
                    <!-- update loan -->
                    @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] != "employee" || Auth::user()->role_id == 11)
                        @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] != "employee")
                            <div class="col-xl-12 col-sm-12 col-12 mt-2">
                                <div class="card-title">
                                    <a class="btn btn-apply"
                                    data-toggle='modal' 
                                    data-id='new'
                                    data-code=''
                                    data-name=''
                                    data-description=''
                                    data-type='SSS_SL'
                                    data-is_active='1'
                                    data-is_regular='1'
                                    data-target='#loan_lib_modal'
                                >
                                    
                                    Create Cash Advance</a>
                                </div>
                                    
                            </div>
                            @endif
                  
                    
                            <div class="col-xl-12 col-sm-12 col-12 ">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="loan_tbl">
                                                <thead>
                                                    <tr>
                                                        <th >Code</th>
                                                        <th >Name</th>
                                                        <th >Type</th>
                                                        <th >Description</th>
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
                        @endif
                    @endif
</div>
        </div>
    </div>
</div>
<div class="modal fade" id="add_edit_employee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="width:100%;">
      <div class="modal-content" >
        <div class="modal-header">
            <!-- update loan -->
            @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] != "employee" || Auth::user()->role_id == 11)
                <h5 class="modal-title" id="label_modal">ADD/EDIT EMPLOYEE</h5>
            @else
            <h5 class="modal-title" id="label_modal">CASH ADVANCE APPLICATION</h5>
            @endif
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
            <!-- update loan -->
            <input type="hidden" name="user_type" id="user_type" value="{{ Auth::user()->access[Route::current()->action['as']]['user_type'] }}">
            <div class="row">
                <div class="col-md-3">
                    <!-- update loan -->
                    @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] != "employee" || Auth::user()->role_id == 11)
                        <input type="hidden" name="loan_id" id="loan_id">
                        <label for="" class="btn btn-info btn-sm" style="float: right;">Select Employee</label>
                    @else
                        <label for="" class="btn btn-info btn-sm" style="float: right;">Employee Name</label>
                    @endif
                </div>
                <div class="col-md-8">
                    <select name="selected_employee" style="width:100%" id="selected_employee" class="form-control form-select">
                    </select>
                </div>
            </div>
            <!-- update loan -->
            @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] == "employee" && Auth::user()->role_id != 11)
            <div class="row">
                <div class="col-md-3">
                        <label for="" class="btn btn-info btn-sm" style="float: right;">Cash Advance Type</label>
                </div>
                <div class="col-md-8">
                    <select name="loan_id" style="width:100%" id="loan_id" class="form-control form-select">
                        <option value="">Select Cash Advance Type</option>
                        @foreach($loan_type as $type)
                            <option value="{{$type->id}}">{{$type->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
            <div class="row" >
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Payment Type</label>
                </div>
                <div class="col-md-5">
                    <select name="payment_type" style="width:100%" id="payment_type" class="form-control form-select">
                        <option value="0"> Select Payment Type</option>
                        <option value="FULL">Full (One Time)</option>
                        <option value="PARTIAL">Installment</option>
                    </select>
                </div>
               
            </div>
            <div class="row" id="variance">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Deduction Often</label>
                </div>
                <div class="col-md-5">
                    <select name="payment_variance" style="width:100%" id="payment_variance" class="form-control form-select">
                        <option value="WEEKLY">WEEKLY</option>
                        <option value="MONTHLY">MONTHLY</option>
                        <option value="SEMI">SEMI MONTHLY (TWICE A MONTH)</option>
                    </select>
                </div>
               
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Principal Amount</label>
                </div>
                <div class="col-md-8">
                    <input type="number" class="form-control" id="principal_amount" name="principal_amount" placeholder="Principal Amount">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Deduction Amount</label>
                </div>
                <div class="col-md-8">
                    <input type="number" class="form-control" id="deduction_amount" name="deduction_amount" placeholder="Deduction Amount">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Balance</label>
                </div>
                <div class="col-md-8">
                    <input type="number" class="form-control" id="balance_amount" name="balance_amount" placeholder="Balance Amount">
                </div>
            </div>
            <!-- <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Date Start</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start date" value='{{date('Y-m-d')}}'>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Date End</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End date" value='{{date('Y-m-d')}}'>
                </div>
            </div> -->
            <div class="row mt-2" id="loan_div">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Loan Status</label>
                </div>
                <div class="col-md-5">
                    <select name="loan_status"  style="width:100%" id="loan_status" class="form-control form-select">
                        <option value="0">Pending</option>
                        <option value="1">Approved</option>
                        <option value="2">Denied</option>
                        <option value="3">Pause</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Notes</label>
                </div>
                <div class="col-md-8">
                    <textarea name="notes" id="notes" cols="30" rows="2" class="form-control" placeholder="Notes"></textarea>
                </div>
            </div>
           
            <div class="row mt-2">
                <div class="col-md-3">
                    
                </div>
                <div class="col-md-8">
                    <button type="button" class="btn btn-success btn-sm" id="add_edit_btn" name="add_edit_btn" >Save/Update</button>
                    <button type="button" class="btn btn-danger btn-sm" id="delete_btn" name="delete_btn" >Delete</button>
                    <button type="button" class="btn btn-warning btn-sm" id="cancel_btn" name="cancel_btn" >Cancel</button>
                    
                </div>
            </div>
             <!-- update loan -->
             @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] != "employee" || Auth::user()->role_id == 11)
                @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="add_edit_tbl">
                                    <thead>
                                        <tr>
                                            <th >Name</th>
                                            <th >Principal <br>Cash Advance</th>
                                            <th >Deduction <br> Amount</th>
                                            <th >Balance</th>
                                            <th >Type</th>
                                            <th >Note</th>
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
                @endif
            @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="loan_lib_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Cash Advance Library Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="">Cash Advance Type</label>
                </div>
                <div class="col-md-8">
                        <select name="loan_type" id="loan_type" class="form-control form-select"  style="width:100%">
                            
                            @foreach($lib_loan_type as $loan_type)
                                <option value='{{$loan_type["code"]}}'>{{$loan_type["name"]}}</option>
                            @endforeach
                        </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Cash Advance Code</label>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="lib_code" id="lib_code" placeholder="Cash Advance Code">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="lib_name" id="lib_name" placeholder="Cash Advance Name">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Description</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="lib_desc" id="lib_desc" placeholder="Cash Advance Description">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Deduction Type</label>
                </div>
                <div class="col-md-8">
                    <select name="is_regular" id="is_regular" class="form-control form-select" style="width:80%;">
                        <option value="1">Regular Deduction (Every Payroll Process) </option>
                        <option value="0">One Time Deduction (Set Per Payroll Process)</option>
                        
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Status</label>
                </div>
                <div class="col-md-8">
                    <select name="lib_is_active" id="lib_is_active" style="width:100%" class="form-control form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                     
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_library" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>