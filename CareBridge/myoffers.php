<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

/* Get all offers made by the logged-in user */
$sql = "SELECT help_offers.*,
               requests.category,
               requests.description,
               requests.location,
               requests.priority,
               requests.status AS request_status,
               users.name AS requester_name
        FROM help_offers
        INNER JOIN requests
        ON help_offers.request_id = requests.id
        INNER JOIN users
        ON requests.user_id = users.id
        WHERE help_offers.helper_id = ?
        ORDER BY help_offers.created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>CareBridge - My Offers</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .offers-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .offers-container h1,
        .offers-container h2 {
            text-align: center;
        }

        .offer-card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .offer-card h3 {
            color: #6C63C9;
            margin-top: 0;
        }

        .request-info {
            margin-top: 15px;
            padding: 15px;
            background: #f5f3ff;
            border-radius: 10px;
        }

        .offer-status {
            margin-top: 18px;
            font-weight: bold;
            font-size: 17px;
        }

        .accepted {
            color: #35A66F;
        }

        .pending {
            color: #FF9F1C;
        }

        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background: #555;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

    </style>

</head>

<body>

    <div class="offers-container">

        <h1>CareBridge</h1>

        <h2>💝 My Help Offers</h2>


        <?php

        if ($result->num_rows > 0) {

            while ($offer = $result->fetch_assoc()) {

        ?>

                <div class="offer-card">

                    <h3>
                        👤 Requester:
                        <?php echo htmlspecialchars($offer["requester_name"]); ?>
                    </h3>

                    <div class="request-info">

                        <p>
                            📦 <strong>Category:</strong>
                            <?php echo htmlspecialchars($offer["category"]); ?>
                        </p>

                        <p>
                            💬 <strong>Request:</strong>
                            <?php echo htmlspecialchars($offer["description"]); ?>
                        </p>

                        <p>
                            📍 <strong>Location:</strong>
                            <?php echo htmlspecialchars($offer["location"]); ?>
                        </p>

                        <p>
                            🚨 <strong>Priority:</strong>
                            <?php echo htmlspecialchars($offer["priority"]); ?>
                        </p>

                        <p>
                            💝 <strong>Your Help:</strong>
                            <?php echo htmlspecialchars($offer["message"]); ?>
                        </p>

                    </div>


                    <p class="offer-status
                        <?php
                        echo ($offer["status"] == "Accepted")
                            ? "accepted"
                            : "pending";
                        ?>">

                        📌 Your Offer Status:
                        <?php echo htmlspecialchars($offer["status"]); ?>

                    </p>


                    <p>
                        📌 <strong>Request Status:</strong>
                        <?php echo htmlspecialchars($offer["request_status"]); ?>
                    </p>


                    <?php if ($offer["status"] == "Accepted") { ?>

                        <p class="accepted">
                            ✅ Your help offer has been accepted!
                        </p>

                    <?php } elseif ($offer["status"] == "Pending") { ?>

                        <p class="pending">
                            ⏳ Waiting for the requester to respond.
                        </p>

                    <?php } ?>

                </div>

        <?php

            }

        } else {

            echo "<p>You have not offered help on any requests yet.</p>";

        }

        ?>


        <a class="back-btn" href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>

</body>

</html>