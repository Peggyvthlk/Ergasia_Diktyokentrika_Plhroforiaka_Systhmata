<?php include('../../includes/header.php');


// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../pages/login.php');
    exit;
}
?>

<h3>Ανακοινώσεις</h3>
<!-- Display existing announcements -->
<div class="announcements-container" id="announcements-container">

    <?php
    // URL of the Flask API endpoint for announcements
    $url = "http://127.0.0.1:5000/api/announcements";

    // Initialize cURL
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPGET, true);

    // Execute cURL request and get the response
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        echo '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    } else {
        // Decode the JSON response
        $data = json_decode($response, true);

        // Check if the data is an array and loop through it
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $announcement) {
                echo '<div class="announcement-card" id="announcement-' . htmlspecialchars($announcement['id']) . '">';
                echo '<h3 class="announcement-title">' . htmlspecialchars($announcement['title']) . '</h3>';
                echo '<p class="announcement-content">' . nl2br(htmlspecialchars($announcement['content'])) . '</p>';
                echo '<p class="announcement-date">Posted on: ' . htmlspecialchars($announcement['created_at']) . '</p>';

                echo '</div>';
            }
        } else {
            echo '<div class="no-announcements">No announcements found</div>';
        }
    }

    // Close cURL session
    curl_close($curl);
    ?>
</div>

<?php include('../../includes/footer.php'); ?>