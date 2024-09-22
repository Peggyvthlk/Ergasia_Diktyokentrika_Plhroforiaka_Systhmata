<?php include('../../includes/header.php'); ?>

<?php
$alert = '';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../pages/login.php');
    exit;
}

// Fetch existing programs from the Flask API
$url = "http://127.0.0.1:5000/api/programs";

// Initialize cURL
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPGET, true);

// Execute cURL request and get the response
$response = curl_exec($curl);

// Check for cURL errors
if (curl_errno($curl)) {
    $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
} else {
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // Decode the JSON response
    $data = json_decode($response, true);

    // Check the HTTP status code and data
    if ($http_status == 200 && is_array($data)) {
        $programs = $data;
    } else {
        $alert = '<div class="alert error">Failed to retrieve programs. Status code: ' . $http_status . '</div>';
        $programs = [];
    }
}

// Close cURL session
curl_close($curl);
?>

<div class="container">
    <h2>View Programs</h2>
    <p>Assume that all programs last for 1 hour</p>
    <?php if ($alert) echo $alert; ?>

    <!-- Display existing programs -->
    <div class="programs-container" id="programs-container">
        <?php
        if (count($programs) > 0) {
            foreach ($programs as $program) {
                $service = $program['service'];
                $service_name = htmlspecialchars($service['name']);
                $service_description = htmlspecialchars($service['description']);
                $service_class_type = htmlspecialchars($service['class_type']);
                $timeslot = htmlspecialchars($program['timeslot']);

                echo '<div class="program-card">';
                echo '<h3 class="program-service-name">Service Name: ' . $service_name . '</h3>';
                echo '<p class="program-service-description">Description: ' . $service_description . '</p>';
                echo '<p class="program-class-type">Class Type: ' . $service_class_type . '</p>';
                echo '<p class="program-timeslot">Timeslot: ' . $timeslot . '</p>';
                echo '</div>';
                echo '<br>';
            }
        } else {
            echo '<div class="no-programs">No programs available</div>';
        }
        ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>