<?php
session_start();
include("connection.php");

// Check if session and query parameter are set
if (!isset($_SESSION['student_id']) || !isset($_GET['id_no'])) {
    header("Location: login.php");
    exit;
}

// Get id_no from query parameter
$id_no = $_GET['id_no'];

// Fetch student data from the database using id_no
$query = "SELECT * FROM student WHERE id_no = '$id_no' LIMIT 1";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $student_data = mysqli_fetch_assoc($result);
} else {
    echo "Student data not found!";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($student_data['name']); ?>!</h1>
    <p>ID: <?php echo htmlspecialchars($student_data['id_no']); ?></p>
    <p>Email: <?php echo htmlspecialchars($student_data['email']); ?></p>
    <p>Contact: <?php echo htmlspecialchars($student_data['contact']); ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>
