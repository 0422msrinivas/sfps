<?php
session_start();

// Include Razorpay SDK
require 'vendor/autoload.php';

use Razorpay\Api\Api;

// Razorpay API Keys
$api_key = 'rzp_live_SMuGxiXppBaM4C'; // Replace with your Razorpay API Key
$api_secret = 'LARG46LmwVkMHA2TElcc8fN6'; // Replace with your Razorpay API Secret

$api = new Api($api_key, $api_secret);

// Retrieve student details from the session or database
if (isset($_SESSION['id_no'])) {
    $student_id = $_SESSION['id_no'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "sfps_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch total fee for the student
    $fee_query = "SELECT sef.total_fee, s.name, s.email, s.contact 
                  FROM student_ef_list sef 
                  JOIN student s ON sef.student_id = s.id 
                  WHERE s.id = (SELECT id FROM student WHERE id_no = '$student_id')";
    $fee_result = $conn->query($fee_query);

    if ($fee_result && $fee_result->num_rows > 0) {
        $fee = $fee_result->fetch_assoc();
        $amount = $fee['total_fee'] * 100; // Razorpay expects amount in paise
        $student_name = $fee['name'];
        $student_email = $fee['email'];
        $student_contact = $fee['contact'];
    } else {
        echo "Fee details not found.";
        exit;
    }
} else {
    echo "User not logged in.";
    exit;
}

// Create Razorpay Order
$orderData = [
    'receipt' => 'rcptid_' . uniqid(),
    'amount' => $amount, // Amount in paise
    'currency' => 'INR',
    'payment_capture' => 1 // Auto-capture payment
];

$order = $api->order->create($orderData);

// Pass the order details to the frontend
$order_id = $order['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with Razorpay</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <button id="rzp-button">Pay Now</button>
    <script>
        const options = {
            "key": "<?php echo $api_key; ?>", // Razorpay API Key
            "amount": "<?php echo $amount; ?>", // Amount in paise
            "currency": "INR",
            "name": "<?php echo $student_name; ?>",
            "description": "Fee Payment",
            "image": "https://your-logo-url.com/logo.png", // Change to your logo
            "order_id": "<?php echo $order_id; ?>",
            "handler": function (response) {
                // Handle successful payment
                window.location.href = `payment_success.php?payment_id=${response.razorpay_payment_id}&order_id=${response.razorpay_order_id}&amount=<?php echo $amount; ?>`;
            },
            "prefill": {
                "name": "<?php echo $student_name; ?>",
                "email": "<?php echo $student_email; ?>",
                "contact": "<?php echo $student_contact; ?>"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        const rzp = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function (e) {
            rzp.open();
            e.preventDefault();
        };
    </script>
</body>
</html>
