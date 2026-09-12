<?php

session_start();

require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Incorrect password!";

        }

    } else {

        $message = "User not found!";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CareBridge - Login</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <h1>CareBridge</h1>

    <h2>Login</h2>

    <?php

    if ($message != "") {
        echo "<p>$message</p>";
    }

    ?>

    <form method="POST">

        <label>Email:</label><br>

        <input type="email" name="email" required>

        <br><br>

        <label>Password:</label><br>

        <input type="password" name="password" required>

        <br><br>

        <button type="submit">Login</button>

    </form>

</body>

</html>