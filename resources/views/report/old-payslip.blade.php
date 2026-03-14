<!DOCTYPE html>
<html>
<head>
    <title>PAYSLIP</title>
</head>
<body>
    <table style="width:100%; border-style:none;">
        <?php foreach($payslip_data as $data){
                $total_income = 0;
                $total_deduction = 0;
            ?>
            <tr>
                <td> <img src="<?php echo 'data:image/png;base64,' . base64_encode(file_get_contents(@$company_info["logo_main"])); ?>" style="width:100px; height:auto;" > </td>
                <td align="left" colspan="3"> <big><strong> -- PAYSLIP -- </strong></big>
                </td>
            </tr>
            <tr>
                <td> <big><strong> <?php echo $company_info["company_name"]; ?> </strong></big>
                </td>
            </tr>
            <tr>
                <td >
                    <?php echo $company_info["address"]; ?>
                </td>
            </tr>
            <tr>
                <td>Name:</td>
                <td colspan="3"> <?php echo $data["employee_code"] ?>  - <?php echo $data["last_name"] ?>, <?php echo $data["first_name"] ?> <?php echo $data["middle_name"] ?>. <?php echo $data["ext_name"] ?> </td>
            </tr>
            <tr>
                <td colspan="2" align="center"><strong>INCOMES</strong></td>
                <td colspan="2" align="center"><strong>DEDUCTIONS</strong></td>
            </tr>
            {{-- INCOMES --}}
            <tr>
                <td colspan="2" valign="top">
                    <table style="width:100%; ">
                                <?php foreach($data["incomes"] as $inc){
                                    if($inc["amount"] <= 0){
                                        continue;
                                    }   
                                    $total_income += $inc["amount"];
                                    ?>
                                <tr> 
                                    <td> <?php echo $inc["income_name"] ?> </td>
                                    <td align="left"> <?php echo number_format($inc["amount"],2) ?> </td>
                                </tr>
                                <?php } ?>
                            
                        
                    </table>
                </td>
                {{-- DEDUCTIONS --}}
                <td colspan="2" valign="top">
                    <table style="width:100%; ">
                                <?php foreach($data["deductions"] as $ded){ 
                                    if($ded["amount"] <= 0){
                                        continue;
                                    } 
                                    
                                    $total_deduction += $ded["amount"];
                                ?>
                                <tr>   
                                    <td > <?php echo $ded["deduction_name"] ?> </td>
                                    <td align="right"> <?php echo number_format($ded["amount"],2) ?> </td>
                                </tr>
                                <?php } ?>
                            
                        
                    </table>
                </td>
            </tr>
            <tr>
                <td> TOTAL:</td>
                <td align="left"> <?php echo number_format($total_income,2) ?> </td>
                <td></td>
                <td align="right"> 
                    <?php echo number_format($total_deduction,2) ?>
                </td>
            </tr>
            <tr>
                <td> </td>
                <td align="left"> </td>
                <td> NET PAY:</td>
                <td align="right"> 
                    <big> <strong> 
                    <?php echo number_format($total_income - $total_deduction,2) ?>
                    </strong></big>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="right">
                 
                    _____________________________ <br>
                    Signature
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <hr style="width:100%; border-style:dashed;">
                </td>
            </tr>
        <?php
        } //EMPLOYEE
    ?>
    </table>
    
</body>
</html>