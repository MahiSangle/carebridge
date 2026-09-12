<?php

session_start();
include("includes/db.php");
include("ai_priority.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $category = $_POST["category"];
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);

    if (empty($category) || empty($description) || empty($location)) {

        $error = "Please fill all the fields.";

    } else {

        // AI-based priority
        $priority = getPriority($category);

        $status = "Pending";

        $sql = "INSERT INTO requests
                (user_id, category, description, location, priority, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isssss",
            $user_id,
            $category,
            $description,
            $location,
            $priority,
            $status
        );

        if ($stmt->execute()) {

            $message = "Request submitted successfully! Priority: " . $priority;

        } else {

            $error = "Something went wrong. Please try again.";

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Request Help - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="header">

    <h1>🆘 Request Help</h1>

    <p>
        Tell the CareBridge community what you need.
    </p>

</div>


<div class="container">

    <div class="card">

        <h2>Ask For Help 🤝</h2>

        <p class="intro">
            Create a request for Blood, Food, or Clothes.
            Our priority system will automatically identify the request priority.
        </p>


        <?php if (!empty($message)) { ?>

            <div class="success">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <?php if (!empty($error)) { ?>

            <div class="error">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <label>
                🏷️ What do you need?
            </label>

            <select name="category" required>

                <option value="">-- Select Category --</option>

                <option value="Blood">
                    🩸 Blood
                </option>

                <option value="Food">
                    🍱 Food
                </option>

                <option value="Clothes">
                    👕 Clothes
                </option>

            </select>


            <label>
                📝 Describe your requirement
            </label>

            <textarea
                name="description"
                placeholder="Explain what you need..."
                required
            ></textarea>


            <label>
                📍 Location
            </label>

            <input
                type="text"
                name="location"
                placeholder="Enter your location"
                value=""
                required
            >


            <button
                type="submit"
                class="submit-btn"
            >
                🆘 Submit Request
            </button>

        </form>


        <div class="priority-info">

            <h3>🤖 Smart Priority System</h3>

            <p>🩸 Blood → <strong>High Priority</strong></p>

            <p>🍱 Food → <strong>Medium Priority</strong></p>

            <p>👕 Clothes → <strong>Low Priority</strong></p>

        </div>

    </div>

</div>

</body>

</html>