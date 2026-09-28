<?php

session_start();

include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];


/* ==========================================
   NOTIFICATIONS
   ========================================== */

$notifications = [];


/* ==========================================
   1. NEW HELP OFFERS ON MY REQUESTS
   ========================================== */

$sql = "
    SELECT 
        help_offers.id,
        help_offers.message,
        help_offers.status,
        requests.category
    FROM help_offers
    INNER JOIN requests
        ON help_offers.request_id = requests.id
    WHERE requests.user_id = ?
    ORDER BY help_offers.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    if ($row["status"] == "Pending") {

        $notifications[] = [
            "icon" => "🤝",
            "title" => "New Help Offer",
            "message" => "Someone has offered help for your "
                       . $row["category"] . " request.",
            "type" => "pending"
        ];

    } elseif ($row["status"] == "Accepted") {

        $notifications[] = [
            "icon" => "💚",
            "title" => "Help Accepted",
            "message" => "Your "
                       . $row["category"]
                       . " request has been successfully connected with a helper.",
            "type" => "success"
        ];

    }

}

$stmt->close();


/* ==========================================
   2. MY HELP OFFERS
   ========================================== */

$sql = "
    SELECT 
        help_offers.status,
        requests.category
    FROM help_offers
    INNER JOIN requests
        ON help_offers.request_id = requests.id
    WHERE help_offers.helper_id = ?
    ORDER BY help_offers.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    if ($row["status"] == "Accepted") {

        $notifications[] = [
            "icon" => "🎉",
            "title" => "Your Help Was Accepted",
            "message" => "Your offer to help with "
                       . $row["category"]
                       . " has been accepted.",
            "type" => "success"
        ];

    } elseif ($row["status"] == "Rejected") {

        $notifications[] = [
            "icon" => "ℹ️",
            "title" => "Help Offer Update",
            "message" => "Your offer for "
                       . $row["category"]
                       . " was not selected this time.",
            "type" => "info"
        ];

    }

}

$stmt->close();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .notification-header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
            border-radius: 0 0 25px 25px;
        }

        .notification-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .notification-header p {
            margin: 0;
            font-size: 17px;
        }

        .notification-container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
        }

        .welcome-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .welcome-card h2 {
            color: #6C63C9;
            margin: 0;
        }

        .notification-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            margin-bottom: 18px;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            border-left: 5px solid #6C63C9;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-card.success {
            border-left-color: #27AE60;
        }

        .notification-card.pending {
            border-left-color: #E67E22;
        }

        .notification-card.info {
            border-left-color: #3498DB;
        }

        .notification-icon {
            font-size: 38px;
            min-width: 55px;
            text-align: center;
        }

        .notification-content h3 {
            margin: 0 0 7px;
            color: #6C63C9;
        }

        .notification-content p {
            margin: 0;
            color: #666;
            line-height: 1.5;
        }

        .empty-card {
            background: white;
            padding: 45px 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        }

        .empty-card .icon {
            font-size: 55px;
        }

        .empty-card h2 {
            color: #6C63C9;
        }

        .empty-card p {
            color: #666;
        }

        .back-button {
            text-align: center;
            margin: 30px 0;
        }

        .back-button a {
            display: inline-block;
            padding: 12px 22px;
            background: #6C63C9;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
        }

        @media (max-width: 600px) {

            .notification-container {
                width: 94%;
            }

            .notification-card {
                padding: 20px;
            }

        }

    </style>

</head>


<body>


<div class="notification-header">

    <h1>🔔 Notifications</h1>

    <p>
        Stay updated about your CareBridge activities.
    </p>

</div>


<div class="notification-container">


    <div class="welcome-card">

        <h2>
            Hello, <?php echo htmlspecialchars($name); ?>! 👋
        </h2>

    </div>


    <?php if (count($notifications) > 0) { ?>


        <?php foreach ($notifications as $notification) { ?>

            <div class="notification-card <?php echo $notification["type"]; ?>">

                <div class="notification-icon">

                    <?php echo $notification["icon"]; ?>

                </div>


                <div class="notification-content">

                    <h3>

                        <?php echo htmlspecialchars($notification["title"]); ?>

                    </h3>


                    <p>

                        <?php echo htmlspecialchars($notification["message"]); ?>

                    </p>

                </div>

            </div>

        <?php } ?>


    <?php } else { ?>


        <div class="empty-card">

            <div class="icon">
                🔔
            </div>

            <h2>
                No New Notifications
            </h2>

            <p>
                You are all caught up! New activity will appear here.
            </p>

        </div>


    <?php } ?>


    <div class="back-button">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>