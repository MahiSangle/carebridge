<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

/* Get only the requests created by the logged-in user */
$sql = "SELECT requests.*,
        (SELECT COUNT(*)
         FROM help_offers
         WHERE help_offers.request_id = requests.id) AS offer_count
        FROM requests
        WHERE requests.user_id = ?
        ORDER BY requests.created_at DESC";

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

    <title>CareBridge - My Requests</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .my-request-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .my-request-container h1 {
            text-align: center;
        }

        .my-request-container h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .test-heading {
            text-align: center;
            color: #e94f64;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .request-card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .request-card h3 {
            margin-top: 0;
            font-size: 24px;
        }

        .offer-count {
            font-weight: bold;
            margin-top: 15px;
        }

        .view-offers {
            display: inline-block;
            margin-top: 15px;
            padding: 11px 20px;
            background: #6C63C9;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .view-offers:hover {
            opacity: 0.9;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #555;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .no-offer {
            color: #777;
            margin-top: 10px;
        }

    </style>

</head>

<body>

    <div class="my-request-container">

        <h1>CareBridge</h1>

        <!-- Temporary test heading -->
        <div class="test-heading">
            MY REQUESTS - TEST
        </div>

        <h2>My Help Requests</h2>

        <?php

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

        ?>

                <div class="request-card">

                    <h3>
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </h3>

                    <p>
                        <strong>Description:</strong>
                        <?php echo htmlspecialchars($row["description"]); ?>
                    </p>

                    <p>
                        📍 <strong>Location:</strong>
                        <?php echo htmlspecialchars($row["location"]); ?>
                    </p>

                    <p>
                        🚨 <strong>Priority:</strong>
                        <?php echo htmlspecialchars($row["priority"]); ?>
                    </p>

                    <p>
                        📌 <strong>Status:</strong>
                        <?php echo htmlspecialchars($row["status"]); ?>
                    </p>

                    <p class="offer-count">
                        💝 Help Offers Received:
                        <?php echo $row["offer_count"]; ?>
                    </p>

                    <?php if ($row["offer_count"] > 0) { ?>

                        <a class="view-offers"
                           href="view_offers.php?request_id=<?php echo $row["id"]; ?>">
                            👥 View Offers
                        </a>

                    <?php } else { ?>

                        <p class="no-offer">
                            No one has offered help yet.
                        </p>

                    <?php } ?>

                </div>

        <?php

            }

        } else {

            echo "<p>You have not submitted any help requests yet.</p>";

        }

        ?>

        <a class="back-btn" href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>

</body>

</html>