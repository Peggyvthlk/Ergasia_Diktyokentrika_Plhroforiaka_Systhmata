<?php include('../includes/header.php'); ?>

<div class="container" style="position: relative;">
    <?php
    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo '<p>' . htmlspecialchars($_SESSION['logged_in']) . '</p>';
        // header('Location: ../index.php');
        // exit;
    }
    ?>
    <h2 style="margin-left: 70px;">Καλώς Ήρθατε στο Γυμναστήριο</h2>
    <p style="margin-left: 70px;">Παρακάτω μπορείτε να δείτε τις υπηρεσίες του γυμαστηρίου μας.</p>

    <!-- Section to display gym services -->
    <h3 style="margin-left: 70px;">Gym Services:</h3>
    <div id="services" style="margin-left: 70px;">
        <?php
        // URL of the Flask API endpoint for gym services
        $url = "http://127.0.0.1:5000/api/gym_services";

        // Initialize cURL
        $curl = curl_init($url);

        // Set cURL options
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);

        // Execute cURL request and get the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
        } else {
            // Decode the JSON response
            $data = json_decode($response, true);

            // Check if the data is an array and loop through it
            if (is_array($data) && count($data) > 0) {
                echo '<div class="services-table-container">';
                foreach ($data as $service) {
                    echo '<div class="service-card">';
                    echo '<h4>' . htmlspecialchars($service['name']) . '</h4>';
                    if ($service['class_type'] == 'Group') {
                        echo '<img src="../images/users.png" width="50" alt="group">';
                    } else if ($service['class_type'] == 'Personal') {
                        echo '<img src="../images/strongman.png" width="50" alt="group">';
                    } else {
                        echo '<img src="../images/gym.png" width="50" alt="group">';
                    }

                    echo '<p>' . htmlspecialchars($service['description']) . '</p>';
                    echo '<div class="max-capacity">Max Capacity: ' . htmlspecialchars($service['max_capacity']) . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="no-services">No services found</div>';
            }
        }

        // Close cURL session
        curl_close($curl);
        ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>