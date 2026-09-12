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


/* =========================
   STATISTICS
   ========================= */

$user_sql = "SELECT COUNT(*) AS total_users
             FROM users
             WHERE role != 'admin'";

$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();
$total_users = $user_data["total_users"];


$request_sql = "SELECT COUNT(*) AS total_requests
                FROM requests";

$request_result = $conn->query($request_sql);
$request_data = $request_result->fetch_assoc();
$total_requests = $request_data["total_requests"];


$offer_sql = "SELECT COUNT(*) AS total_offers
              FROM help_offers";

$offer_result = $conn->query($offer_sql);
$offer_data = $offer_result->fetch_assoc();
$total_offers = $offer_data["total_offers"];


$accepted_sql = "SELECT COUNT(*) AS accepted
                 FROM help_offers
                 WHERE status = 'Accepted'";

$accepted_result = $conn->query($accepted_sql);
$accepted_data = $accepted_result->fetch_assoc();
$total_accepted = $accepted_data["accepted"];


$high_sql = "SELECT COUNT(*) AS high_requests
             FROM requests
             WHERE priority = 'High'
             AND status = 'Pending'";

$high_result = $conn->query($high_sql);
$high_data = $high_result->fetch_assoc();
$high_requests = $high_data["high_requests"];


/* Recent requests */

$recent_sql = "SELECT requests.*,
                      users.name AS requester_name,
                      users.role AS requester_role
               FROM requests
               INNER JOIN users
               ON requests.user_id = users.id
               ORDER BY requests.created_at DESC
               LIMIT 5";

$recent_result = $conn->query($recent_sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Admin Dashboard - CareBridge</title>

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
            padding: 55px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 40px;
        }

        .header p {
            margin-top: 12px;
            font-size: 17px;
        }


        /* CONTAINER */

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }


        /* WELCOME */

        .welcome {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            margin-bottom: 35px;
        }

        .welcome h2 {
            color: #6C63C9;
            margin-top: 0;
        }

        .welcome p {
            color: #666;
        }


        /* STATISTICS */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .stat-icon {
            font-size: 35px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #6C63C9;
            margin-top: 8px;
        }

        .stat-title {
            color: #666;
            margin-top: 5px;
            font-weight: bold;
        }


        /* HIGH PRIORITY */

        .urgent {
            margin-top: 25px;
            background: #FFE8EC;
            border-left: 6px solid #E94F64;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .urgent strong {
            color: #E94F64;
        }


        /* MANAGEMENT */

        .section-title {
            text-align: center;
            margin: 55px 0 25px;
        }

        .section-title h2 {
            color: #6C63C9;
        }

        .section-title p {
            color: #666;
        }


        .management {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .management-card {
            background: white;
            padding: 30px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .management-card .icon {
            font-size: 42px;
        }

        .management-card h3 {
            color: #6C63C9;
        }

        .management-card p {
            color: #666;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            background: #6C63C9;
            color: white;
            padding: 13px 25px;
            border-radius: 10px;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn:hover {
            background: #574FB3;
        }


        /* RECENT REQUESTS */

        .recent-box {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        .request-row {
            padding: 18px;
            border-bottom: 1px solid #eee;
        }

        .request-row:last-child {
            border-bottom: none;
        }

        .request-row strong {
            color: #6C63C9;
        }

        .priority-high {
            color: #E94F64;
            font-weight: bold;
        }

        .priority-medium {
            color: #FF9F43;
            font-weight: bold;
        }

        .priority-low {
            color: #35A66F;
            font-weight: bold;
        }

        .status {
            color: #6C63C9;
            font-weight: bold;
        }


        /* BACK */

        .back {
            text-align: center;
            margin: 40px 0;
        }

        .back a {
            color: #6C63C9;
            text-decoration: none;
            font-weight: bold;
        }


        @media (max-width: 700px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .management {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 30px;
            }

        }

    </style>

</head>


<body>


<!-- HEADER -->

<div class="header">

    <h1>👑 CareBridge</h1>

    <p>Admin Dashboard</p>

</div>


<div class="container">


    <!-- WELCOME -->

    <div class="welcome">

        <h2>Welcome, Administrator 👋</h2>

        <p>
            Monitor the CareBridge community, requests, help offers and their progress.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                👥
            </div>

            <div class="stat-number">
                <?php echo $total_users; ?>
            </div>

            <div class="stat-title">
                Community Users
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🆘
            </div>

            <div class="stat-number">
                <?php echo $total_requests; ?>
            </div>

            <div class="stat-title">
                Total Requests
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                💝
            </div>

            <div class="stat-number">
                <?php echo $total_offers; ?>
            </div>

            <div class="stat-title">
                Help Offers
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <div class="stat-number">
                <?php echo $total_accepted; ?>
            </div>

            <div class="stat-title">
                Accepted Helps
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🔴
            </div>

            <div class="stat-number">
                <?php echo $high_requests; ?>
            </div>

            <div class="stat-title">
                High Priority Pending
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🤝
            </div>

            <div class="stat-number">
                <?php echo $total_accepted; ?>
            </div>

            <div class="stat-title">
                Successful Connections
            </div>

        </div>

    </div>


    <!-- URGENT -->

    <?php if ($high_requests > 0) { ?>

        <div class="urgent">

            🚨

            <strong>
                <?php echo $high_requests; ?>
            </strong>

            high-priority request(s) currently need attention.

        </div>

    <?php } else { ?>

        <div class="urgent">

            ✅

            No high-priority pending requests currently need attention.

        </div>

    <?php } ?>


    <!-- MANAGEMENT -->

    <div class="section-title">

        <h2>🛠️ Admin Management</h2>

        <p>
            Manage and monitor the CareBridge platform.
        </p>

    </div>


    <div class="management">


        <div class="management-card">

            <div class="icon">
                📋
            </div>

            <h3>
                View All Requests
            </h3>

            <p>
                View every help request with requester,
                category, location, priority and current status.
            </p>

            <a
                class="btn"
                href="admin_requests.php"
            >
                View Requests
            </a>

        </div>


        <div class="management-card">

            <div class="icon">
                👥
            </div>

            <h3>
                Community Overview
            </h3>

            <p>
                Monitor registered users and the activity
                taking place across CareBridge.
            </p>

            <a
                class="btn"
                href="admin_requests.php"
            >
                Monitor Activity
            </a>

        </div>


    </div>


    <!-- RECENT REQUESTS -->

    <div class="section-title">

        <h2>🕒 Recent Requests</h2>

        <p>
            Latest requests submitted by the CareBridge community.
        </p>

    </div>


    <div class="recent-box">


        <?php if ($recent_result->num_rows > 0) { ?>


            <?php while ($row = $recent_result->fetch_assoc()) { ?>


                <div class="request-row">

                    <strong>
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </strong>

                    — 

                    <?php echo htmlspecialchars($row["description"]); ?>

                    <br>

                    👤
                    <?php echo htmlspecialchars($row["requester_name"]); ?>

                    |

                    📍
                    <?php echo htmlspecialchars($row["location"]); ?>

                    |

                    🎯

                    <span class="priority-<?php echo strtolower($row["priority"]); ?>">

                        <?php echo htmlspecialchars($row["priority"]); ?>

                    </span>

                    |

                    📌

                    <span class="status">

                        <?php echo htmlspecialchars($row["status"]); ?>

                    </span>

                </div>


            <?php } ?>


        <?php } else { ?>


            <div class="request-row">

                No requests have been created yet.

            </div>


        <?php } ?>


    </div>


    <!-- BACK -->

    <div class="back">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>