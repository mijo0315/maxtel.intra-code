<div class="col-xl-12 col-sm-12 col-12 front_tab" id="profilling">
    <!-- <div class="col-xl-12 col-sm-12 col-12">
        <div class="card profile_card division" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Division <i onclick="show_profile_body('division')" style="float:right; cursor: pointer;" id="division-ico" class="fas fa-caret-square-down fa-lg profile-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                <div class="col-xl-5 col-sm-6 col-6 ">
                        
                     
                          
                            @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-division=''
                            data-schedule_id='0'
                            data-is_active='1'
                            data-target='#division_modal'
                            >Add Division</a>
                            @endif
                      
                        
                        
                    </div>
                    
                    <div class="col-xl-12 col-sm-12 col-12 ">
                        
                    
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="division_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Code</th>
                                                    <th >Name</th>
                                                    <th >Date Created</th>
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
    </div> -->
    <div class="col-xl-12 col-sm-12 col-12 mt-0">
        <div class="card profile_card department" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Department <i onclick="show_profile_body('department')" style="float:right; cursor: pointer;" id="department-ico" class="fas fa-caret-square-down fa-lg profile-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                   
                    
                    <div class="col-xl-5 col-sm-6 col-6 ">
                        
                       
                          
                            @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-dept_div = ''
                            data-department=''
                            data-schedule_id='0'
                            data-is_active='1'
                            data-target='#department_modal'
                            >Add Department</a>
                            @endif
                        
                        
                        
                    </div>
                    
                    <div class="col-xl-12 col-sm-12 col-12 ">
                       
                    
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="department_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Code</th>
                                                    <!-- <th >Division</th> -->
                                                    <th >Department</th>
                                                    <th >Date Created</th>
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
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card profile_card branch" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Site<i onclick="show_profile_body('branch')" style="float:right; cursor: pointer;" id="branch-ico" class="fas fa-caret-square-down fa-lg profile-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                  
                    <div class="col-xl-7 col-sm-6 col-6 ">
                        
                       
                          
                            @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-branch=''
                            data-schedule_id='0'
                            data-is_active='1'
                            data-target='#branch_modal'
                            >Add Site</a>
                            @endif
                       
                        
                        
                    </div>
                    
                    <div class="col-xl-12 col-sm-12 col-12 ">
                       
                    
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="branch_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Code</th>
                                                    <th >Name</th>
                                                    <th >Date Created</th>
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
<!-- Button trigger modal -->
  
  <!-- Modal  DIVISION --> 
  <div class="modal fade" id="division_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Division Details</h5>
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
                    <input type="text" class="form-control" name="div_code" id="div_code" placeholder="Division Code">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="div_name" id="div_name" placeholder="Division Name">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Schedule</label>
                </div>
                <div class="col-md-8">
                    <select name="div_sched" id="div_sched" style="width:100%" class="form-control form-select">
                        <option value="0">Select Schedule</option>
                        @foreach($lib_week_schedule as $schedule)
                            <option value="{{$schedule['id']}}">{{$schedule['name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Status</label>
                </div>
                <div class="col-md-8">
                    <select name="div_is_active" id="div_is_active" style="width:100%" class="form-control form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                     
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_division" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal  DEPARTMENT --> 
  <div class="modal fade" id="department_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Department Details</h5>
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
                    <input type="text" class="form-control" name="dept_code" id="dept_code" placeholder="Department Code">
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-md-4">
                    <label for="">Tagged Division</label>
                </div>
                <div class="col-md-8">
                    <select name="dept_div" id="dept_div" style="width:100%" class="form-control form-select">
                        <option value="0">Select Division</option>
                        @foreach($division as $schedule)
                            <option value="{{$schedule['id']}}">{{$schedule['division']}}</option>
                        @endforeach
                    </select>
                </div>
            </div> -->
            <div class="row">
                <div class="col-md-4">
                    <label for="">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="dept_name" id="dept_name" placeholder="Department Name">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Schedule</label>
                </div>
                <div class="col-md-8">
                    <select name="dept_sched" id="dept_sched" style="width:100%" class="form-control form-select">
                        <option value="0">Select Schedule</option>
                        @foreach($lib_week_schedule as $schedule)
                            <option value="{{$schedule['id']}}">{{$schedule['name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Status</label>
                </div>
                <div class="col-md-8">
                    <select name="dept_is_active" id="dept_is_active" style="width:100%" class="form-control form-select">
                        <option value="1">Active</option>
                        <option value="0">Inative</option>
                     
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_department" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>
    <!-- Modal  BRANCH --> 
    <div class="modal fade" id="branch_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Site Details</h5>
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
                        <input type="text" class="form-control" name="branch_code" id="branch_code" placeholder="Site Code">
                    </div>
    
    
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="">Name</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="branch_name" id="branch_name" placeholder="Site Name">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-4">
                        <label for="">Schedule</label>
                    </div>
                    <div class="col-md-8">
                        <select name="branch_sched" id="branch_sched" style="width:100%" class="form-control form-select">
                            <option value="0">Select Schedule</option>
                            @foreach($lib_week_schedule as $schedule)
                                <option value="{{$schedule['id']}}">{{$schedule['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-4">
                        <label for="">Status</label>
                    </div>
                    <div class="col-md-8">
                        <select name="branch_is_active" id="branch_is_active" style="width:100%" class="form-control form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                         
                        </select>
                    </div>
                </div>
    
    
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
              <button type="button" id="save_branch" class="btn btn-success btn-sm">Save changes</button>
            </div>
          </div>
        </div>
      </div>