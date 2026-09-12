<?php

session_start();
include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/* Only admin can access */
if ($_SESSION["role"] != "admin") {
    header("Location: dashboard.php");
    exit();
}


/* Get all requests with requester name, role and offer count */

$sql = "SELECT requests.*,
               users.name AS requester_name,
               users.role AS requester_role,
               (SELECT COUNT(*)
                FROM help_offers
                WHERE help_offers.request_id = requests.id) AS offer_count
        FROM requests
        INNER JOIN users
        ON requests.user_id = users.id
        ORDER BY
            CASE
                WHEN requests.priority = 'High' THEN 1
                WHEN requests.priority = 'Medium' THEN 2
                WHEN requests.priority = 'Low' THEN 3
                ELSE 4
            END,
            requests.created_at DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Admin - All Requests | CareBridge</title>

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


        /* HEADER */

        .header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
        }

        .header h1 {
            margin: 0 0 10px;
            font-size: 36px;
        }

        .header p {
            margin: 0;
            font-size: 16px;
        }


        /* CONTAINER */

        .container {
            width: 92%;
            max-width: 1250px;
            margin: 40px auto;
        }


        /* INTRO */

        .intro {
            background: white;
            padding: 28px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .intro h2 {
            color: #6C63C9;
            margin-top: 0;
        }

        .intro p {
            color: #666;
        }


        /* REQUEST CARD */

        .request-card {
            background: white;
            padding: 25px;
            margin-bottom: 22px;
            border-radius: 18px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            border-left: 6px solid #6C63C9;
        }

        .blood {
            border-left-color: #E94F64;
        }

        .food {
            border-left-color: #FF9F43;
        }

        .clothes {
            border-left-color: #35A66F;
        }


        /* CATEGORY */

        .category {
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 18px;
        }

        .blood-text {
            color: #E94F64;
        }

        .food-text {
            color: #FF9F43;
        }

        .clothes-text {
            color: #35A66F;
        }


        /* INFORMATION */

        .info {
            margin: 10px 0;
            line-height: 1.5;
        }

        .label {
            font-weight: bold;
        }


        /* ROLE */

        .role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            background: #F0ECFF;
            color: #6C63C9;
            font-size: 13px;
            font-weight: bold;
            margin-left: 5px;
        }


        /* PRIORITY */

        .high {
            color: #E94F64;
            font-weight: bold;
        }

        .medium {
            color: #FF9F43;
            font-weight: bold;
        }

        .low {
            color: #35A66F;
            font-weight: bold;
        }


        /* STATUS */

        .pending {
            color: #FF9F43;
            font-weight: bold;
        }

        .accepted {
            color: #35A66F;
            font-weight: bold;
        }


        /* OFFER COUNT */

        .offers {
            margin-top: 18px;
            padding: 12px;
            background: #F5F2FF;
            border-radius: 10px;
            color: #6C63C9;
            font-weight: bold;
        }


        /* EMPTY */

        .empty {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 18px;
            color: #777;
        }


        /* BACK */

        .back {
            text-align: center;
            margin: 40px 0;
        }

        .back a {
            display: inline-block;
            background: #6C63C9;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: bold;
        }

        .back a:hover {
            background: #574FB3;
        }


        /* MOBILE */

        @media (max-width: 700px) {

            .container {
                width: 94%;
            }

            .header h1 {
                font-size: 29px;
            }

        }

    </style>

</head>


<body>


<!-- HEADER -->

<div class="header">

    <h1>👑 All Help Requests</h1>

    <p>
        Admin Request Management
    </p>

</div>


<div class="container">


    <!-- INTRO -->

    <div class="intro">

        <h2>
            📋 Community Request Management
        </h2>

        <p>
            Monitor all requests submitted through CareBridge,
            including their priority, status and received help offers.
        </p>

    </div>


    <?php if ($result && $result->num_rows > 0) { ?>


        <?php while ($row = $result->fetch_assoc()) { ?>


            <?php

            if ($row["category"] == "Blood") {

                $card_class = "blood";
                $text_class = "blood-text";
                $icon = "🩸";

            } elseif ($row["category"] == "Food") {

                $card_class = "food";
                $text_class = "food-text";
                $icon = "🍱";

            } else {

                $card_class = "clothes";
                $text_class = "clothes-text";
                $icon = "👕";

            }

            $priority_class = strtolower($row["priority"]);

            $status_class = strtolower($row["status"]);

            ?>


            <div class="request-card <?php echo $card_class; ?>">


                <!-- CATEGORY -->

                <div class="category <?php echo $text_class; ?>">

                    <?php echo $icon; ?>

                    <?php echo htmlspecialchars($row["category"]); ?>

                    Request

                </div>


                <!-- REQUESTER -->

                <div class="info">

                    👤

                    <span class="label">
                        Requested by:
                    </span>

                    <?php echo htmlspecialchars($row["requester_name"]); ?>


                    <?php if ($row["requester_role"] == "ngo") { ?>

                        <span class="role">
                            🏢 NGO
                        </span>

                    <?php } else { ?>

                        <span class="role">
                            👤 Individual
                        </span>

                    <?php } ?>

                </div>


                <!-- DESCRIPTION -->

                <div class="info">

                    📝

                    <span class="label">
                        Requirement:
                    </span>

                    <?php echo htmlspecialchars($row["description"]); ?>

                </div>


                <!-- LOCATION -->

                <div class="info">

                    📍

                    <span class="label">
                        Location:
                    </span>

                    <?php echo htmlspecialchars($row["location"]); ?>

                </div>


                <!-- PRIORITY -->

                <div class="info">

                    🤖

                    <span class="label">
                        AI Priority:
                    </span>

                    <span class="<?php echo $priority_class; ?>">

                        <?php echo htmlspecialchars($row["priority"]); ?>

                    </span>

                </div>


                <!-- STATUS -->

                <div class="info">

                    📌

                    <span class="label">
                        Request Status:
                    </span>

                    <?php if ($row["status"] == "Accepted") { ?>

                        <span class="accepted">
                            ✅ Accepted
                        </span>

                    <?php } else { ?>

                        <span class="pending">
                            ⏳ Pending
                        </span>

                    <?php } ?>

                </div>


                <!-- OFFERS -->

                <div class="offers">

                    💝 Help Offers Received:

                    <?php echo $row["offer_count"]; ?>

                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


        <div class="empty">

            <h2>
                🌱 No Requests Found
            </h2>

            <p>
                No help requests have been submitted yet.
            </p>

        </div>


    <?php } ?>


    <!-- BACK -->

    <div class="back">

        <a href="admin.php">
            ← Back to Admin Dashboard
        </a>

    </div>


</div>


</body>

</html>