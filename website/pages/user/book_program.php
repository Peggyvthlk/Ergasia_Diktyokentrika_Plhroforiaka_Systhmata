<?php include('../../includes/header.php'); ?>

<?php


// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../pages/login.php');
    exit;
}

$alert = '';
$username = $_SESSION['username'] ?? null; // Assuming you store username in session

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    if ($username === null) {
        $alert = '<div class="alert error">User not identified.</div>';
    } else {
        $program_id = intval($_POST['program_id']);
        $status = 'Pending'; // Default status

        // Create the booking data
        $booking_data = [
            'username' => $username,
            'schedule_id' => $program_id,
            'status' => $status,
            'booking_time' => date('Y-m-d H:i:s') // Current time
        ];

        // Flask API URL
        $url = "http://127.0.0.1:5000/api/bookings";

        // Initialize cURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($booking_data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Execute cURL request and get the response
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
        } else {
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($http_status == 201) {
                $alert = '<div class="alert success">Booking successful!</div>';
            } else if ($http_status == 403) {
                $alert = '<div class="alert error">You canceled more than twice in the past week!</div>';
            } else {
                $alert = '<div class="alert error">Failed to book program. Status code: ' . $http_status . '</div>';
            }
        }

        // Close cURL session
        curl_close($curl);
    }
}

// Fetch programs from the Flask API to populate the booking form
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
    $programs = [];
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
    <h2>Book a Program</h2>

    <?php if ($alert) echo $alert; ?>

    <!-- Booking Form -->
    <div class="form-container">
        <h3>Select Program to Book</h3>
        <form id="booking-form" method="post" action="book_program.php">
            <div class="form-group">
                <label for="program_id">Select Program:</label>
                <select id="program_id" name="program_id" class="form-control" required>
                    <?php
                    if (count($programs) > 0) {
                        foreach ($programs as $program) {
                            $program_id = htmlspecialchars($program['id']);
                            $service_name = htmlspecialchars($program['service']['name']);
                            $timeslot = htmlspecialchars($program['timeslot']);
                            echo '<option value="' . $program_id . '">' . $service_name . ' - ' . $timeslot . '</option>';
                        }
                    } else {
                        echo '<option value="">No programs available</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-submit">Book Program</button>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>