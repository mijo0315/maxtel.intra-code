<div class="col-xl-12 col-sm-12 col-12 front_tab" id="oth_library">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card oth_income_card oth_library" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Allowances & Incentives<i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                    
                    @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] != "employee" && Auth::user()->role_id != 15)
					<div class="col-xl-12 col-sm-12 col-12 mt-2">
                          @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                           <div class="card-title">
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-tax_type='NON'
                            data-is_regular='1'
                            data-tax_item='0'
                            data-is_active='1'
                            data-target='#oth_library_modal'
                            >
                                
                                   Create Allowances & Incentives</a>
                            </div>
								  @endif
                    </div>
                  
                  
                     
                  
                    <div class="col-xl-12 col-sm-12 col-12 ">
                        
                    
                          
                    
                       
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="oth_library_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Code</th>
                                                    <th >Name</th>
                                                    <th >Regular</th>
                                                    <th >Tax Type</th>
                                                    {{-- <th >Tax Item</th> --}}
                                                    
                                                    {{-- <th >Date Created</th> --}}
                                                    <th >Status</th>
                                                   
                                                    
                                                    <th >Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
            
            
                                    </div>
                                    
                                   
                                </div>
                                
                            
                       
                    </div><hr>
                @endif
               
					<div class="col-xl-12 col-sm-12 col-12 mt-2">
                          @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                           <div class="card-title">
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-emp_id=''
                            data-amount=''
                            data-status=''
                            data-date_filed=''
                            data-remarks=''
                            data-target='#allowance_request_modal'
                            >
                                
                                   Request Operational Allowance</a>
                            </div>
								  @endif
                    </div>
                  
                  
                     
                  
                    <div class="col-xl-12 col-sm-12 col-12 ">
                        
                    
                          
                    
                       
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="allowance_request_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Name</th>  
                                                    <th >Amount</th>
                                                    <th >Date Filed</th>
                                                    <th >Remarks</th>
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
<div class="modal fade" id="add_edit_employee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"  role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="label_modal">ADD/EDIT EMPLOYEE</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
            <div class="row">
                <div class="col-md-12">
                    
                    
                    {{-- //STOPPED ON AMOUNT THINKING --}}
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-outline-primary " id="all_emp_label"> 
                                    <input class="btn-check select_employee"  type="radio" name="select_employee" id="all_emp" value="all_emp"> All Employee
                                </label>
                                <label class="btn btn-outline-primary ml-1" id="daily_emp_label">
                                    <input class="btn-check select_employee" type="radio" name="select_employee" id="daily_emp" value="daily_emp" > All Daily Employee
                                </label>
                                <label class="btn btn-outline-primary ml-1" id="monthly_emp_label">
                                    <input class="btn-check select_employee" type="radio" name="select_employee" id="monthly_emp" value="monthly_emp" > All Monthly Employee
                                </label>
                                <label class="btn btn-outline-primary ml-1" id="custom_emp_label">
                                    <input class="btn-check select_employee" type="radio" name="select_employee" id="custom_emp" value="custom_emp" >Select Employee
                                </label>
                            </div>
                              
                            
                </div>
              
            </div>
            <div class="row" id="selected_div">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Select Employee</label>
                </div>
                <div class="col-md-8">
                    <select name="selected_employee" style="width:100%" id="selected_employee" class="form-control form-select" multiple>
                    </select>
                </div>
            </div>
            <div class="row" >
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Income Type</label>
                </div>
                <div class="col-md-5">
                    <select name="income_type" style="width:100%" id="income_type" class="form-control form-select">
                        <option value="0"> Select Income Type</option>
                        <option value="DAILY">Daily</option>
                        <option value="WEEKLY">Weekly</option>
                        <option value="SEMI">Semi - Monthly</option>
                        <option value="MONTHLY">Monthly</option>
                        
                    </select>
                </div>
               
            </div>
            <div class="row" id="amount_div">
                <div class="col-md-3">
                    <label for="" id="amount_label_1" class="btn btn-info btn-sm" style="float: right;">Amount</label>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control" name="amount_val" id="amount_val" value="0" >
                </div>
            </div>
            <div class="row" id="amount_div2">
                <div class="col-md-3">
                    <label for="" id="amount_label_2" class="btn btn-info btn-sm" style="float: right;">Amount</label>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control" name="amount_val_2" id="amount_val_2" value="0" >
                </div>
            </div>
            <div class="row" id="amount_div3">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Week 3 Amount</label>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control" name="amount_val_3" id="amount_val_3" value="0" >
                </div>
            </div>
            <div class="row" id="amount_div4">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Week 4 Amount</label>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control" name="amount_val_4" id="amount_val_4" value="0" >
                </div>
            </div>
            <div class="row" id="amount_div5">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Week 5 Amount</label>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control" name="amount_val_5" id="amount_val_5" value="0" >
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                </div>
                <div class="col-md-3 mt-1">
                    <button  type="button" id="include" class="btn btn-success btn-md"> <i class="fas fa-plus-square"></i> Include</button>
                </div>
            </div>
            <div class="row" id="custom_tbl" class="ml-1">
                <div class="col-md-12">
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="income_file">
                                    <thead>
                                        <tr>
                                         
                                            <th >Name</th>
                                            <th >Income Type</th>
                                            <th >Amount</th>
                                            {{-- <th >Tax Item</th> --}}
                                            
                                            {{-- <th >Date Created</th> --}}
                                            {{-- <th >Status</th> --}}
                                           
                                            
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
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_oth_income" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>
<div class="modal fade" id="oth_library_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Allowances & Incentives Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="">Code</label>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="lib_code" id="lib_code" placeholder="Allowance & Incentive Code">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="lib_name" id="lib_name" placeholder="Allowance & Incentive Name">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label for="">Description</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="lib_desc" id="lib_desc" placeholder="Allowance & Incentive Description">
                </div>
            </div>
            <div class="row mt-2">
                
                <div class="col-md-4">
                    <label for="">Income Type</label>
                </div>
                <div class="col-md-8">
                    <select name="lib_is_regular" id="lib_is_regular" style="width:100%" class="form-control form-select">
                        <option value="1">Regular Income (Every Payroll Process) </option>
                        <option value="0">One Time (Set Per Payroll Process)</option>
                     
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                
                    <div class="col-md-4">
                        <label for="">Tax Type</label>
                    </div>
                    <div class="col-md-8">
                        <select name="lib_tax_type" id="lib_tax_type" style="width:100%" class="form-control form-select">
                            <option value="NON">NON TAXABLE</option>
                            <option value="TAX">TAXABLE</option>
                         
                        </select>
                    </div>
            </div>
            <div class="row mt-2">
                
                <div class="col-md-4">
                    <label for="">Tax Item</label>
                </div>
                <div class="col-md-8" id="tax_item_NON">
                    <select name="lib_tax_item_NON" id="lib_tax_item_NON" style="width:100%" class="form-control form-select">
                        <option value="0">Select Tax Item</option>
                        @foreach($lib_bir_non_taxable as $non_item)
                            <option value="{{$non_item["id"]}}">{{$non_item["name"]}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8" id="tax_item_TAX">
                    <select name="lib_tax_item_TAX" id="lib_tax_item_TAX" style="width:100%" class="form-control form-select">
                        <option value="0">Select Tax Item</option>
                        @foreach($lib_bir_taxable as $tax_item)
                            <option value="{{$tax_item["id"]}}">{{$tax_item["name"]}}</option>
                        @endforeach
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
<!-- request Allowance modal -->
  <div class="modal fade" id="allowance_request_modal" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="requestModalLabel">Allowance Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Select Employee</label>
				</div>
                <div class="col-md-8">
					<select id="allowance_emp_name" class="form-control form-select" style="width:100%;">
                    </select>
				</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label class="btn btn-sm btn-info w-100">Amount</label>
                </div>
                <div class="col-md-8">
                    <input type="number" class="form-control" name="amount" id="amount" placeholder="Amount">
                </div>
            </div>
            <div class="row mt-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Date Filed</label>
				</div>
				<div class="col-md-8">
					<input type="date" id="date_filed" name="date_filed" class="form-control" placeholder="Date Filed">
				</div>
			</div>
            <div class="row mt-2">
				<div class="col-md-4">
					<label class="btn btn-sm btn-info w-100">Remarks</label>
				</div>
				<div class="col-md-8">
					<textarea id="remarks" name="remarks" class="form-control" cols="10" rows="3" placeholder="Remarks"></textarea>
				</div>
			</div>
            <div class="row mt-2">
                
                <div class="col-md-4">
                    <label class="btn btn-sm btn-info w-100">Status</label>
                </div>
                <div class="col-md-8">
                    <select name="status" id="status" style="width:100%" class="form-control form-select">
                        <option value="">select status</option>
                        <option value="FILED">FILED</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="REJECT">REJECT</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_allowance_request" class="btn btn-success btn-sm">Submit</button>
        </div>
      </div>
    </div>
  </div>
  <!-- request Allowance modal end -->