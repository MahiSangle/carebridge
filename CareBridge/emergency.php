<?php

session_start();

include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/* ==============================
   COMMUNITY STATISTICS
   ============================== */

// Total requests
$result = $conn->query("SELECT COUNT(*) AS total FROM requests");
$total_requests = $result->fetch_assoc()["total"];


// High priority pending requests
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE priority = 'High'
    AND status = 'Pending'
");
$urgent_requests = $result->fetch_assoc()["total"];


// Blood requests
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests
    WHERE category = 'Blood'
    AND status = 'Pending'
");
$blood_requests = $result->fetch_assoc()["total"];


// Successful helps
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM help_offers
    WHERE status = 'Accepted'
");
$successful_helps = $result->fetch_assoc()["total"];


// Community members
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role != 'admin'
");
$community_members = $result->fetch_assoc()["total"];


/* ==============================
   HIGH PRIORITY REQUESTS
   ============================== */

$emergency_sql = "
    SELECT 
        requests.*,
        users.name AS requester_name,
        users.role AS requester_role
    FROM requests
    JOIN users
        ON requests.user_id = users.id
    WHERE requests.priority = 'High'
    AND requests.status = 'Pending'
    ORDER BY requests.created_at DESC
";

$emergency_result = $conn->query($emergency_sql);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Emergency Impact - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .emergency-header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
            border-radius: 0 0 25px 25px;
        }

        .emergency-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .emergency-header p {
            margin: 0;
            font-size: 17px;
        }

        .impact-container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .impact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .impact-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        }

        .impact-card .number {
            font-size: 34px;
            font-weight: bold;
            color: #6C63C9;
            margin-bottom: 5px;
        }

        .impact-card .label {
            color: #666;
            font-weight: bold;
        }

        .emergency-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .emergency-section h2 {
            color: #6C63C9;
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
        }

        .emergency-card {
            border: 1px solid #eee;
            border-left: 6px solid #D63E52;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 18px;
            background: #fffafa;
        }

        .emergency-card h3 {
            margin-top: 0;
            color: #D63E52;
        }

        .emergency-card p {
            margin: 8px 0;
        }

        .high-badge {
            display: inline-block;
            background: #FFE1E5;
            color: #D63E52;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 13px;
        }

        .impact-message {
            text-align: center;
            background: #F5F2FF;
            padding: 25px;
            border-radius: 16px;
        }

        .impact-message h2 {
            margin-bottom: 10px;
        }

        .back-button {
            text-align: center;
            margin-top: 25px;
        }

        @media (max-width: 900px) {

            .impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .impact-grid {
                grid-template-columns: 1fr;
            }

            .impact-container {
                width: 94%;
            }

            .emergency-section {
                padding: 22px;
            }

        }

    </style>

</head>


<body>


<div class="emergency-header">

    <h1>🚨 Emergency Impact Dashboard</h1>

    <p>
        Every urgent request is an opportunity to make a difference.
    </p>

</div>


<div class="impact-container">


    <!-- COMMUNITY STATISTICS -->

    <div class="impact-grid">

        <div class="impact-card">

            <div class="number">
                <?php echo $urgent_requests; ?>
            </div>

            <div class="label">
                🚨 Urgent Requests
            </div>

        </div>


        <div class="impact-card">

            <div class="number">
                <?php echo $blood_requests; ?>
            </div>

            <div class="label">
                🩸 Blood Needs
            </div>

        </div>


        <div class="impact-card">

            <div class="number">
                <?php echo $successful_helps; ?>
            </div>

            <div class="label">
                🤝 Successful Helps
            </div>

        </div>


        <div class="impact-card">

            <div class="number">
                <?php echo $community_members; ?>
            </div>

            <div class="label">
                👥 Community Members
            </div>

        </div>

    </div>



    <!-- EMERGENCY REQUESTS -->

    <div class="emergency-section">

        <h2>
            🚨 Current High Priority Requests
        </h2>


        <?php

        if ($emergency_result->num_rows > 0) {

            while ($request = $emergency_result->fetch_assoc()) {

        ?>

                <div class="emergency-card">

                    <h3>
                        🩸 <?php echo htmlspecialchars($request["category"]); ?>
                    </h3>

                    <p>
                        <strong>Requested by:</strong>
                        <?php echo htmlspecialchars($request["requester_name"]); ?>
                    </p>

                    <p>
                        <strong>Type:</strong>
                        <?php echo htmlspecialchars($request["requester_role"]); ?>
                    </p>

                    <p>
                        <strong>Requirement:</strong>
                        <?php echo htmlspecialchars($request["description"]); ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?php echo htmlspecialchars($request["location"]); ?>
                    </p>

                    <p>
                        <span class="high-badge">
                            🔴 High Priority
                        </span>
                    </p>

                </div>

        <?php

            }

        } else {

        ?>

            <div class="impact-message">

                <h2>💚 No Current Emergencies</h2>

                <p>
                    There are no pending high-priority requests right now.
                    The community is currently stable.
                </p>

            </div>

        <?php

        }

        ?>

    </div>



    <!-- IMPACT MESSAGE -->

    <div class="impact-message">

        <h2>
            💝 CareBridge Community Impact
        </h2>

        <p>
            CareBridge connects people who need help with people
            who are ready to make a difference.
        </p>

        <p>
            <strong>
                <?php echo $total_requests; ?>
            </strong>
            requests have been created by the community.
        </p>

    </div>


    <!-- BACK -->

    <div class="back-button">

        <a href="dashboard.php" class="btn">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>