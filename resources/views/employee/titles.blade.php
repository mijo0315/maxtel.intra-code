<div class="col-xl-12 col-sm-12 col-12 front_tab" id="titles">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card titles_card position" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Position <i onclick="show_titles_body('position')" style="float:right; cursor: pointer;" id="position-ico" class="fas fa-caret-square-down fa-lg titles-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                    
                    <div class="col-xl-3 col-sm-6 col-6 ">
                        
                    
                          
                            @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-name=''
                            data-rf='RF'
                            data-is_active='1'
                            data-target='#position_modal'
                            >Add Position</a>
                            @endif
                      
                        
                        
                    </div>
                    
                    <div class="col-xl-12 col-sm-12 col-12 ">
                       
                    
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="position_tbl">
                                            <thead>
                                                <tr>
                                                    <th >Code</th>
                                                    <th >Name</th>
                                                    <th >Type</th>
                                                    
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
        <div class="card titles_card designation" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Designation <i onclick="show_titles_body('designation')" style="float:right; cursor: pointer;" id="designation-ico" class="fas fa-caret-square-down fa-lg titles-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                  
                    <div class="col-xl-7 col-sm-6 col-6 ">
                        
                     
                          
                            @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-name=''
                            data-is_active='1'
                            data-target='#designation_modal'
                            >Add Designation</a>
                            @endif
                       
                        
                        
                    </div>
                    
                    <div class="col-xl-12 col-sm-12 col-12 ">
                       
                    
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="designation_tbl">
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
{{-- MODAL POSITION --}}
<div class="modal fade" id="position_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Position Details</h5>
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
                    <input type="text" class="form-control" name="pos_code" id="pos_code" placeholder="Position Code">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="pos_name" id="pos_name" placeholder="Position Name">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Position Type</label>
                </div>
                <div class="col-md-8">
                    <select name="pos_type" id="pos_type" style="width:100%" class="form-control form-select">
                        <option value="RF">Rank and File</option>
                        <option value="SM">Supervisory / Managerial</option>
                        <option value="EX">Executives</option>
                        <option value="ST">Specialized / Technical Roles</option>
                        <option value="AD">Administrative</option>
                        <option value="FC">Freelance / Consulting</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Status</label>
                </div>
                <div class="col-md-8">
                    <select name="pos_is_active" id="pos_is_active" style="width:100%" class="form-control form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                     
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_position" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>
  
  {{-- MODAL POSITION --}}
<div class="modal fade" id="designation_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Designation Details</h5>
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
                    <input type="text" class="form-control" name="des_code" id="des_code" placeholder="Designation Code">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Name</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="des_name" id="des_name" placeholder="Designation Name">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="">Status</label>
                </div>
                <div class="col-md-8">
                    <select name="des_is_active" id="des_is_active" style="width:100%" class="form-control form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                     
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          <button type="button" id="save_designation" class="btn btn-success btn-sm">Save changes</button>
        </div>
      </div>
    </div>
  </div>