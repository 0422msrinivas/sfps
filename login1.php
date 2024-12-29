<?php
session_start();
include("connection.php");
include("functions.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id_no = $_POST['id_no'];
    $contact = $_POST['contact'];

    // Debugging the form values
    echo "ID No: " . $id_no . "<br>";
    echo "Contact: " . $contact . "<br>";

    if (!empty($id_no)) {
        // Check database connection
        if ($con) {
            $query = "SELECT * FROM student WHERE id_no = '$id_no' LIMIT 1";
            $result = mysqli_query($con, $query);

            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $student_data = mysqli_fetch_assoc($result);
                    $default_contact = "Bitm123#";

                    // Debugging contact check
                    echo "Student contact: " . $student_data['contact'] . "<br>";

                    if ($student_data['contact'] === $contact || $contact === $default_contact) {
                        $_SESSION['id_no'] = $student_data['id_no'];  // Store id_no in session
                        header("Location: student_page.php");
                        die;
                    }
                } else {
                    echo "No student found with this ID number.";
                }
            } else {
                echo "Error in query: " . mysqli_error($con);
            }
        } else {
            echo "Database connection failed!";
        }

        echo "Wrong ID number or contact!";
    } else {
        echo "ID number is required!";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<style type="text/css">
    #text {
        height: 25px;
        border-radius: 5px;
        padding: 4px;
        border: solid thin #aaa;
        width: 100%;
    }

    #button {
        padding: 10px;
        width: 100px;
        color: white;
        background-color: lightblue;
        border: none;
    }

    #box {
        background-color: grey;
        margin: auto;
        width: 300px;
        padding: 20px;
    }
</style>

<div id="box">
    <form method="post">
        <div style="font-size: 20px; margin: 10px; color: white;">Login</div>

        <!-- Change input names to match the new requirements -->
        <input id="text" type="text" name="id_no"><br><br>

        <!-- Set the default contact as Bitm-->
        <input id="text" type="password" name="contact" ><br><br>

        <input id="button" type="submit" value="Login"><br><br>

        <a href="signup.php">Click to Signup</a><br><br>
    </form>
</div>
</body>
</html>
