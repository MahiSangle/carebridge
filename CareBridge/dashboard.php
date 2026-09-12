<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["name"];
$role = $_SESSION["role"];

?>

<!DOCTYPE html>
<html>

<head>

    <title>CareBridge - Dashboard</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="dashboard-page">

    <!-- Background Effects -->

    <div class="background-glow glow1"></div>
    <div class="background-glow glow2"></div>


    <!-- Main Dashboard -->

    <div class="dashboard-container">


        <!-- Logo -->

        <div class="logo">

            <div class="logo-icon">🤝</div>

            <h1>CareBridge</h1>

        </div>


        <!-- Hero Section -->

        <div class="hero-section">

            <div class="welcome-badge">

                ✨ WELCOME TO THE COMMUNITY

            </div>


            <h2>
                Hello, <?php echo htmlspecialchars($name); ?>! 👋
            </h2>


            <p class="hero-text">

                Every act of kindness creates a ripple of hope.
                Connect, support, and make a real difference. 💙

            </p>


            <p class="role">

                👤 Your Role:
                <strong>
                    <?php echo htmlspecialchars($role); ?>
                </strong>

            </p>

        </div>


        <!-- Section Title -->

        <div class="action-heading">

            <span>⚡</span>

            <h3>How would you like to make an impact today?</h3>

        </div>


        <!-- Action Cards -->

        <div class="action-grid">


            <!-- Request Help -->

            <a href="request.php" class="action-card">

                <div class="card-icon">
                    🆘
                </div>

                <h3>Request Help</h3>

                <p>
                    Get support from people who care.
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>



            <!-- Offer Help -->

            <a href="offer.php" class="action-card">

                <div class="card-icon">
                    🤝
                </div>

                <h3>Offer Help</h3>

                <p>
                    Your small action can change someone's day.
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>



            <!-- View Requests -->

            <a href="requests.php" class="action-card">

                <div class="card-icon">
                    🌍
                </div>

                <h3>Explore Requests</h3>

                <p>
                    Discover people and communities needing support.
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>



            <!-- My Requests -->

            <a href="myrequests.php" class="action-card">

                <div class="card-icon">
                    📋
                </div>

                <h3>My Requests</h3>

                <p>
                    Track and manage your help requests.
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>



            <!-- Admin -->

            <a href="admin.php" class="action-card admin-card">

                <div class="card-icon">
                    ⚙️
                </div>

                <h3>Admin Dashboard</h3>

                <p>
                    Manage and monitor the CareBridge platform.
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>


        </div>


        <!-- Motivation Section -->

        <div class="impact-section">

            <div class="impact-icon">
                💙
            </div>

            <div>

                <h3>One Connection. One Act. One Impact.</h3>

                <p>
                    Together, we can build a stronger and more caring community.
                </p>

            </div>

        </div>


        <!-- Logout -->

        <div class="logout-section">

            <a href="logout.php" class="logout-button">

                🚪 Logout

            </a>

        </div>


        <!-- Footer -->

        <div class="dashboard-footer">

            🌐 CareBridge • Connecting People Through Kindness

        </div>


    </div>

</body>

</html>