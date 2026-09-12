<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

$request_id = isset($_GET["request_id"]) ? intval($_GET["request_id"]) : 0;

if ($request_id <= 0) {
    die("Invalid request.");
}


/* Accept Help Offer */

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accept_offer"])) {

    $offer_id = intval($_POST["offer_id"]);


    /* Check request belongs to logged-in user */

    $check_request = "SELECT id
                      FROM requests
                      WHERE id = ? AND user_id = ?";

    $check_stmt = $conn->prepare($check_request);

    if (!$check_stmt) {
        die("Database error: " . $conn->error);
    }

    $check_stmt->bind_param("ii", $request_id, $user_id);
    $check_stmt->execute();

    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        die("You are not allowed to accept offers for this request.");
    }


    /* Check offer belongs to this request */

    $check_offer = "SELECT id
                    FROM help_offers
                    WHERE id = ? AND request_id = ?";

    $offer_check_stmt = $conn->prepare($check_offer);

    if (!$offer_check_stmt) {
        die("Database error: " . $conn->error);
    }

    $offer_check_stmt->bind_param("ii", $offer_id, $request_id);
    $offer_check_stmt->execute();

    $offer_check_result = $offer_check_stmt->get_result();

    if ($offer_check_result->num_rows == 0) {
        die("Invalid help offer.");
    }


    /* Accept selected offer */

    $update_offer = "UPDATE help_offers
                     SET status = 'Accepted'
                     WHERE id = ? AND request_id = ?";

    $update_offer_stmt = $conn->prepare($update_offer);

    if (!$update_offer_stmt) {
        die("Database error: " . $conn->error);
    }

    $update_offer_stmt->bind_param("ii", $offer_id, $request_id);

    if (!$update_offer_stmt->execute()) {
        die("Error accepting offer: " . $conn->error);
    }


    /* Reject all other offers */

    $reject_other = "UPDATE help_offers
                     SET status = 'Rejected'
                     WHERE request_id = ?
                     AND id != ?";

    $reject_stmt = $conn->prepare($reject_other);

    if (!$reject_stmt) {
        die("Database error: " . $conn->error);
    }

    $reject_stmt->bind_param("ii", $request_id, $offer_id);

    if (!$reject_stmt->execute()) {
        die("Error rejecting other offers: " . $conn->error);
    }


    /* Accept the request */

    $update_request = "UPDATE requests
                       SET status = 'Accepted'
                       WHERE id = ? AND user_id = ?";

    $update_request_stmt = $conn->prepare($update_request);

    if (!$update_request_stmt) {
        die("Database error: " . $conn->error);
    }

    $update_request_stmt->bind_param("ii", $request_id, $user_id);

    if (!$update_request_stmt->execute()) {
        die("Error updating request: " . $conn->error);
    }


    /* Reload page */

    header("Location: view_offers.php?request_id=" . $request_id . "&accepted=1");
    exit();
}


/* Get request */

$request_sql = "SELECT *
                FROM requests
                WHERE id = ? AND user_id = ?";

$request_stmt = $conn->prepare($request_sql);

if (!$request_stmt) {
    die("Database error: " . $conn->error);
}

$request_stmt->bind_param("ii", $request_id, $user_id);
$request_stmt->execute();

$request_result = $request_stmt->get_result();

if ($request_result->num_rows == 0) {
    die("You are not allowed to view offers for this request.");
}

$request = $request_result->fetch_assoc();


/* Get all offers with helper details */

$offer_sql = "SELECT help_offers.*,
                     users.name AS helper_name,
                     users.phone AS helper_phone,
                     users.email AS helper_email
              FROM help_offers
              INNER JOIN users
              ON help_offers.helper_id = users.id
              WHERE help_offers.request_id = ?
              ORDER BY help_offers.created_at DESC";

$offer_stmt = $conn->prepare($offer_sql);

if (!$offer_stmt) {
    die("Database error: " . $conn->error);
}

$offer_stmt->bind_param("i", $request_id);
$offer_stmt->execute();

$offer_result = $offer_stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>CareBridge - Help Offers</title>

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

        .request-summary {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .offer-card {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .helper-name {
            color: #6C63C9;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .contact-info {
            margin-top: 15px;
            padding: 18px;
            border-radius: 10px;
            background: #f5f3ff;
        }

        .contact-info h4 {
            color: #6C63C9;
            margin-top: 0;
        }

        .status {
            font-weight: bold;
            margin-top: 15px;
        }

        .accepted {
            color: #35A66F;
            font-weight: bold;
        }

        .rejected {
            color: #E94F64;
            font-weight: bold;
        }

        .pending {
            color: #FF9F1C;
            font-weight: bold;
        }

        .accept-btn {
            margin-top: 18px;
            padding: 11px 22px;
            background: #45B97C;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .accept-btn:hover {
            opacity: 0.9;
        }

        .success-message {
            background: #e8f8ef;
            color: #257a4d;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
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

        <h2>💝 Help Offers Received</h2>


        <?php if (isset($_GET["accepted"])) { ?>

            <div class="success-message">
                ✅ Help offer accepted successfully!
            </div>

        <?php } ?>


        <!-- Request Details -->

        <div class="request-summary">

            <h3>
                <?php echo htmlspecialchars($request["category"]); ?> Request
            </h3>

            <p>
                <strong>Description:</strong>
                <?php echo htmlspecialchars($request["description"]); ?>
            </p>

            <p>
                📍 <strong>Location:</strong>
                <?php echo htmlspecialchars($request["location"]); ?>
            </p>

            <p>
                🚨 <strong>Priority:</strong>
                <?php echo htmlspecialchars($request["priority"]); ?>
            </p>

            <p>
                📌 <strong>Status:</strong>
                <?php echo htmlspecialchars($request["status"]); ?>
            </p>

        </div>


        <?php

        if ($offer_result->num_rows > 0) {

            while ($offer = $offer_result->fetch_assoc()) {

        ?>

                <div class="offer-card">

                    <div class="helper-name">
                        👤 Helper:
                        <?php echo htmlspecialchars($offer["helper_name"]); ?>
                    </div>

                    <p>
                        💬 <strong>Help Message:</strong>
                    </p>

                    <p>
                        <?php echo htmlspecialchars($offer["message"]); ?>
                    </p>


                    <div class="contact-info">

                        <h4>📞 Contact Helper</h4>

                        <p>
                            📱 <strong>Phone:</strong>
                            <?php echo htmlspecialchars($offer["helper_phone"]); ?>
                        </p>

                        <p>
                            ✉️ <strong>Email:</strong>
                            <?php echo htmlspecialchars($offer["helper_email"]); ?>
                        </p>

                    </div>


                    <p class="status">

                        📌 Offer Status:

                        <?php echo htmlspecialchars($offer["status"]); ?>

                    </p>


                    <?php if ($offer["status"] == "Pending" && $request["status"] == "Pending") { ?>

                        <form method="POST">

                            <input type="hidden"
                                   name="offer_id"
                                   value="<?php echo $offer["id"]; ?>">

                            <button type="submit"
                                    name="accept_offer"
                                    class="accept-btn">

                                ✅ Accept Help

                            </button>

                        </form>


                    <?php } elseif ($offer["status"] == "Accepted") { ?>

                        <p class="accepted">
                            ✅ This help offer has been accepted.
                        </p>


                    <?php } elseif ($offer["status"] == "Rejected") { ?>

                        <p class="rejected">
                            ❌ This help offer was not selected.
                        </p>

                    <?php } ?>

                </div>

        <?php

            }

        } else {

            echo "<p>No one has offered help for this request yet.</p>";

        }

        ?>


        <a class="back-btn" href="myrequests.php">
            ← Back to My Requests
        </a>

    </div>

</body>

</html>