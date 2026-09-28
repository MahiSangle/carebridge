<?php

session_start();

include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


/* ================================
   GET LOGGED-IN USER DETAILS
   ================================ */

$sql = "
    SELECT name, email, phone, role, created_at
    FROM users
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("User details not found.");
}

$user = $result->fetch_assoc();

$stmt->close();


/* ================================
   MASK PHONE NUMBER
   ================================ */

$phone = $user["phone"];

if (!empty($phone) && strlen($phone) >= 4) {

    $masked_phone =
        str_repeat("*", strlen($phone) - 4)
        . substr($phone, -4);

} else {

    $masked_phone = "Private";

}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - CareBridge</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .profile-header {
            background: linear-gradient(135deg, #6C63C9, #8B7FE8);
            color: white;
            text-align: center;
            padding: 45px 20px;
            border-radius: 0 0 25px 25px;
        }

        .profile-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .profile-header p {
            margin: 0;
            font-size: 17px;
        }

        .profile-container {
            width: 90%;
            max-width: 750px;
            margin: 40px auto;
        }

        .profile-card {
            background: white;
            padding: 40px;
            border-radius: 22px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            text-align: center;
        }

        .profile-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #F5F2FF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .profile-card h2 {
            color: #6C63C9;
            margin: 10px 0;
        }

        .role {
            display: inline-block;
            background: #F5F2FF;
            color: #6C63C9;
            padding: 7px 18px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .details {
            text-align: left;
            margin-top: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 18px;
            margin-bottom: 12px;
            background: #FAF9FF;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        .detail-label {
            font-weight: bold;
            color: #6C63C9;
        }

        .detail-value {
            color: #444;
            text-align: right;
            word-break: break-word;
        }

        .private {
            color: #777;
        }

        .profile-note {
            margin-top: 25px;
            padding: 18px;
            background: #F5F2FF;
            border-radius: 12px;
            color: #666;
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

        @media (max-width: 600px) {

            .profile-container {
                width: 94%;
            }

            .profile-card {
                padding: 25px;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .detail-value {
                text-align: left;
            }

        }

    </style>

</head>


<body>


<div class="profile-header">

    <h1>👤 My Profile</h1>

    <p>
        View your CareBridge account information.
    </p>

</div>


<div class="profile-container">


    <div class="profile-card">


        <div class="profile-icon">
            👤
        </div>


        <h2>
            <?php echo htmlspecialchars($user["name"]); ?>
        </h2>


        <div class="role">

            <?php echo htmlspecialchars($user["role"]); ?>

        </div>


        <div class="details">


            <!-- NAME -->

            <div class="detail-row">

                <span class="detail-label">
                    👤 Name
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["name"]); ?>
                </span>

            </div>


            <!-- EMAIL -->

            <div class="detail-row">

                <span class="detail-label">
                    📧 Email
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["email"]); ?>
                </span>

            </div>


            <!-- PHONE -->

            <div class="detail-row">

                <span class="detail-label">
                    📱 Phone
                </span>

                <span class="detail-value private">
                    🔒 <?php echo htmlspecialchars($masked_phone); ?>
                </span>

            </div>


            <!-- ROLE -->

            <div class="detail-row">

                <span class="detail-label">
                    👤 Account Type
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["role"]); ?>
                </span>

            </div>


            <!-- MEMBER SINCE -->

            <div class="detail-row">

                <span class="detail-label">
                    📅 Member Since
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["created_at"]); ?>
                </span>

            </div>


        </div>


        <div class="profile-note">

            🔒 Your phone number is kept private and is shown in masked form.

        </div>


    </div>


    <div class="back-button">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>