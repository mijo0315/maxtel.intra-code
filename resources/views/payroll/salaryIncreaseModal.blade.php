<div class="modal fade" id="salaryIncreaseModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Salary Increase
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>


            <div class="modal-body">

                <input type="hidden" id="salary_pay_id">


                <div class="form-group">
                    <label>
                        Daily Increase Amount
                    </label>

                    <input 
                        type="number" 
                        class="form-control"
                        id="salary_amount"
                        value="0"
                        required>
                        
                </div>


                <div class="form-group">
                    <label>
                        From
                    </label>

                    <input 
                        type="date"
                        class="form-control"
                        id="salary_from"
                        required>
                </div>


                <div class="form-group">
                    <label>
                        To
                    </label>

                    <input 
                        type="date"
                        class="form-control"
                        id="salary_to"
                        required>
                </div>


            </div>


            <div class="modal-footer">

                <button 
                    class="btn btn-success"
                    onclick="save_salary_increase()">
                    Save
                </button>

            </div>

        </div>
    </div>
</div>