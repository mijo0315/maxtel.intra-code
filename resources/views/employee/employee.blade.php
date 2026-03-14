<div class="col-xl-12 col-sm-12 col-12 front_tab" id="employee_list">



     <div class="col-xl-12 col-sm-12 col-12 ">

        <div class="card oth_income_card oth_library" >

            <div class="card-header" style="background-color: #2f47ba;">

                <h2 class="card-titles" style="color: white;">Employee List <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>

            </div>





    <div class="row">

        <div class="col-xl-12 col-sm-12 col-12 ">

    
        @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
            <div class="m-3 d-flex align-items-center flex-wrap gap-3">
                <a onclick="emp_view('new')" class="btn btn-apply btn-md" data-toggle="modal" data-mode="new">
                    Add Employee
                </a>

            </div>
          
          @endif

          

          

        </div>			

        

    </div>

    <div class="row mt-4">
        <!-- employee upload -->
        <div class="col-12">
            <form id="uploadEmployeeForm" enctype="multipart/form-data">
                @csrf
                <input type="file" name="employee_excel_file" id="employee_excel_file" accept=".xlsx, .xls" style="margin-left:20px;">
                <button type="submit" class="btn btn-apply btn-md">   Upload Employee</button>
                <a href="{{ route('employee.export') }}" class="btn btn-apply btn-md">
                    Export to Excel
                </a>
            </form>
        </div>	
        
    </div>





    @if(Auth::user()->company['version'] == 1)

    <div class="row">

    <div class="col-xl-12 col-sm-12 col-12 ">

           

              

                 

               

                    <div class="col-md-3">

                            <label class="employee-count-label"><strong>{{$employee_count}} Employee(s)</strong></label>

                               



                            </div>

                

          

        </div>



    </div>

    @endif



    

    <div class="row">

        <div class="col-xl-12 col-sm-12 col-12">

           

        

                <div class="card-body">

                    <div class="row">

                        

                        <div class="col-xl-12 col-sm-12 col-12 table-responsive ">

                            <table class="table table-striped table-bordered table-hover" id="tbl_employee_list">

                                <thead>

                                    <tr>

                                        <th >Name</th>

                                        <th >Position</th>

                                        <th >Department</th>

                                        <th >Site</th>

                                        <th >Designation</th>

                                        <th>Action</th>

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