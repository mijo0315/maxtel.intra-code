<div class="modal fade" id="uploadEmployeeOtherIncome" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('uploadEmployeeOtherIncome') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
            <input type="hidden" name="pay_id_upload_income" id="pay_id_upload_income">
          <h5 class="modal-title" id="uploadModalLabel">Upload Income Excel File</h5>
          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="excelFile" class="form-label">Choose XLSX File</label>
            <input class="form-control" type="file" name="excel" accept=".xlsx" required>
          </div>
          <a href="{{ asset('public/other_income_format.xlsx') }}" class="btn btn-link">📥 Download Format</a>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Upload</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>