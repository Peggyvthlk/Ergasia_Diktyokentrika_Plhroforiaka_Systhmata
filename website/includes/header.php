<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management System</title>
    <link rel="stylesheet" href="/website/css/style.css"> <!-- Link to your CSS file -->
</head>

<body>
    <?php
    // Start the session
    session_start();
    ?>

    <?php
    if (!isset($_SESSION['role']) || $_SESSION['role'] == null) {
        include('user_navbar.php');
        echo '<header>

            <h1>Καλώς ήρθατε στο Γυμναστήριο</h1>

            </header>';
    } else if ($_SESSION['role'] == 'user') {
        include('user_navbar.php');
        echo '<header>

            <h1>Καλώς ήρθατε στο Γυμναστήριο</h1>

            </header>';
    } else {
        include('navbar.php');
        echo '<header>

            <h1>Σύστημα Διαχείρισης Γυμναστηρίου</h1>

            </header>';
    }
    ?>