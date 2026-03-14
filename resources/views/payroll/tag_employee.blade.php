<div class="modal fade" id="tag_employee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="label_modal">ADD/EDIT EMPLOYEE</h5> 
          <!-- <a class='btn btn-success btn-md ml-3' data-toggle='modal' data-target='#payroll_inc_modal' data-emp_id='all' id='add_oth_inc' > <i class='fas fa-plus-circle'></i> Other Income </a>
          <a class='btn btn-danger btn-md ml-3' data-toggle='modal' data-target='#payroll_ded_modal' data-emp_id='all' id='add_oth_ded' > <i class='fas fa-minus-circle'></i> Other Deduction </a> -->
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
            <input type="hidden" name='pay_id' id="pay_id"> 

            <div class="row">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Filter</label>
                </div>
                <div class="col-md-5">
                    <select name="filter" style="width:100%" id="filter" class="form-control form-select">
                    </select>
                </div>
            </div>


            <div class="row" id="select_employee_div">
                <div class="col-md-3">
                    <label for="" id="select_label" class="btn btn-info btn-sm" style="float: right;">Select Tagging</label>
                </div>
                <div class="col-md-8" id="select_data_div">
                    <select name="select_data" style="width:100%" id="select_data" class="form-control form-select">
                    </select>

                </div>

                <div class="col-md-8" id="select_emp_div">
                    <select name="select_emp" style="width:100%" id="select_emp" class="form-control form-select" multiple>
                    </select>
                </div>
               
            </div>




            <div class="row" id="inc_btn">
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
                                <table class="table table-striped table-bordered table-hover" id="tagged_employee">
                                    <thead>
                                        <tr>
                                            <th style="width:50%;">Name</th>
                                            <th style="width:20%;">Basic Pay</th>
                                            <th style="width:30%;">Action</th>
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
        </div>
      </div>
    </div>
</div>