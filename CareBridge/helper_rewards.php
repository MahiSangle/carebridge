<?php

session_start();

include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];


/* =========================================
   COUNT SUCCESSFUL HELPS
   ========================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM help_offers
    WHERE helper_id = ?
    AND status = 'Accepted'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

$successful_helps = $data["total"];

$stmt->close();


/* =========================================
   DETERMINE BADGE
   ========================================= */

if ($successful_helps >= 5) {

    $badge = "🏆 Community Champion";
    $message = "Amazing! You have made a major impact in the community.";

} elseif ($successful_helps >= 3) {

    $badge = "🤝 Community Helper";
    $message = "Great work! Your kindness is making a difference.";

} elseif ($successful_helps >= 1) {

    $badge = "🌱 New Helper";
    $message = "Every act of kindness matters. Keep helping!";

} else {

    $badge = "💙 Future Helper";
    $message = "Your first successful help will unlock your first badge.";

}


/* =========================================
   GET ACCEPTED HELP DETAILS
   ========================================= */

$history_sql = "
    SELECT
        help_offers.created_at,
        requests.category,
        requests.description,
        requests.location
    FROM help_offers
    JOIN requests
        ON help_offers.request_id = requests.id
    WHERE help_offers.helper_id = ?
    AND help_offers.status = 'Accepted'
    ORDER BY help_offers.created_at DESC
";

$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();

$history_result = $history_stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Helper Rewards - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .reward-header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
            border-radius: 0 0 25px 25px;
        }

        .reward-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .reward-header p {
            margin: 0;
            font-size: 17px;
        }

        .reward-container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .reward-main {
            background: white;
            padding: 40px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .reward-icon {
            font-size: 70px;
            margin-bottom: 10px;
        }

        .reward-main h2 {
            color: #6C63C9;
            margin: 10px 0;
        }

        .reward-count {
            font-size: 42px;
            font-weight: bold;
            color: #6C63C9;
            margin: 15px 0;
        }

        .reward-badge {
            display: inline-block;
            padding: 14px 25px;
            border-radius: 30px;
            background: #F5F2FF;
            color: #6C63C9;
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0 15px;
        }

        .reward-message {
            color: #666;
            font-size: 16px;
        }

        .levels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .level-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        }

        .level-card .level-icon {
            font-size: 40px;
        }

        .level-card h3 {
            color: #6C63C9;
        }

        .level-card p {
            color: #666;
        }

        .history {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .history h2 {
            text-align: center;
            color: #6C63C9;
            margin-top: 0;
            margin-bottom: 25px;
        }

        .history-card {
            border: 1px solid #eee;
            border-left: 5px solid #6C63C9;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            background: #faf9ff;
        }

        .history-card h3 {
            color: #6C63C9;
            margin-top: 0;
        }

        .history-card p {
            margin: 6px 0;
        }

        .no-history {
            text-align: center;
            color: #777;
            padding: 20px;
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

        @media (max-width: 700px) {

            .levels {
                grid-template-columns: 1fr;
            }

            .reward-container {
                width: 94%;
            }

            .reward-main,
            .history {
                padding: 22px;
            }

        }

    </style>

</head>


<body>


<div class="reward-header">

    <h1>🏆 Helper Recognition & Badges</h1>

    <p>
        Every act of kindness deserves recognition.
    </p>

</div>


<div class="reward-container">


    <!-- CURRENT REWARD -->

    <div class="reward-main">

        <div class="reward-icon">
            🏆
        </div>

        <h2>
            <?php echo htmlspecialchars($name); ?>
        </h2>

        <div class="reward-count">
            <?php echo $successful_helps; ?>
        </div>

        <p>
            Successful Helps
        </p>

        <div class="reward-badge">
            <?php echo $badge; ?>
        </div>

        <p class="reward-message">
            <?php echo $message; ?>
        </p>

    </div>


    <!-- BADGE LEVELS -->

    <div class="levels">


        <div class="level-card">

            <div class="level-icon">
                🌱
            </div>

            <h3>
                New Helper
            </h3>

            <p>
                Unlock with 1 successful help.
            </p>

        </div>


        <div class="level-card">

            <div class="level-icon">
                🤝
            </div>

            <h3>
                Community Helper
            </h3>

            <p>
                Unlock with 3 successful helps.
            </p>

        </div>


        <div class="level-card">

            <div class="level-icon">
                🏆
            </div>

            <h3>
                Community Champion
            </h3>

            <p>
                Unlock with 5 successful helps.
            </p>

        </div>


    </div>


    <!-- SUCCESSFUL HELP HISTORY -->

    <div class="history">

        <h2>
            💝 Your Successful Help History
        </h2>


        <?php

        if ($history_result->num_rows > 0) {

            while ($help = $history_result->fetch_assoc()) {

        ?>

                <div class="history-card">

                    <h3>
                        🤝 <?php echo htmlspecialchars($help["category"]); ?>
                    </h3>

                    <p>
                        <strong>Help Provided:</strong>
                        <?php echo htmlspecialchars($help["description"]); ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?php echo htmlspecialchars($help["location"]); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        ✅ Accepted
                    </p>

                </div>

        <?php

            }

        } else {

        ?>

            <div class="no-history">

                <p>
                    You haven't completed a successful help yet.
                </p>

                <p>
                    Offer help to a community member to earn your first badge! 🌱
                </p>

            </div>

        <?php

        }

        ?>

    </div>


    <!-- BACK BUTTON -->

    <div class="back-button">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>
