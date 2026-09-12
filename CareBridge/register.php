<?php

session_start();

require_once "includes/db.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    if ($name == "" || $email == "" || $phone == "" || $password == "" || $role == "") {

        $message = "Please fill all fields.";

    } else {

        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $message = "Email already registered. Please login.";

        } else {

            // Encrypt password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $sql = "INSERT INTO users (name, email, password, phone, role)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "sssss",
                    $name,
                    $email,
                    $hashed_password,
                    $phone,
                    $role
                );

                if ($stmt->execute()) {

                    $message = "Registration successful! Redirecting to login...";
                    $success = true;

                } else {

                    $message = "Registration failed: " . $stmt->error;

                }

            } else {

                $message = "Database error: " . $conn->error;

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CareBridge - Register</title>

    <link rel="stylesheet" href="css/style.css">

    <?php if ($success) { ?>

        <meta http-equiv="refresh" content="2;url=login.php">

    <?php } ?>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6c63c9, #39b7e8);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .register-box {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            padding: 35px 40px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.20);
        }

        .logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo span {
            font-size: 34px;
            font-weight: bold;
            color: #3156b8;
            letter-spacing: 1px;
        }

        .logo-icon {
            font-size: 32px;
            vertical-align: middle;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 28px;
            font-size: 15px;
        }

        .register-box h2 {
            text-align: center;
            color: #263238;
            margin-bottom: 25px;
            font-size: 25px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #263238;
            font-weight: bold;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #d8d8d8;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            background: #fff;
            color: #263238;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #6c63c9;
            box-shadow: 0 0 0 3px rgba(108, 99, 201, 0.12);
        }

        .password-box {
            position: relative;
        }

        .password-box input {
            padding-right: 50px;
        }

        .show-password {
            position: absolute;
            right: 10px;
            top: 8px;
            width: 38px;
            height: 38px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 18px;
        }

        .register-btn {
            width: 100%;
            border: none;
            padding: 14px;
            margin-top: 8px;
            border-radius: 11px;
            background: linear-gradient(135deg, #6c63c9, #3156b8);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .message {
            padding: 13px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            background: #fff4d6;
            color: #8a6500;
        }

        .success {
            background: #dff7e8;
            color: #16733d;
        }

        .login-text {
            text-align: center;
            margin-top: 22px;
            color: #666;
            font-size: 14px;
        }

        .login-text a {
            color: #6c63c9;
            font-weight: bold;
            text-decoration: none;
        }

        .home-link {
            text-align: center;
            margin-top: 12px;
        }

        .home-link a {
            color: #777;
            text-decoration: none;
            font-size: 13px;
        }

    </style>

</head>

<body>

    <div class="register-box">

        <div class="logo">
            <span class="logo-icon">🤝</span>
            <span>CareBridge</span>
        </div>

        <div class="subtitle">
            COMMUNITY • CARE • CONNECTION
        </div>

        <h2>Create Your Account</h2>

        <?php if ($message != "") { ?>

            <div class="message <?php echo $success ? 'success' : ''; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>

        <?php if (!$success) { ?>

        <form method="POST" action="register.php">

            <div class="form-group">

                <label>👤 Full Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your name"
                    required
                >

            </div>

            <div class="form-group">

                <label>📧 Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="form-group">

                <label>📱 Phone Number</label>

                <input
                    type="text"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >

            </div>

            <div class="form-group">

                <label>🔒 Password</label>

                <div class="password-box">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Create a password"
                        required
                    >

                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword()"
                    >
                        👁️
                    </button>

                </div>

            </div>

            <div class="form-group">

                <label>🤝 Register As</label>

                <select name="role" required>

                    <option value="">Select your role</option>

                    <option value="individual">Individual</option>

                    <option value="ngo">NGO</option>

                </select>

            </div>

            <button type="submit" class="register-btn">

                Create CareBridge Account

            </button>

        </form>

        <?php } ?>

        <div class="login-text">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>

        <div class="home-link">

            <a href="index.php">
                ← Back to CareBridge Home
            </a>

        </div>

    </div>

    <script>

        function togglePassword() {

            const password = document.getElementById("password");

            if (password.type === "password") {

                password.type = "text";

            } else {

                password.type = "password";

            }

        }

    </script>

</body>

</html>