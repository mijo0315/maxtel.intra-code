<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .page {
            margin-bottom: 10px;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .grid-table td {
            width: 50%;
            vertical-align: top;
            padding: 5px;
        }

        .payslip {
            min-height: 380px; /* Increased height */
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #000;
            overflow: visible; /* Allow full content */
            page-break-inside: avoid; /* Prevent mid-breaks */
        }

        .payslip img {
            width: 60px;
        }

        .payslip h1 {
            font-size: 11px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            font-size: 8.5px;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        th, td {
            border: 1px solid #999;
            padding: 2px;
            text-align: left;
        }

        .no-border, .no-border td {
            border: none;
            padding: 0;
        }

        .confidential {
            color: red;
            font-weight: bold;
        }

        .net-pay td {
            background-color: #B9D7EF;
        }

        .logo {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
@php
    $chunked = array_chunk($payslip_data, 4);
@endphp

@foreach($chunked as $chunkIndex => $chunk)
    <div class="page" @if($chunkIndex < count($chunked) - 1) style="page-break-after: always;" @endif>
        <table class="grid-table">
            @for ($i = 0; $i < 4; $i += 2)
                <tr>
                    @for ($j = 0; $j < 2; $j++)
                        @php
                            $index = $i + $j;
                        @endphp
                        <td>
                            @if (isset($chunk[$index]))
                                @php
                                    $data = $chunk[$index];
                                    $total_income = 0;
                                    $total_deduction = 0;
                                    $imagePath = 'https://maxtel.intra-code.com/public/upload_images/logo/logs.png';
                                    $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                                    $dataPath = file_get_contents($imagePath);
                                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataPath);
                                @endphp

                                <div class="payslip">
                                    <table class="no-border">
                                        <tr>
                                            <td><img src="{{ $base64 }}" class="logo" alt="Logo"></td>
                                            <td style="text-align:right;">{{ $data["branch"] }}</td>
                                        </tr>
                                    </table>

                                    <h1 style="text-align:center;">Payslip</h1>
                                    <p style="text-align:center;">{{ $period }}</p>

                                    <table class="no-border">
                                        <tr>
                                            <td><strong>NAME:</strong> {{ $data["last_name"] }}, {{ $data["first_name"] }} {{ $data["middle_name"] }} {{ $data["ext_name"] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>DEPARTMENT:</strong> {{ $data["department"] }}</td>
                                            <td class="confidential">CONFIDENTIAL</td>
                                        </tr>
                                    </table>

                                    <table>
                                        <thead>
                                            <tr>
                                                <th colspan="2">EARNINGS</th>
                                                <th colspan="2">DEDUCTIONS</th>
                                            </tr>
                                            <tr>
                                                <td><strong>Description</strong></td>
                                                <td><strong>Amount</strong></td>
                                                <td><strong>Description</strong></td>
                                                <td><strong>Amount</strong></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $maxRows = max(count($data["incomes"]), count($data["deductions"]));
                                            @endphp

                                            @for ($k = 0; $k < $maxRows; $k++)
                                                @php
                                                    $income = $data["incomes"][$k] ?? null;
                                                    $deduction = $data["deductions"][$k] ?? null;

                                                    $hasIncome = $income && $income["amount"] > 0;
                                                    $hasDeduction = $deduction && $deduction["amount"] > 0;

                                                    if ($hasIncome) {
                                                        $total_income += $income["amount"];
                                                    }
                                                    if ($hasDeduction) {
                                                        $total_deduction += $deduction["amount"];
                                                    }
                                                @endphp

                                                @if ($hasIncome || $hasDeduction)
                                                    <tr>
                                                        <td>{{ $hasIncome ? $income["income_name"] : '' }}</td>
                                                        <td>{{ $hasIncome ? number_format($income["amount"], 2) : '' }}</td>
                                                        <td>{{ $hasDeduction ? $deduction["deduction_name"] : '' }}</td>
                                                        <td>{{ $hasDeduction ? number_format($deduction["amount"], 2) : '' }}</td>
                                                    </tr>
                                                @endif
                                            @endfor
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td><strong>Total Earnings</strong></td>
                                                <td>{{ number_format($total_income, 2) }}</td>
                                                <td><strong>Total Deductions</strong></td>
                                                <td>{{ number_format($total_deduction, 2) }}</td>
                                            </tr>
                                            <tr class="net-pay">
                                                <td colspan="2"><strong>Net Pay</strong></td>
                                                <td colspan="2" style="text-align:right;"><strong>{{ number_format($total_income - $total_deduction, 2) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
@endforeach
</body>
</html>
