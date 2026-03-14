@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Payroll Management
@stop
@section("styles")
<!-- Include Daterangepicker CSS -->
<link rel="stylesheet" type="text/css" href="{{ asset_with_env('assets/css/daterangepicker.css')}}" />
<style>
    .image-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .gallery-item {
        width: 200px;
        text-align: center;
    }
    .gallery-item img {
        width: 100%;
        height: auto;
        border-radius: 8px;
    }
    .gallery-item p {
        font-size: 14px;
        margin-top: 8px;
    }
    .pagination {
        text-align: center;
        margin-top: 20px;
    }
    .pagination button {
        padding: 10px 20px;
        margin: 0 5px;
        background-color: #2f47ba;
        color: white;
        border: none;
        cursor: pointer;
    }
    .pagination button:disabled {
        background-color: grey;
    }
    
    /* Loading indicator styles */
    .export-loading {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    
    .export-loading.active {
        display: flex;
    }
    
    .loading-box {
        background: white;
        padding: 40px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    
    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #2f47ba;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .progress-bar-container {
        width: 300px;
        height: 25px;
        background-color: #f0f0f0;
        border-radius: 5px;
        margin: 20px auto;
        overflow: hidden;
        border: 1px solid #ddd;
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #2f47ba, #1e2e7a);
        width: 0%;
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
</style>
@stop
@section("content")
@if(preg_match("/R/i", Auth::user()->access[Route::current()->action["as"]]["access"])=="0")
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-xl-12 col-sm-12 col-12 mb-4">
                    <div class="row">
                        <div class="col-xl-10 col-sm-8 col-12 ">
                            <label >YOU HAVE NO PRIVILEGE ON THIS PAGE </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
<div class="page-wrapper" id="face_time_audit_page">
    <div class="content container-fluid">
        <div class="col-xl-12 col-sm-12 col-12">
            <div class="card oth_income_card oth_library">
                <div class="card-header" style="background-color: #2f47ba;">
                    <h2 class="card-titles" style="color: white;">Face and Time Audit <i style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
                </div>
                
                <div class="row">
                    <div class="col-xl-12 col-sm-12 col-12">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <select id="emp_list" class="form-control form-select w-100">
                                        <option value="0">Select Employee</option>
                                        @foreach($tbl_employee as $emp)
                                            <option value="{{$emp->emp_code}}">{{$emp->emp_code}} - {{$emp->last_name}}, {{$emp->first_name}} {{$emp->middle_name}} {{$emp->ext_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div><br>
                            <div class="row" id="datepicker_div">
                                <div class="col-md-4">
                                    <input type="text" id="date_range" class="form-control" placeholder="Select Date Range" />
                                </div>
                                <div class="col-md-2">
                                    <button id="export-btn" class="btn btn-primary w-100">Export to Excel</button>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <small class="text-muted">Note: Leave employee blank to export all employees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xl-12 col-sm-12 col-12">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12 col-sm-12 col-12">
                                    <div id="image-gallery" class="image-gallery">
                                        <!-- Gallery Items will be dynamically loaded here -->
                                    </div>

                                    <div id="pagination" class="pagination">
                                        <button id="prev-btn" disabled>Prev</button>
                                        <button id="next-btn">Next</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="export-loading" class="export-loading">
    <div class="loading-box">
        <div class="spinner"></div>
        <h3>Preparing Your Export</h3>
        <p id="export-status">Initializing...</p>
        <div class="progress-bar-container">
            <div id="progress-bar" class="progress-bar" style="width: 0%">
                <span id="progress-text">0%</span>
            </div>
        </div>
        <p style="font-size: 12px; color: #666; margin-top: 10px;">This may take a few minutes. Please don't close this window.</p>
    </div>
</div>
@endif
@stop

@section("scripts")
<!-- Include Moment.js -->
<script src="{{ asset_with_env('assets/js/moment.min.js')}}"></script>
<!-- Include Daterangepicker JS -->
<script src="{{ asset_with_env('assets/js/daterangepicker.min.js')}}"></script>
<script>
    $(document).ready(function() {
        var user_count = {{ count($tbl_employee) }};
        var curr_id = @json($tbl_employee[0]->emp_code ?? null);

        $('#emp_list').select2({ width: '100%' });

        if (user_count === 1 && curr_id !== null) {
           
            if ($('#emp_list option[value="' + curr_id + '"]').length) {
                $('#emp_list').val(curr_id).trigger('change');
            } else {
                console.warn('No emp_list found', curr_id);
            }
        }
        var currentPage = 1;
        var totalImages = 0;
        var imagesPerPage = 20;

        // Initialize the daterangepicker
		$('#date_range').daterangepicker({
			locale: {
				format: 'YYYY-MM-DD',
			},
			autoUpdateInput: false,
		});

		// Update table when the user selects a date range
		$('#date_range').on('apply.daterangepicker', function (ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
			loadFaceTimeAuditImages();
		});
		//cancel date range
		$('#date_range').on('cancel.daterangepicker', function () {
			$(this).val('');
			loadFaceTimeAuditImages();
		});

        function loadFaceTimeAuditImages() {
            var empId = $("#emp_list").val();
            
            $.ajax({
                url: "{{ route('load_face_time_audit_tbl') }}",
                type: "POST",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}", 
                    "emp_id": empId,
                    "date_range": $('#date_range').val(),
                    "page": currentPage,
                    "limit": imagesPerPage
                },
                success: function(response) {
                    totalImages = response.total_images; // Assuming total_images is returned in the response
                    renderGallery(response.images);
                    updatePaginationButtons();
                }
            });
        }

        function renderGallery(images) {
            if(images){
                var galleryHtml = '';
                images.forEach(function(image) {
                    var locText = image.loc ? image.loc : "No Data";
                    galleryHtml += `
                        <div class="gallery-item">
                            <img src="https://maxtel-face.intra-code.com/${image.image}" alt="${image.name}">
                            <p>${image.state} <br> ${image.created_at} <br> ${locText}</p>
                        </div>
                    `;
                });
                $('#image-gallery').html(galleryHtml);
            }
        }

        function updatePaginationButtons() {
            $("#prev-btn").prop("disabled", currentPage === 1);
            $("#next-btn").prop("disabled", currentPage * imagesPerPage >= totalImages);
        }

        $("#emp_list").on("change", function() {
            currentPage = 1; // Reset to first page when employee changes
            loadFaceTimeAuditImages();
        });

        $("#next-btn").on("click", function() {
            if (currentPage * imagesPerPage < totalImages) {
                currentPage++;
                loadFaceTimeAuditImages();
            }
        });

        $("#prev-btn").on("click", function() {
            if (currentPage > 1) {
                currentPage--;
                loadFaceTimeAuditImages();
            }
        });

        $("#export-btn").on("click", function() {
            var empId = $("#emp_list").val();
            var dateRange = $('#date_range').val();
            
            // Default to '0' (all employees) if no selection
            if (!empId) {
                empId = '0';
            }

            // Create a form and submit to trigger download
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('export_face_time_audit') }}";
            
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = "{{ csrf_token() }}";
            form.appendChild(tokenInput);
            
            var empIdInput = document.createElement('input');
            empIdInput.type = 'hidden';
            empIdInput.name = 'emp_id';
            empIdInput.value = empId;
            form.appendChild(empIdInput);
            
            if (dateRange) {
                var dateRangeInput = document.createElement('input');
                dateRangeInput.type = 'hidden';
                dateRangeInput.name = 'date_range';
                dateRangeInput.value = dateRange;
                form.appendChild(dateRangeInput);
            }
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });

        loadFaceTimeAuditImages(); // Initial load
    });
</script>
@stop