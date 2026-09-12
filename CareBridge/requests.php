<?php

session_start();
include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


/* =========================
   PENDING COMMUNITY REQUESTS
   ========================= */

$sql = "SELECT requests.*,
               users.name AS requester_name,
               users.role AS requester_role
        FROM requests
        INNER JOIN users
        ON requests.user_id = users.id
        WHERE requests.user_id != ?
        AND requests.status = 'Pending'
        ORDER BY
            CASE
                WHEN requests.priority = 'High' THEN 1
                WHEN requests.priority = 'Medium' THEN 2
                WHEN requests.priority = 'Low' THEN 3
                ELSE 4
            END,
            requests.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();


/* =========================
   COMMUNITY STATISTICS
   ========================= */

/* Successful accepted help */
$help_sql = "SELECT COUNT(*) AS total_help
             FROM help_offers
             WHERE status = 'Accepted'";

$help_result = $conn->query($help_sql);
$help_data = $help_result->fetch_assoc();

$total_help = $help_data["total_help"];


/* Total requests */
$request_sql = "SELECT COUNT(*) AS total_requests
                FROM requests";

$request_result = $conn->query($request_sql);
$request_data = $request_result->fetch_assoc();

$total_requests = $request_data["total_requests"];


/* Total community members */
$user_sql = "SELECT COUNT(*) AS total_users
             FROM users
             WHERE role != 'admin'";

$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();

$total_users = $user_data["total_users"];


/* =========================
   PEOPLE WHO HELPED
   ========================= */

$helper_sql = "SELECT users.name AS helper_name,
                      requests.category,
                      help_offers.message,
                      help_offers.created_at
               FROM help_offers
               INNER JOIN users
               ON help_offers.helper_id = users.id
               INNER JOIN requests
               ON help_offers.request_id = requests.id
               WHERE help_offers.status = 'Accepted'
               ORDER BY help_offers.created_at DESC
               LIMIT 6";

$helper_result = $conn->query($helper_sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Explore Requests - CareBridge</title>

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

        /* HERO */

        .hero {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 65px 20px;
        }

        .hero h1 {
            font-size: 42px;
            margin: 0 0 15px;
        }

        .hero p {
            font-size: 18px;
            max-width: 750px;
            margin: auto;
            line-height: 1.7;
        }


        /* CONTAINER */

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }


        /* IMPACT MESSAGE */

        .impact-message {
            background: white;
            padding: 35px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            margin-bottom: 35px;
        }

        .impact-message h2 {
            color: #6C63C9;
            font-size: 30px;
            margin-top: 0;
        }

        .impact-message p {
            color: #666;
            font-size: 17px;
            line-height: 1.6;
        }


        /* STATISTICS */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 25px;
        }

        .stat-card {
            padding: 25px;
            border-radius: 16px;
            background: #F5F2FF;
        }

        .stat-number {
            font-size: 35px;
            font-weight: bold;
            color: #6C63C9;
        }

        .stat-title {
            margin-top: 8px;
            color: #555;
            font-weight: bold;
        }


        /* SECTION */

        .section-title {
            text-align: center;
            margin: 55px 0 25px;
        }

        .section-title h2 {
            color: #6C63C9;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .section-title p {
            color: #666;
        }


        /* REQUEST CARDS */

        .request-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
        }

        .request-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border-top: 6px solid #6C63C9;
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

        .info {
            margin: 11px 0;
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


        /* OFFER BUTTON */

        .offer-btn {
            display: block;
            text-align: center;
            background: #6C63C9;
            color: white;
            text-decoration: none;
            padding: 13px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: bold;
        }

        .offer-btn:hover {
            background: #574FB3;
        }


        /* EMPTY */

        .empty {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 18px;
            color: #777;
        }


        /* HELPERS */

        .helpers-section {
            margin-top: 60px;
        }

        .helper-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .helper-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .helper-icon {
            font-size: 40px;
        }

        .helper-card h3 {
            margin: 12px 0 8px;
            color: #6C63C9;
        }

        .helper-card p {
            color: #666;
        }

        .accepted {
            color: #35A66F;
            font-weight: bold;
        }


        /* PRIVACY */

        .privacy {
            margin-top: 25px;
            padding: 15px;
            border-radius: 10px;
            background: #FFF4D8;
            color: #765900;
            text-align: center;
            font-size: 14px;
        }


        /* MOBILE */

        @media (max-width: 700px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 30px;
            }

            .container {
                width: 94%;
            }

        }

    </style>

</head>


<body>


<!-- HERO -->

<div class="hero">

    <h1>🌍 Explore Requests</h1>

    <p>
        Every request is a chance to make a difference.
        Discover people and NGOs who need support and become part of a community that cares.
    </p>

</div>


<div class="container">


    <!-- STRONG MESSAGE -->

    <div class="impact-message">

        <h2>
            ❤️ Every Act of Kindness Creates an Impact
        </h2>

        <p>
            CareBridge brings together people who need help
            and people who are ready to help.
            Together, we can turn a request into hope.
        </p>


        <!-- STATISTICS -->

        <div class="stats">

            <div class="stat-card">

                <div class="stat-number">
                    <?php echo $total_help; ?>
                </div>

                <div class="stat-title">
                    🤝 Successful Helps
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">
                    <?php echo $total_requests; ?>
                </div>

                <div class="stat-title">
                    🆘 Requests Created
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">
                    <?php echo $total_users; ?>
                </div>

                <div class="stat-title">
                    👥 Community Members
                </div>

            </div>

        </div>

    </div>


    <!-- CURRENT REQUESTS -->

    <div class="section-title">

        <h2>
            🆘 People Who Need Help
        </h2>

        <p>
            Urgent requests appear first so the community can respond faster.
        </p>

    </div>


    <?php if ($result->num_rows > 0) { ?>


        <div class="request-grid">


            <?php while ($row = $result->fetch_assoc()) { ?>


                <?php

                if ($row["category"] == "Blood") {

                    $card_class = "blood-card";
                    $text_class = "blood-text";
                    $icon = "🩸";

                } elseif ($row["category"] == "Food") {

                    $card_class = "food-card";
                    $text_class = "food-text";
                    $icon = "🍱";

                } else {

                    $card_class = "clothes-card";
                    $text_class = "clothes-text";
                    $icon = "👕";

                }

                $priority_class = strtolower($row["priority"]);

                ?>


                <div
                    class="request-card <?php echo $card_class; ?>"
                >


                    <div class="category <?php echo $text_class; ?>">

                        <?php echo $icon; ?>

                        <?php echo htmlspecialchars($row["category"]); ?>

                        Request

                    </div>


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


                    <div class="info">

                        📝

                        <span class="label">
                            Requirement:
                        </span>

                        <?php echo htmlspecialchars($row["description"]); ?>

                    </div>


                    <div class="info">

                        📍

                        <span class="label">
                            Location:
                        </span>

                        <?php echo htmlspecialchars($row["location"]); ?>

                    </div>


                    <div class="info">

                        🎯

                        <span class="label">
                            Priority:
                        </span>

                        <span class="<?php echo $priority_class; ?>">

                            <?php echo htmlspecialchars($row["priority"]); ?>

                        </span>

                    </div>


                    <div class="info">

                        📌

                        <span class="label">
                            Status:
                        </span>

                        <span class="medium">
                            <?php echo htmlspecialchars($row["status"]); ?>
                        </span>

                    </div>


                    <a
                        class="offer-btn"
                        href="offer.php?request_id=<?php echo $row["id"]; ?>"
                    >

                        💝 Offer Help

                    </a>


                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <div class="empty">

            <h2>
                🌱 No Pending Requests
            </h2>

            <p>
                There are currently no requests from other community members.
            </p>

        </div>


    <?php } ?>


    <!-- PEOPLE WHO HELPED -->

    <div class="helpers-section">


        <div class="section-title">

            <h2>
                🏆 People Who Made A Difference
            </h2>

            <p>
                These community members stepped forward and their help was accepted.
            </p>

        </div>


        <?php if ($helper_result->num_rows > 0) { ?>


            <div class="helper-grid">


                <?php while ($helper = $helper_result->fetch_assoc()) { ?>


                    <div class="helper-card">

                        <div class="helper-icon">
                            🤝
                        </div>


                        <h3>

                            <?php echo htmlspecialchars($helper["helper_name"]); ?>

                        </h3>


                        <p>

                            Helped with

                            <strong>

                                <?php echo htmlspecialchars($helper["category"]); ?>

                            </strong>

                        </p>


                        <p class="accepted">

                            ✅ Help Accepted

                        </p>

                    </div>


                <?php } ?>


            </div>


            <div class="privacy">

                🔒 Helper contact details are kept private.
                They are shared with the requester after an offer is accepted.

            </div>


        <?php } else { ?>


            <div class="empty">

                <h3>
                    💚 Be the First to Make a Difference
                </h3>

                <p>
                    Accepted helpers will appear here as proof of community impact.
                </p>

            </div>


        <?php } ?>


    </div>


</div>


</body>

</html>