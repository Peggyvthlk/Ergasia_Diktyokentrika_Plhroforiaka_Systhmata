<!-- index.php -->
<?php include('includes/header.php'); ?>

<div class="container">
    <?php
    // session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: pages/login.php');
        exit;
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Welcome</title>
    </head>

    <body>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>You are logged in.</p>
        <a href="pages/logout.php">Logout</a>
    </body>

    </html>

</div>

<?php include('includes/footer.php'); ?>