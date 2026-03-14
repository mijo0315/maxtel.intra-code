<div class="modal fade" id="uploadEmployeeDeductionOneTime" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('uploadEmployeeDeductionOneTime') }}" enctype="multipart/form-data">
        


      @csrf
     
      <input type="hidden" name="pay_id_upload_deduction" id="pay_id_upload_deduction">
      <div class="modal-content">
        <div class="modal-header">
         <input type="hidden" id="upload_income_id" name ="upload_income_id">
          <h5 class="modal-title" id="uploadModalLabel">Upload Deduction Excel File</h5>
          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="excelFile" class="form-label">Choose XLSX File</label>
            <input class="form-control" type="file" name="excel" accept=".xlsx" required>
          </div>
          <a href="{{ asset('public/deduction_format.xlsx') }}" class="btn btn-link">📥 Download Format</a>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Upload</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>