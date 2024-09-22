<?php include('../../includes/header.php'); ?>

<?php


// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../pages/login.php');
    exit;
}

// Fetch the username from the session
$username = $_SESSION['username']; // Ensure that username is stored in the session

$alert = '';

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancel_booking_id = intval($_POST['cancel_booking_id']);

    // URL of the Flask API endpoint for cancelling bookings
    $cancel_url = "http://127.0.0.1:5000/api/bookings/" . $cancel_booking_id;

    // Initialize cURL to cancel the booking
    $curl = curl_init($cancel_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

    // Execute cURL request and get the response
    $cancel_response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    } else {
        $cancel_http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($cancel_http_status == 200) {
            $alert = '<div class="alert success">Booking cancelled successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to cancel booking. Status code: ' . $cancel_http_status . '</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show updated booking history
    header("Refresh:0");
    exit;
}

// URL of the Flask API endpoint for fetching user bookings
$bookings_url = "http://127.0.0.1:5000/api/bookings/user/" . urlencode($username);

// Initialize cURL to get bookings
$curl = curl_init($bookings_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPGET, true);

// Execute cURL request and get the response
$bookings_response = curl_exec($curl);

// Check for cURL errors
if (curl_errno($curl)) {
    $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    $bookings = [];
} else {
    $bookings_http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($bookings_http_status == 200) {
        $bookings = json_decode($bookings_response, true);
    } else {
        $alert = '<div class="alert error">Failed to retrieve bookings. Status code: ' . $bookings_http_status . '</div>';
        $bookings = [];
    }
}

// Close cURL session
curl_close($curl);

// Fetch programs from the Flask API
$programs_url = "http://127.0.0.1:5000/api/programs";

// Initialize cURL to get programs
$curl = curl_init($programs_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPGET, true);

// Execute cURL request and get the response
$programs_response = curl_exec($curl);

// Check for cURL errors
if (curl_errno($curl)) {
    $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    $programs = [];
} else {
    $programs_http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($programs_http_status == 200) {
        $programs = json_decode($programs_response, true);
    } else {
        $alert = '<div class="alert error">Failed to retrieve programs. Status code: ' . $programs_http_status . '</div>';
        $programs = [];
    }
}

// Close cURL session
curl_close($curl);

// Create a mapping from program ID to program details
$program_map = [];
foreach ($programs as $program) {
    $program_map[$program['id']] = $program;
}
?>

<div class="container">
    <h2>Your Booking History</h2>

    <?php if ($alert) echo $alert; ?>

    <!-- Display booking history -->
    <div class="bookings-container">
        <?php if (count($bookings) > 0): ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Timeslot</th>
                        <th>Status</th>
                        <th>Booking Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        $program_id = htmlspecialchars($booking['schedule_id']);
                        $program = isset($program_map[$program_id]) ? $program_map[$program_id] : null;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['id']); ?></td>
                            <td><?php echo $program ? htmlspecialchars($program['service']['name']) : 'Unknown'; ?></td>
                            <td><?php echo $program ? htmlspecialchars($program['timeslot']) : 'Unknown'; ?></td>
                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                            <td>
                                <?php if ($booking['status'] !== 'cancelled'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="cancel_booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                        <button type="submit" class="btn-cancel" style="background-color:red;color:white;">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    Cancelled
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>

<!-- Include CSS for styling the table -->
<style>
    .bookings-table {
        width: 100%;
        border-collapse: collapse;
    }

    .bookings-table th,
    .bookings-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .bookings-table th {
        background-color: #f4f4f4;
        text-align: left;
    }

    .btn-cancel {
        padding: 5px 10px;
        border: none;
        cursor: pointer;
    }
</style>