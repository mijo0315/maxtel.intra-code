<div class="col-xl-12 col-sm-12 col-12 front_tab" id="oth_library">
    <div class="col-xl-12 col-sm-12 col-12 ">
        <div class="card oth_income_card oth_library" >
            <div class="card-header" style="background-color: #2f47ba;">
                <h2 class="card-titles" style="color: white;">Library <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="fas fa-caret-square-down fa-lg oth_library-ico"></i></h2>
            </div>
            <div class="card-body">
                <div class="row">
                  
                    <div class="col-xl-8 col-sm-6 col-6 ">
                        
                       
                          
                            @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                            <a class="btn btn-apply"
                            data-toggle='modal' 
                            data-id='new'
                            data-code=''
                            data-tax_type='NON'
                            data-tax_item='0'
                            data-is_active='1'
                            data-target='#oth_library_modal'
                            >Add Other Income File</a>
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
                                                    <th >Tax Type</th>
                                                    <th >Tax Item</th>
                                                    
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
