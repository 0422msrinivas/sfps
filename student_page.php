<?php
session_start();

// Check if the student_id is set in the session
if (isset($_SESSION['id_no'])) {
    $student_id = $_SESSION['id_no'];  // Get the student_id from session
} else {
    // If not set, handle accordingly (e.g., show an error or redirect)
    echo "User not logged in.";
    exit;  // Terminate further script execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Details</title>
    <style>
        /* Add your CSS here */
        .container {
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f9;
            border-radius: 8px;
            width: 80%;
            margin: auto;
        }

        .header {
            text-align: center;
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        button {
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Student Fee Details</h2>
            <p><strong>User ID:</strong> <?php echo $student_id; ?></p> <!-- Display user ID here -->
        </div>

        <?php
        // Database connection
        $conn = new mysqli("localhost", "root", "", "sfps_db");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch student details based on id_no from student table
        $student_query = "SELECT * FROM student WHERE id_no = '$student_id'";
        $student_result = $conn->query($student_query);

        if ($student_result && $student_result->num_rows > 0) {
            $student = $student_result->fetch_assoc();
            echo "<p><strong>Name:</strong> {$student['name']}</p>";
            echo "<p><strong>Contact:</strong> {$student['contact']}</p>";
            echo "<p><strong>Email:</strong> {$student['email']}</p>";
            echo "<p><strong>Address:</strong> {$student['address']}</p>";
        } else {
            echo "<p>Student not found.</p>";
        }

        // Fetch total fee from student_ef_list by matching student.id with student_ef_list.student_id
        $fee_query = "SELECT sef.total_fee, c.course 
                      FROM student_ef_list sef
                      JOIN student s ON sef.student_id = s.id
                      JOIN courses c ON sef.course_id = c.id
                      WHERE s.id = (SELECT id FROM student WHERE id_no = '$student_id')";
        $fee_result = $conn->query($fee_query);

        if ($fee_result && $fee_result->num_rows > 0) {
            while ($fee = $fee_result->fetch_assoc()) {
                echo "<p><strong>Course:</strong> {$fee['course']}</p>";
                echo "<p><strong>Total Fee:</strong> {$fee['total_fee']}</p>";
            }
        } else {
            echo "<p>Fee details not found.</p>";
        }

        // Fetch payment history using the student_id (from session)
        $payment_query = "SELECT * FROM payments1 WHERE user_id = (SELECT id_no FROM student WHERE id_no = '$student_id')";
        $payment_result = $conn->query($payment_query);

        if ($payment_result && $payment_result->num_rows > 0) {
            echo "<h3>Payment History</h3>";
            echo "<table>";
            echo "<tr><th>Date</th><th>Amount</th><th>Remarks</th></tr>";
            while ($payment = $payment_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$payment['date_created']}</td>";
                echo "<td>{$payment['amount']}</td>";
                echo "<td>{$payment['remarks']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No payment history found.</p>";
        }

        // Debugging: Print number of rows returned by each query
        echo "<p>Student Result Count: " . $student_result->num_rows . "</p>";
        echo "<p>Fee Result Count: " . $fee_result->num_rows . "</p>";
        echo "<p>Payment Result Count: " . $payment_result->num_rows . "</p>";

        $conn->close();
        ?>

        <div class="actions">
           <button onclick="window.location.href='razorpay_payment.php?id_no=<?php echo $student_id; ?>'">Pay Fee</button>
        </div>
    </div>
</body>
</html>
