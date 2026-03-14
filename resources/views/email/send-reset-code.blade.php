<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style type="text/css">
        body {
            font-family: Arial;
        }
        a {
            text-decoration: none;
        }
        .orange-text {
            color: #f37934 !important;
        }
        .orange-background {
            background: #ebc634 !important;
            color: #fff !important;
            padding: 1px 25px;
            border: 1px solid #d5d5d5;
        }
        .img-logo {
            max-width: 400px;
            margin: auto;
        }
        .signature {
            font-weight: bold;
        }
        .signature-logo {
            max-width: 250px;
        }
        .text-center {
            text-align: center;
        }
        .link-btn {
            padding: 10px;
            background: #3cb5d0;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #3cb5d0;
        }
        .panel-body {
            border: 1px solid #d5d5d5;
            padding: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading orange-background text-center" style="border-radius: 10px 10px 0px 0px;"></div>
                <div class="panel-body">
                    <table style="width: 60%; margin: auto; border-collapse: collapse; border: 0; font-family: arial;">
                        <tr style="background:#6389FF; height: 100px;">
                            <td width="30%" style="text-align: center; vertical-align: middle;"><img src="https://maxtel.intra-code.com/public/upload_images/logo/logs.png" alt="IntraCode Logo" style="width: 80%" /></td>
                            <td style="text-align: center; vertical-align: middle; color: #fff; padding: 0">
                                <h1>MAXTEL PAYROLL SYSTEM</h1>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: 20px; background-color: #ededed;">
                               <h3 style="text-align:text-center !important;">Reset your password</h3>
                                <p>Hi @if(!empty($full_name)) {{$full_name}} @else user @endif,</p>
                                <p>We have received a request to recover your account password. To set a new password, simply click the link below.<br />
                                    <p><a href="https://maxtel.intra-code.com/password-reset/{{$reset_code}}?email={{ urlencode($email) }}">Reset Password</a></p>
                                    <p>If you didn’t ask to recover your password, please ignore this email. </p>
                                    <br>
                                    <p>
                                        Thanks and Best Regards
                                    </p><br>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
