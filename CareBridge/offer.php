<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$helper_id = $_SESSION["user_id"];

$message = "";
$error = "";

/* Submit Help Offer */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $request_id = intval($_POST["request_id"]);
    $help_message = trim($_POST["message"]);

    if ($request_id > 0 && !empty($help_message)) {

        /* Check that request exists and is still pending */
        $check = $conn->prepare(
            "SELECT id FROM requests 
             WHERE id = ? AND status = 'Pending'"
        );

        $check->bind_param("i", $request_id);
        $check->execute();

        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {

            /* Save help offer */
            $stmt = $conn->prepare(
                "INSERT INTO help_offers 
                (request_id, helper_id, message, status)
                VALUES (?, ?, ?, 'Pending')"
            );

            $stmt->bind_param(
                "iis",
                $request_id,
                $helper_id,
                $help_message
            );

            if ($stmt->execute()) {
                $message = "Help offer submitted successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }

        } else {
            $error = "This request is no longer available.";
        }

    } else {
        $error = "Please write how you can help.";
    }
}


/* Get Blood Requests */
$blood_sql = "SELECT requests.*,
                     users.name AS requester_name,
                     users.role AS requester_role
              FROM requests
              INNER JOIN users ON requests.user_id = users.id
              WHERE requests.category = 'Blood'
              AND requests.status = 'Pending'
              AND requests.user_id != ?
              ORDER BY requests.created_at DESC";

$blood_stmt = $conn->prepare($blood_sql);
$blood_stmt->bind_param("i", $helper_id);
$blood_stmt->execute();
$blood_result = $blood_stmt->get_result();


/* Get Food and Clothes Requests */
$ngo_sql = "SELECT requests.*,
                   users.name AS requester_name,
                   users.role AS requester_role
            FROM requests
            INNER JOIN users ON requests.user_id = users.id
            WHERE requests.category IN ('Food', 'Clothes')
            AND requests.status = 'Pending'
            AND requests.user_id != ?
            AND users.role = 'ngo'
            ORDER BY 
                CASE
                    WHEN requests.category = 'Food' THEN 1
                    WHEN requests.category = 'Clothes' THEN 2
                    ELSE 3
                END,
                requests.created_at DESC";

$ngo_stmt = $conn->prepare($ngo_sql);
$ngo_stmt->bind_param("i", $helper_id);
$ngo_stmt->execute();
$ngo_result = $ngo_stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Offer Help - CareBridge</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #FFF9F3;
            color: #263238;
        }

        .header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            padding: 45px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 10px;
            font-size: 36px;
        }

        .header p {
            margin: 0;
            font-size: 17px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .success {
            background: #E8F8EF;
            color: #248653;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
        }

        .error {
            background: #FFE9EC;
            color: #D63E52;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
        }

        .section {
            margin-bottom: 50px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .section-title h2 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        .section-title p {
            color: #666;
            margin: 0;
        }

        .blood-title {
            color: #E94F64;
        }

        .ngo-title {
            color: #35A66F;
        }

        .request-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
        }

        .request-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border-top: 5px solid #6C63C9;
        }

        .blood-card {
            border-top-color: #E94F64;
        }

        .food-card {
            border-top-color: #FF9F43;
        }

        .clothes-card {
            border-top-color: #35A66F;
        }

        .category {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .blood-category {
            color: #E94F64;
        }

        .food-category {
            color: #FF9F43;
        }

        .clothes-category {
            color: #35A66F;
        }

        .info {
            margin: 10px 0;
            line-height: 1.5;
        }

        .label {
            font-weight: bold;
        }

        .priority-high {
            color: #E94F64;
            font-weight: bold;
        }

        .priority-medium {
            color: #FF9F43;
            font-weight: bold;
        }

        .priority-low {
            color: #35A66F;
            font-weight: bold;
        }

        .status {
            color: #FF9F43;
            font-weight: bold;
        }

        .offer-form {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 18px;
        }

        textarea {
            width: 100%;
            min-height: 90px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            resize: vertical;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        textarea:focus {
            outline: none;
            border-color: #6C63C9;
        }

        .offer-btn {
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #6C63C9;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .offer-btn:hover {
            background: #574FB3;
        }

        .blood-btn {
            background: #E94F64;
        }

        .blood-btn:hover {
            background: #D63E52;
        }

        .ngo-btn {
            background: #35A66F;
        }

        .ngo-btn:hover {
            background: #248653;
        }

        .empty {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px;
            color: #777;
        }

        .note {
            background: #F3F0FF;
            padding: 18px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 35px;
            color: #5149A5;
        }

        @media (max-width: 600px) {

            .header h1 {
                font-size: 28px;
            }

            .container {
                width: 94%;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <h1>💝 Offer Help</h1>

    <p>
        Your small act of kindness can make a big difference.
    </p>

</div>


<div class="container">

    <div class="note">
        🤝 Choose a request below and tell the requester how you can help.
    </div>


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


    <!-- BLOOD SECTION -->

    <div class="section">

        <div class="section-title">

            <h2 class="blood-title">
                🩸 Blood — For Individuals
            </h2>

            <p>
                Help individuals with urgent blood requirements.
            </p>

        </div>


        <?php if ($blood_result->num_rows > 0) { ?>

            <div class="request-grid">

                <?php while ($row = $blood_result->fetch_assoc()) { ?>

                    <div class="request-card blood-card">

                        <div class="category blood-category">
                            🩸 Blood Request
                        </div>

                        <div class="info">
                            👤
                            <span class="label">Requested by:</span>
                            <?php echo htmlspecialchars($row["requester_name"]); ?>
                        </div>

                        <div class="info">
                            📝
                            <span class="label">Requirement:</span>
                            <?php echo htmlspecialchars($row["description"]); ?>
                        </div>

                        <div class="info">
                            📍
                            <span class="label">Location:</span>
                            <?php echo htmlspecialchars($row["location"]); ?>
                        </div>

                        <div class="info">
                            🎯
                            <span class="label">Priority:</span>

                            <span class="priority-high">
                                <?php echo htmlspecialchars($row["priority"]); ?>
                            </span>
                        </div>

                        <div class="info">
                            📌
                            <span class="label">Status:</span>

                            <span class="status">
                                <?php echo htmlspecialchars($row["status"]); ?>
                            </span>
                        </div>


                        <form method="POST" class="offer-form">

                            <input
                                type="hidden"
                                name="request_id"
                                value="<?php echo $row["id"]; ?>"
                            >

                            <textarea
                                name="message"
                                placeholder="Write how you can help..."
                                required
                            ></textarea>

                            <button
                                type="submit"
                                class="offer-btn blood-btn"
                            >
                                🩸 Offer Blood / Help
                            </button>

                        </form>

                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty">
                🩸 No pending blood requests right now.
            </div>

        <?php } ?>

    </div>


    <!-- FOOD & CLOTHES SECTION -->

    <div class="section">

        <div class="section-title">

            <h2 class="ngo-title">
                🍱 Food & 👕 Clothes — For NGOs
            </h2>

            <p>
                Support NGOs by donating essential food and clothing.
            </p>

        </div>


        <?php if ($ngo_result->num_rows > 0) { ?>

            <div class="request-grid">

                <?php while ($row = $ngo_result->fetch_assoc()) { ?>

                    <?php

                    if ($row["category"] == "Food") {
                        $card_class = "food-card";
                        $category_class = "food-category";
                        $icon = "🍱";
                    } else {
                        $card_class = "clothes-card";
                        $category_class = "clothes-category";
                        $icon = "👕";
                    }

                    ?>

                    <div class="request-card <?php echo $card_class; ?>">

                        <div class="category <?php echo $category_class; ?>">

                            <?php echo $icon; ?>

                            <?php echo htmlspecialchars($row["category"]); ?>
                            Request

                        </div>

                        <div class="info">
                            🏢
                            <span class="label">NGO:</span>
                            <?php echo htmlspecialchars($row["requester_name"]); ?>
                        </div>

                        <div class="info">
                            📝
                            <span class="label">Requirement:</span>
                            <?php echo htmlspecialchars($row["description"]); ?>
                        </div>

                        <div class="info">
                            📍
                            <span class="label">Location:</span>
                            <?php echo htmlspecialchars($row["location"]); ?>
                        </div>

                        <div class="info">
                            🎯
                            <span class="label">Priority:</span>

                            <?php if ($row["priority"] == "Medium") { ?>

                                <span class="priority-medium">
                                    <?php echo htmlspecialchars($row["priority"]); ?>
                                </span>

                            <?php } else { ?>

                                <span class="priority-low">
                                    <?php echo htmlspecialchars($row["priority"]); ?>
                                </span>

                            <?php } ?>

                        </div>

                        <div class="info">
                            📌
                            <span class="label">Status:</span>

                            <span class="status">
                                <?php echo htmlspecialchars($row["status"]); ?>
                            </span>
                        </div>


                        <form method="POST" class="offer-form">

                            <input
                                type="hidden"
                                name="request_id"
                                value="<?php echo $row["id"]; ?>"
                            >

                            <textarea
                                name="message"
                                placeholder="Write how you can help..."
                                required
                            ></textarea>

                            <button
                                type="submit"
                                class="offer-btn ngo-btn"
                            >
                                💝 Offer Help to NGO
                            </button>

                        </form>

                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty">
                🍱👕 No pending Food or Clothes requests from NGOs right now.
            </div>

        <?php } ?>

    </div>

</div>

</body>

</html>