
<div class="modal fade" id="salaryIncreaseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl salary-modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    WO#27 - Salary Increase
                </h5>
                <button type="button" class="close text-white"
                        data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="salary_pay_id">

                <!-- Employee Selection -->
                <div class="form-group">
                    <label for="salary_employee_ids">
                        <strong>Employees</strong>
                    </label>

                    <div class="mb-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                id="salary_select_all">
                            <i class="fa fa-check-square"></i> Select All
                        </button>

                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="salary_clear_all">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>

                    <select id="salary_employee_ids"
                            class="form-control"
                            multiple="multiple"
                            style="width:100%;"
                            data-placeholder="Search or select employees">
                    </select>

                    <small class="form-text text-muted">
                        Select all employees, multiple employees, or
                        individual employees.
                    </small>
                </div>

                <hr>

                <!-- Salary Increase Details -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="salary_amount">
                                Daily Increase Amount
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₱</span>
                                </div>
                                <input type="number"
                                       class="form-control"
                                       id="salary_amount"
                                       min="0.01"
                                       step="0.01"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="salary_from">From</label>
                            <input type="date"
                                   class="form-control"
                                   id="salary_from">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="salary_to">To</label>
                            <input type="date"
                                   class="form-control"
                                   id="salary_to">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button"
                            class="btn btn-success"
                            id="btn_save_salary_increase"
                            onclick="save_salary_increase()">
                        <i class="fa fa-save"></i>
                        Apply Salary Increase
                    </button>
                </div>

                <hr>

                <!-- Employee Salary Increase Table -->
                <div class="salary-table-container">
                    <h5 class="mb-3">
                        Salary Increase Employee List
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover"
                               id="salary_increase_tbl"
                               style="width:100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee No.</th>
                                    <th>Employee Name</th>
                                    <th>Salary Type</th>
                                    <th>Salary Increase Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>