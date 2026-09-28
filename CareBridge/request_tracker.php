<?php

session_start();

include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


/* =========================================
   GET USER'S REQUESTS
   ========================================= */

$sql = "
    SELECT 
        requests.*,
        (
            SELECT COUNT(*)
            FROM help_offers
            WHERE help_offers.request_id = requests.id
        ) AS offer_count
    FROM requests
    WHERE requests.user_id = ?
    ORDER BY requests.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Request Status Tracker - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .tracker-header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
            border-radius: 0 0 25px 25px;
        }

        .tracker-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .tracker-header p {
            margin: 0;
            font-size: 17px;
        }

        .tracker-container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .tracker-card {
            background: white;
            padding: 28px;
            margin-bottom: 25px;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        }

        .tracker-card h2 {
            color: #6C63C9;
            margin-top: 0;
        }

        .request-info {
            background: #F8F6FF;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .request-info p {
            margin: 7px 0;
        }

        .priority {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .high {
            background: #FFE1E5;
            color: #D63E52;
        }

        .medium {
            background: #FFF0D5;
            color: #B86B00;
        }

        .low {
            background: #E5F6EA;
            color: #248653;
        }

        .progress {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 35px 0 20px;
            position: relative;
        }

        .progress::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: #ddd;
            z-index: 0;
        }

        .step {
            width: 30%;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 42px;
            height: 42px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: #ddd;
            color: #777;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .step.active .step-circle {
            background: #6C63C9;
            color: white;
        }

        .step.completed .step-circle {
            background: #248653;
            color: white;
        }

        .step h4 {
            margin: 5px 0;
        }

        .step p {
            font-size: 13px;
            color: #777;
        }

        .current-status {
            text-align: center;
            padding: 15px;
            background: #F5F2FF;
            border-radius: 12px;
            font-weight: bold;
            color: #6C63C9;
        }

        .empty {
            text-align: center;
            padding: 45px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
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

        @media (max-width: 650px) {

            .tracker-container {
                width: 94%;
            }

            .tracker-card {
                padding: 20px;
            }

            .progress::before {
                left: 8%;
                right: 8%;
            }

            .step h4 {
                font-size: 14px;
            }

            .step p {
                font-size: 11px;
            }

        }

    </style>

</head>


<body>


<div class="tracker-header">

    <h1>📍 Request Status Tracker</h1>

    <p>
        Track the progress of your help requests.
    </p>

</div>


<div class="tracker-container">


<?php

if ($result->num_rows > 0) {

    while ($request = $result->fetch_assoc()) {

        /*
         * Determine current stage
         */

        if ($request["status"] == "Accepted") {

            $current_step = 3;
            $status_text = "Help Accepted";

        } elseif ($request["offer_count"] > 0) {

            $current_step = 2;
            $status_text = "Offer Received";

        } else {

            $current_step = 1;
            $status_text = "Request Pending";

        }

?>


    <div class="tracker-card">


        <h2>
            <?php echo htmlspecialchars($request["category"]); ?>
        </h2>


        <div class="request-info">

            <p>
                <strong>Requirement:</strong>
                <?php echo htmlspecialchars($request["description"]); ?>
            </p>

            <p>
                <strong>Location:</strong>
                <?php echo htmlspecialchars($request["location"]); ?>
            </p>

            <p>
                <strong>Priority:</strong>

                <span class="priority
                <?php
                    echo strtolower($request["priority"]);
                ?>">

                    <?php echo htmlspecialchars($request["priority"]); ?>

                </span>

            </p>

            <p>
                <strong>Offers Received:</strong>
                <?php echo $request["offer_count"]; ?>
            </p>

        </div>


        <!-- PROGRESS TRACKER -->

        <div class="progress">


            <!-- STEP 1 -->

            <div class="step
                <?php
                    if ($current_step >= 1) {
                        echo "completed";
                    }
                ?>">

                <div class="step-circle">
                    ✓
                </div>

                <h4>Request Created</h4>

                <p>
                    Your request has been submitted.
                </p>

            </div>


            <!-- STEP 2 -->

            <div class="step
                <?php
                    if ($current_step == 2) {
                        echo "active";
                    }

                    if ($current_step >= 3) {
                        echo "completed";
                    }
                ?>">

                <div class="step-circle">

                    <?php

                    if ($current_step >= 3) {
                        echo "✓";
                    } else {
                        echo "2";
                    }

                    ?>

                </div>

                <h4>Offer Received</h4>

                <p>
                    Someone has offered help.
                </p>

            </div>


            <!-- STEP 3 -->

            <div class="step
                <?php
                    if ($current_step == 3) {
                        echo "active";
                    }
                ?>">

                <div class="step-circle">

                    <?php

                    if ($current_step == 3) {
                        echo "✓";
                    } else {
                        echo "3";
                    }

                    ?>

                </div>

                <h4>Help Accepted</h4>

                <p>
                    Your request has been successfully connected.
                </p>

            </div>


        </div>


        <div class="current-status">

            📌 Current Status:
            <?php echo $status_text; ?>

        </div>


    </div>


<?php

    }

} else {

?>


    <div class="empty">

        <h2>📋 No Requests Yet</h2>

        <p>
            You haven't created any help requests yet.
        </p>

        <br>

        <a href="request.php" class="btn">
            🆘 Create a Request
        </a>

    </div>


<?php

}

?>


    <div class="back-button">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>


