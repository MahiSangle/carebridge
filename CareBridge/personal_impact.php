<?php

session_start();

include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];
$role = $_SESSION["role"];


/* ================================
   TOTAL REQUESTS CREATED
   ================================ */

$sql = "
    SELECT COUNT(*) AS total
    FROM requests
    WHERE user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

$total_requests = $data["total"];

$stmt->close();


/* ================================
   TOTAL HELP OFFERS
   ================================ */

$sql = "
    SELECT COUNT(*) AS total
    FROM help_offers
    WHERE helper_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

$total_offers = $data["total"];

$stmt->close();


/* ================================
   SUCCESSFUL HELPS
   ================================ */

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


/* ================================
   ACCEPTED REQUESTS
   ================================ */

$sql = "
    SELECT COUNT(*) AS total
    FROM requests
    WHERE user_id = ?
    AND status = 'Accepted'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

$accepted_requests = $data["total"];

$stmt->close();


/* ================================
   IMPACT MESSAGE
   ================================ */

if ($successful_helps >= 5) {

    $impact_message = "Outstanding! You are making a major difference in the community. 🏆";

} elseif ($successful_helps >= 3) {

    $impact_message = "Amazing work! Your contribution is creating a positive impact. 🤝";

} elseif ($successful_helps >= 1) {

    $impact_message = "Great start! Every act of kindness creates an impact. 🌱";

} elseif ($total_requests > 0 || $total_offers > 0) {

    $impact_message = "You have started making a difference. Keep going! 💙";

} else {

    $impact_message = "Start your journey with CareBridge and make a difference today. 💙";

}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Personal Impact - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .impact-header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
            border-radius: 0 0 25px 25px;
        }

        .impact-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .impact-header p {
            margin: 0;
            font-size: 17px;
        }

        .impact-container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .profile-impact {
            background: white;
            padding: 35px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .profile-icon {
            font-size: 60px;
        }

        .profile-impact h2 {
            color: #6C63C9;
            margin: 10px 0;
        }

        .role-badge {
            display: inline-block;
            background: #F5F2FF;
            color: #6C63C9;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 28px 15px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        }

        .stat-icon {
            font-size: 38px;
        }

        .stat-number {
            font-size: 35px;
            font-weight: bold;
            color: #6C63C9;
            margin: 8px 0;
        }

        .stat-card p {
            color: #666;
            margin: 0;
        }

        .impact-message {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .impact-message h2 {
            color: #6C63C9;
            margin-top: 0;
        }

        .impact-message p {
            color: #666;
            font-size: 17px;
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

        @media (max-width: 800px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 500px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .impact-container {
                width: 94%;
            }

        }

    </style>

</head>


<body>


<div class="impact-header">

    <h1>📈 Personal Impact & Improvement</h1>

    <p>
        See how your actions contribute to the CareBridge community.
    </p>

</div>


<div class="impact-container">


    <!-- USER INFORMATION -->

    <div class="profile-impact">

        <div class="profile-icon">
            👤
        </div>

        <h2>
            <?php echo htmlspecialchars($name); ?>
        </h2>

        <span class="role-badge">
            <?php echo htmlspecialchars($role); ?>
        </span>

    </div>


    <!-- PERSONAL STATISTICS -->

    <div class="stats-grid">


        <div class="stat-card">

            <div class="stat-icon">
                🆘
            </div>

            <div class="stat-number">
                <?php echo $total_requests; ?>
            </div>

            <p>
                Requests Created
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🤝
            </div>

            <div class="stat-number">
                <?php echo $total_offers; ?>
            </div>

            <p>
                Help Offers
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                💚
            </div>

            <div class="stat-number">
                <?php echo $successful_helps; ?>
            </div>

            <p>
                Successful Helps
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <div class="stat-number">
                <?php echo $accepted_requests; ?>
            </div>

            <p>
                Requests Accepted
            </p>

        </div>


    </div>


    <!-- IMPACT MESSAGE -->

    <div class="impact-message">

        <h2>
            🌟 Your Community Impact
        </h2>

        <p>
            <?php echo htmlspecialchars($impact_message); ?>
        </p>

    </div>


    <!-- BACK -->

    <div class="back-button">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>