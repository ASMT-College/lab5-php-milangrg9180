<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Validation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f06, #4a90e2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        form h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input:focus {
            border-color: #4a90e2;
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4a90e2;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #357ab8;
        }
    </style>
</head>
<body>

<form id="registerForm" method="POST">
    <h2>Form Submission</h2>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username">
    <div class="error" id="usernameError"></div>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email">
    <div class="error" id="emailError"></div>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password">
    <div class="error" id="passwordError"></div>

    <label for="confirmPassword">Confirm Password:</label>
    <input type="password" id="confirmPassword" name="confirmPassword">
    <div class="error" id="confirmPasswordError"></div>

    <button type="submit">Submit</button>
</form>

<script>
document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();

    // Clear previous error messages
    document.querySelectorAll('.error').forEach(error => error.textContent = '');

    let isValid = true;

    // Username validation
    const username = document.getElementById('username').value;
    if (username.length < 2) {
        isValid = false;
        document.getElementById('usernameError').textContent = 'Username must be at least 2 characters long';
    }

    // Password validation
    const password = document.getElementById('password').value;
    if (!password.match(/[a-zA-Z]/) || !password.match(/[0-9]/)) {
        isValid = false;
        document.getElementById('passwordError').textContent = 'Password must contain at least one letter and one number';
    }

    // Email validation
    const email = document.getElementById('email').value;
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!email.match(emailPattern)) {
        isValid = false;
        document.getElementById('emailError').textContent = 'Please enter a valid email';
    }

    // Confirm password validation
    const confirmPassword = document.getElementById('confirmPassword').value;
    if (password !== confirmPassword) {
        isValid = false;
        document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
    }

    if (isValid) {
        // Perform Ajax call for server-side validation
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true); // Empty string for the current file
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Form submitted successfully');
                } else {
                    if (response.usernameError) document.getElementById('usernameError').textContent = response.usernameError;
                    if (response.passwordError) document.getElementById('passwordError').textContent = response.passwordError;
                    if (response.emailError) document.getElementById('emailError').textContent = response.emailError;
                    if (response.confirmPasswordError) document.getElementById('confirmPasswordError').textContent = response.confirmPasswordError;
                    if (response.dbError) alert(response.dbError); // Display database error if any
                }
            }
        };
        xhr.send(`username=${username}&password=${password}&email=${email}&confirmPassword=${confirmPassword}`);
    }
});
</script>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$servername = "localhost";
$username = "root";
$password = ""; // Default password is empty for XAMPP
$dbname = "MyDatabase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function validate_username($username) {
    return strlen($username) >= 2;
}

function validate_password($password) {
    return preg_match('/[a-zA-Z]/', $password) && preg_match('/[0-9]/', $password);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_confirm_password($password, $confirm_password) {
    return $password === $confirm_password;
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $confirm_password = $_POST['confirmPassword'];

    $is_valid = true;

    if (!validate_username($username)) {
        $is_valid = false;
        $response['usernameError'] = 'Username must be at least 2 characters long';
    }

    if (!validate_password($password)) {
        $is_valid = false;
        $response['passwordError'] = 'Password must contain at least one letter and one number';
    }

    if (!validate_email($email)) {
        $is_valid = false;
        $response['emailError'] = 'Please enter a valid email';
    }

    if (!validate_confirm_password($password, $confirm_password)) {
        $is_valid = false;
        $response['confirmPasswordError'] = 'Passwords do not match';
    }

    if ($is_valid) {
        // Hash the password before saving it to the database
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $email);

        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['dbError'] = 'Database error: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['success'] = false;
    }

    echo json_encode($response);
}

$conn->close();
?>
</body>
</html>
