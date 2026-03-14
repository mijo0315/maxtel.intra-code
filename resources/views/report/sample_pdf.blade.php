<!DOCTYPE html>
<html>
<head>
    <title>PDF Document</title>
</head>
<body>
    <?php
        foreach($payslip_data as $data){
            echo $data["last_name"];


        }

    ?>

    <h1>PDF Content</h1>
    <p>This is the content of the PDF document.</p>
</body>
</html>