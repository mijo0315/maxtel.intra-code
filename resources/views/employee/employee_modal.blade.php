<div class="page-wrapper" id="add_emp_page">
    <div class="content container-fluid">
  
    <div class="mt-2">
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 ">
                <div class="card ">
                 
                    <div class="card-header" style="background-color: #2f47ba;">
<h2 class="card-titles" style="color: white;">Employee Information <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
</div>
                    <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class='emp_label'> Employee Photo</label>
                                </div>
                                <div class="col-md-6">
                                <!-- remove pic -->
                                    <img src="{{ asset_with_env(str_replace('public/', '', Auth::user()->company["linked_employee"]["profile_picture"])) }}" id="profile_pic" onerror="this.src='{{ asset_with_env('upload_images/emp_pic/avatar-user.jpg')}}'; " alt="logo" style="max-height: 10vh; margin-right:5px;" />
                                        <input type="file" id="emp_file" name="emp_img" accept="image/*" />
                                    
                                       
                                      
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label' > Face Recognition ID Number (Number Only)</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="bio_id" placeholder="Face Recognition ID Number">
                                </div>
                              <div class="col-md-2">
                                    <label class='emp_label' > Company ID Number (Number Only)</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="emp_code" placeholder="Company ID Number">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label' > Last Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="last_name" placeholder="Last Name">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'> First Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="first_name" placeholder="First Name">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'> Middle Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="middle_name" placeholder="Middle Name">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'> Extension Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="ext_name" placeholder="Extension Name">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'> Contact Number</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="contact_no" placeholder="Contact Number">
                                </div>
                              
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-1">
                                    <label class='emp_label'> SSS No.</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="sss_no" placeholder="SSS Number">
                                </div>
                                <div class="col-md-1">
                                    <label class='emp_label'> PhilHealth No.</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="philhealth_no" placeholder="PhilHealth Number">
                                </div>
                                
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-1">
                                    <label class='emp_label'> HDMF No.</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="hdmf_no" placeholder="HDMF Number">
                                </div>
                                <div class="col-md-1">
                                    <label class='emp_label'> TIN No.</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="tin_no" placeholder="TIN Number">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-1">
                                    <label class='emp_label'> Position</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="position">
                                        <option value="0">Select Position</option>
                                        @foreach($position as $pos)
                                        <option value="{{$pos['id']}}">{{$pos['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class='emp_label'> Department</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="department">
                                        <option value="0">Select Department</option>
                                        @foreach($department as $dept)
                                        <option value="{{$dept['id']}}">{{$dept['department']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                           
                            <div class="row mt-2">
                                <div class="col-md-1">
                                    <label class='emp_label'> Site</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="branch">
                                        <option value="0">Select Site</option>
                                        @foreach($branch as $bran)
                                            <option value="{{$bran['id']}}">{{$bran['branch']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class='emp_label'> Designation</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="designation">
                                            <option value="0">Select Designation</option>
                                        @foreach($designation as $desig)
                                            <option value="{{$desig['id']}}">{{$desig['name']}}</option>
                                        @endforeach
                                    </select> 
                                </div>
                            </div>
                         
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'> Hiring Type</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="hiring_type">
                                        <option value="1">Direct Hiring</option>
                                        <option value="0">In direct Hiring</option>
                                        
                                    </select>
                                </div>
                                <div class="col-md-7 agency_div">
                                    <div class="row">
                                        <div class="col-md-3 ">
                                            <label class='emp_label'> Agency name</label>
                                        </div>
        
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="agency_name" placeholder="Agency Name">
                                        </div>
                                    </div>
                                   
                                </div>
                               
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'>Start Date</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>

                                <div class="col-md-2">
                                    <label class='emp_label'>Date of Birth</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class='emp_label'> Address</label>
                                </div>
                                <div class="col-md-10">
                                    <textarea name="address" id="address" rows="2" class="form-control" placeholder="Address"></textarea>
                                </div>
                                
                               
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-0">
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 ">
                <div class="card">
                   
                    <div class="card-header" style="background-color: #2f47ba;">
<h2 class="card-titles" style="color: white;">Salary Information <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
</div>
                    <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="emp_label"> Salary Type</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="salary_type">
                                        <option value="DAILY">DAILY</option>
                                        <option value="MONTHLY">MONTHLY</option>
                                        
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="emp_label"> Salary Rate</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="salary_rate" placeholder="Salary Rate">
                                </div>
                            </div>
                        
                            <div class="row mt-2" id="yearly_divisor_div">
                                <div class="col-md-2">
                                    <label class="emp_label"> Yearly Divisor</label>
                                </div>
                                 <div class="col-md-3" >
                                    <input type="number" class="form-control yearly_divisor" id="yearly_divisor" placeholder="Yearly Divisor">
                                </div>
                                 <div class="col-md-2">
                                    <label class="emp_label"> Allowance</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="allowance" placeholder="Allowance">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class="emp_label"> Minimum Wage Earner</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="is_mwe">
                                        <option value="1">YES</option>
                                        <option value="0">NO</option>
                                        
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="emp_label"> HR Group</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="hr_group">
                                        <option value="group_a">GROUP A</option>
                                        <option value="group_b">GROUP B</option>
                                        <option value="group_c">GROUP C</option>
                                        <option value="group_d">GROUP D</option>
                                        <option value="group_e">GROUP E</option>
                                    </select>
                                </div>
                                
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class="emp_label"> Fix Divisor</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="fix_divisor" placeholder="Fix Divisor">
                                </div>
                                <div class="col-md-2">
                                    <label class="emp_label"> Fix HDMF</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="fix_hdmf" placeholder="Fix HDMF">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class="emp_label"> Fix SSS</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="fix_sss" placeholder="Fix SSS">
                                </div>
                                <div class="col-md-2">
                                    <label class="emp_label"> Fix PhilHealth</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="fix_philhealth" placeholder="Fix PhilHealth">
                                </div>
                            </div>
                           
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class="emp_label"> Fix Tax Rate</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control number" id="fix_rate" placeholder="Fix Rate">
                                </div>
                                <div class="col-md-2">
                                    <label class="emp_label"> Status</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Resign</option>
                                        
                                    </select>
                                </div>
                            </div>
                             <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class="emp_label"> Employee Status</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="employee_status">
                                        <option value="">Select Status</option>
                                        <option value="Probationary">Probationary</option>
                                        <option value="Trainee">Trainee</option>
                                        <option value="Project Employee">Project Employee</option>
                                        <option value="Regular">Regular</option>
                                    </select>
                                </div>
                            </div>
                         
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-0">
        <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 ">
                <div class="card">
                  
                 
                    <div class="card-header" style="background-color: #2f47ba;">
<h2 class="card-titles" style="color: white;">Account Information <i  style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>
</div>
                    <div class="card-body">
                            <div class="row">
                                <div class="col-md-1">
                                    <label class="emp_label"> User Name</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="email" class="form-control" id="user_name" placeholder="User Name" autocomplete="off">
                                </div>
                                <div class="col-md-1">
                                    <label class="emp_label"> Password</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="password" class="form-control" id="password" placeholder="Password" autocomplete="off" >
                                </div>
                                <div class="col-md-1">
                                    <label class="emp_label"> Role</label>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control form-select" id="role">
                                        <option value="0">Select Role</option>
                                        @foreach($role as $rol)
                                            <option value="{{$rol['id']}}">{{$rol['name']}}</option>
                                        @endforeach
                                    </select> 
                                </div>
                            </div>
                       
<br>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <button id="save_btn" value="new" class="btn btn-success btn-lg w-20">
                                        Save
                                    </button>
        
                                    <a  class='btn btn-lg btn-warning w-20' onclick = 'emp_view_close();' > Close </a>
                                </div>
                                
                            </div>
                         
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
