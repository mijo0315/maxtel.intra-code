<div class="modal fade" id="credit_adj_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Credit Adjustment</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">X</button>
            </div>

            <div class="modal-body">

                <!-- FORM -->
                <div class="row">
                    <div class="col-md-6">
                        <select id="ca_emp_id" class="form-control form-select" style="width:100%;"></select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" id="ca_amount" class="form-control" placeholder="Amount">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success" onclick="save_credit_adj()">Save</button>
                    </div>
                </div>

                <hr>

                <!-- TABLE -->
                <table class="table table-bordered" id="credit_adj_tbl">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>

            </div>

        </div>
    </div>
</div>