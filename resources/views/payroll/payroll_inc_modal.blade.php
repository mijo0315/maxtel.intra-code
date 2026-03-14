<div class="modal fade" id="payroll_inc_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="label_modal">OTHER INCOME PAYROLL</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
            <input type="hidden" name='pay_inc_id' id="pay_inc_id"> 

            <div class="row">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Employee List</label>
                </div>
                <div class="col-md-5">
                    <select name="emp_list_inc" id="emp_list_inc" style="width:100%"  class="form-control form-select" multiple>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Other Income (One Time)</label>
                </div>
                <div class="col-md-5">
                    <select name="inc_one_time" id="inc_one_time" style="width:100%"  class="form-control form-select">
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label for="" class="btn btn-info btn-sm" style="float: right;">Amount</label>
                </div>
                <div class="col-md-5">
                    <input name="inc_amount" id="inc_amount" style="width:100%" type="number" class="form-control" placeholder="Amount" >
                </div>
            </div>

            <div class="row" id="income_btn">
                <div class="col-md-3">

                </div>
                <div class="col-md-3 mt-1">
                    <button  type="button" id="inc_include" class="btn btn-success btn-md"> <i class="fas fa-plus-square"></i> Insert</button>
                </div>
            </div>

            <div class="row" id="custom_tbl" class="ml-1">
                <div class="col-md-12">

                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-xl-12 col-sm-12 col-12 table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="income_tbl">
                                    <thead>
                                        <tr>
                                            <th style="width:40%;">Name</th>
                                            <th style="width:20%;">Type</th>
                                            <th style="width:20%;">Amount</th>
                                            <th style="width:20%;">Action</th>
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