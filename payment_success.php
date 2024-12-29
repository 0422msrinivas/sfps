<?php
session_start();

// Include Razorpay SDK
require 'vendor/autoload.php';
use Razorpay\Api\Api;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// TCPDF Library
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

// Razorpay API Keys
$api_key = 'rzp_live_SMuGxiXppBaM4C'; // Replace with your Razorpay API Key
$api_secret = 'LARG46LmwVkMHA2TElcc8fN6'; // Replace with your Razorpay API Secret

$api = new Api($api_key, $api_secret);

// Directory to save receipts
$receipts_dir = __DIR__ . '/receipts/';

// Ensure the receipts directory exists, and create it if it doesn't
if (!is_dir($receipts_dir)) {
    mkdir($receipts_dir, 0777, true);
}

// Retrieve payment details from URL parameters
if (isset($_GET['payment_id']) && isset($_GET['order_id']) && isset($_GET['amount'])) {
    $payment_id = $_GET['payment_id'];
    $order_id = $_GET['order_id'];
    $amount = $_GET['amount']; // Amount passed via URL (in paise)

    // Convert amount from paise to INR (divide by 100)
    $amount_in_inr = $amount / 100;

    try {
        // Fetch the order details from Razorpay API
        $order = $api->order->fetch($order_id);

        // Verify that the payment ID matches the order
        if ($order['id'] === $order_id) {
            // Fetch the payment details
            $payment = $api->payment->fetch($payment_id);

            // Check if the payment is successful
            if ($payment['status'] === 'captured') {
                // Database connection
                $conn = new mysqli("localhost", "root", "", "sfps_db");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Assuming session contains student ID (alphanumeric like 3br21ai108)
                $user_id = $_SESSION['id_no'];

                // Fetch student details from the database using the user_id
                $stmt = $conn->prepare("SELECT email, name FROM student WHERE id_no = ?");
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $stmt->store_result();
                
                // Fetch student details if found
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($student_email, $student_name);
                    $stmt->fetch();
                    
                    // Prepare the SQL statement to insert data into the payments1 table
                    $stmt_insert = $conn->prepare("INSERT INTO payments1 (user_id, payment_id, order_id, amount) VALUES (?, ?, ?, ?)");
                    $stmt_insert->bind_param("sssi", $user_id, $payment_id, $order_id, $amount_in_inr);

                    // Execute the statement to insert payment record
                    if ($stmt_insert->execute()) {
                        // Generate the receipt content (HTML format)
                        $receipt_html = "
                            <html>
                            <head>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        padding: 20px;
                                        background-color: #f9f9f9;
                                    }
                                    .receipt-container {
                                        width: 60%;
                                        margin: auto;
                                        padding: 10px;
                                        border: 1px solid #ccc;
                                        background-color: white;
                                        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
                                    }
                                    h1 {
                                        text-align: center;
                                        color: #333;
                                    }
                                    table {
                                        width: 100%;
                                        border-collapse: collapse;
                                        margin-top: 20px;
                                    }
                                    table, th, td {
                                        border: 1px solid #ddd;
                                    }
                                    th, td {
                                        padding: 10px;
                                        text-align: left;
                                    }
                                    .btn-print {
                                        display: block;
                                        margin: 20px auto;
                                        padding: 10px 20px;
                                        background-color: #4CAF50;
                                        color: white;
                                        border: none;
                                        cursor: pointer;
                                        font-size: 16px;
                                    }
                                    .btn-print:hover {
                                        background-color: #45a049;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='receipt-container'>
                                    <h1>Payment Receipt</h1>
                                    <p><strong>Payment ID:</strong> $payment_id</p>
                                    <p><strong>Order ID:</strong> $order_id</p>
                                    <p><strong>Amount:</strong> ₹$amount_in_inr</p>
                                    <p><strong>Payment Status:</strong> Successful</p>
                                    <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                                    <table>
                                        <tr>
                                            <th>Amount Paid</th>
                                            <td>₹$amount_in_inr</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Status</th>
                                            <td>Successful</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date</th>
                                            <td>" . date('Y-m-d H:i:s') . "</td>
                                        </tr>
                                    </table>
                                </div>
                            </body>
                            </html>
                        ";

                        // Generate the PDF receipt using TCPDF
                        $pdf = new TCPDF();
                        $pdf->AddPage();
                        $pdf->SetFont('helvetica', '', 12);
                        $pdf->writeHTML($receipt_html);

                        // Path to save the PDF
                        $pdf_file = $receipts_dir . 'pay_' . $payment_id . '_receipt.pdf';
                        $pdf->Output($pdf_file, 'F'); // Save the PDF file

                        // Send the receipt via email
                        $mail = new PHPMailer(true);
                        try {
                            // Server settings
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = '0422msrinivasa@gmail.com';  // SMTP username
                            $mail->Password = 'zmhi qqvy aepo hiny';        // SMTP password
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;

                            // Recipients
                            $mail->setFrom('0422msrinivasa@gmail.com', 'School Fee Payment');
                            $mail->addAddress($student_email, $student_name);
                            $mail->addAttachment($pdf_file); // Attach the generated PDF receipt

                            // Email content
                            $mail->isHTML(true);
                            $mail->Subject = 'Payment Receipt - Order # ' . $order_id;
                            $mail->Body = $receipt_html; // Include receipt details in the email body

                            // Send the email
                            $mail->send();
                            echo 'Receipt has been sent to the student email.';
                        } catch (Exception $e) {
                            echo "Error sending email: {$mail->ErrorInfo}";
                        }
                    } else {
                        echo "Failed to store payment details: " . $stmt_insert->error;
                    }

                    // Close the insert statement
                    $stmt_insert->close();
                } else {
                    echo "Student not found!";
                }

                // Close the statement and connection
                $stmt->close();
                $conn->close();
            } else {
                echo "Payment verification failed. Status: " . $payment['status'];
            }
        } else {
            echo "Order ID mismatch!";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Missing payment ID, order ID, or amount.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go to Student Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="btn" onclick="window.location.href='student_page.php'">Go to Student Page</button>
</body>
</html>
