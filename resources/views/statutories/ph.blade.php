<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="ph_tab">

    

    <div class="row">

        <div class="col-xl-12 col-sm-12 col-12 ">

            <div class="card ">

                
				<div class="card-header" style="background-color: #2f47ba;">
                    <h2 class="card-titles" style="color: white;">PhilHealth Computation Table<i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
                </div>



                <div class="card-body">

                    <div class="row">

                        <div class="col-xl-12 col-sm-12 col-12 table-responsive-sm">

                            <table class="table table-striped table-bordered table-hover" id="ph_tbl">

                                <thead>

                                    <tr>

                                        <th>Salary From</th>

                                        <th>Salary To</th>

                                        <th>Employer Rate </th>

                                        <th>Employee Rate</th>

                                        <th>Effective Year</th>

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





<div class="modal fade" tabindex="-1" role="dialog" id="ph_modal">

	<div class="modal-dialog" role="document">

	  <div class="modal-content">

		<div class="modal-header">

		  <h5 class="modal-title">PhilHealth Update</h5>

		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">

			<span aria-hidden="true">&times;</span>

		  </button>

		</div>

		<div class="modal-body">

			<div class="row">

				<div class="col-md-4">

					<label for="code_lib" class="btn btn-sm btn-info w-100">Salary From</label>

				</div>

				<div class="col-md-8">

					<input type="number" class="form-control text-right" placeholder="Salary from" id="ph_salary_from">

				</div>

			</div>



            <div class="row">

				<div class="col-md-4">

					<label for="code_lib" class="btn btn-sm btn-info w-100">Salary To</label>

				</div>

				<div class="col-md-8">

					<input type="number" class="form-control text-right" placeholder="Salary to" id="ph_salary_to">

				</div>

			</div>



        



            <div class="row">

				<div class="col-md-4">

					<label for="code_lib" class="btn btn-sm btn-info w-100">Employer Rate</label>

				</div>

				<div class="col-md-8">

					<input type="number" class="form-control text-right" placeholder="Employer Rate" id="ph_com_share">

				</div>

			</div>



			<div class="row">

				<div class="col-md-4">

					<label for="code_lib" class="btn btn-sm btn-info w-100">Employee Rate</label>

				</div>

				<div class="col-md-8">

					<input type="number" class="form-control text-right" placeholder="Employee Rate" id="ph_emp_share">

				</div>

			</div>



            <div class="row">

				<div class="col-md-4">

					<label for="code_lib" class="btn btn-sm btn-info w-100 ">Effective Year</label>

				</div>

				<div class="col-md-8">

					<input type="number" class="form-control text-right" placeholder="year" id="ph_year">

				</div>

			</div>



		</div>

		<div class="modal-footer">

			

		  <button type="button" id="ph_update_btn" class="btn btn-success">Update</button>

		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

		</div>

	  </div>

	</div>

  </div>