<?php

session_start(); // Start the session if not already started

// Assuming these variables contain the payment details.
$student_name = "John Doe"; // Example student name
$student_email = "john.doe@example.com"; // Example student email
$amount_paid = 500.00; // Example amount paid
$payment_date = date("Y-m-d H:i:s"); // Payment date
$payment_id = "PAY123456"; // Example payment ID
$order_id = "ORD123456"; // Example order ID

// HTML content for the receipt
$receipt_html = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
            }
            .receipt-container {
                width: 60%;
                margin: auto;
                padding: 10px;
                border: 1px solid #ccc;
                background-color: #f9f9f9;
            }
            h1 {
                text-align: center;
                color: #333;
            }
            .receipt-details {
                margin-top: 20px;
            }
            .receipt-details td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
        </style>
    </head>
    <body>
        <div class='receipt-container'>
            <h1>Payment Receipt</h1>
            <p><strong>Student Name:</strong> $student_name</p>
            <p><strong>Email:</strong> $student_email</p>
            <p><strong>Amount Paid:</strong> ₹$amount_paid</p>
            <p><strong>Payment Date:</strong> $payment_date</p>
            <p><strong>Payment ID:</strong> $payment_id</p>
            <p><strong>Order ID:</strong> $order_id</p>

            <table class='receipt-details'>
                <tr>
                    <td><strong>Amount Paid:</strong></td>
                    <td>₹$amount_paid</td>
                </tr>
                <tr>
                    <td><strong>Payment Status:</strong></td>
                    <td>Successful</td>
                </tr>
                <tr>
                    <td><strong>Payment Date:</strong></td>
                    <td>$payment_date</td>
                </tr>
            </table>

            <div style='text-align: center; margin-top: 20px;'>
                <button onclick='window.print()'>Print Receipt</button>
            </div>
        </div>
    </body>
    </html>
";

// Output the receipt HTML
echo $receipt_html;

?>
