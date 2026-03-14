<div class="col-xl-12 col-sm-12 col-12 stat_tab" id="scheduling_tab">
   
    <div class="row">
 
        <div class="col-xl-12 col-sm-12 col-12 mb-4">
            <div class="head-link-set">
                <ul>
                   
                    <!-- hide tab & select ** add id="sub_sched_by_employee_li"-->
                    <li id="sub_sched_by_employee_li"><a class="sub_mnu" id="sub_sched_by_employee"  onclick="">By Employee</a></li>
                    <li id="sub_sched_by_position_li" ><a  class="active sub_mnu" id="sub_sched_by_position"  onclick="">By Position</a></li>
                    <li id="sub_sched_by_designation_li"><a class="sub_mnu" id="sub_sched_by_designation"  onclick="">By Designation</a></li>
                    <li id="sub_sched_by_department_li"><a class="sub_mnu" id="sub_sched_by_department"  onclick="">By Department</a></li>
                    <li id="sub_sched_by_branch_li"><a class="sub_mnu" id="sub_sched_by_branch"  onclick="">By Branch</a></li>
                   
    
    
                </ul>
            </div>
        </div>
       
    
    </div>
    @include("timekeeping.sched_position")
    @include("timekeeping.sched_department")
    @include("timekeeping.sched_branch")
    @include("timekeeping.sched_designation")
    @include("timekeeping.sched_employee")
    
</div>
