<?php include('../../includes/header.php'); ?>

<?php
$alert = '';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Handle the delete request for programs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $url = "http://127.0.0.1:5000/api/programs/" . $deleteId;

    // Initialize cURL for DELETE request
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

    // Execute cURL request and get the response
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    } else {
        $data = json_decode($response, true);
        if (isset($data['message']) && $data['message'] === 'Program deleted successfully') {
            $alert = '<div class="alert success">Program deleted successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to delete program.</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show the updated programs list
    header("Refresh:0");
    exit;
}

// Handle the add new program request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'], $_POST['timeslot'])) {
    $service_id = intval($_POST['service_id']);
    $timeslot = $_POST['timeslot'];

    $url = "http://127.0.0.1:5000/api/programs";
    $data = [
        'service_id' => $service_id,
        'timeslot' => $timeslot
    ];

    // Initialize cURL for POST request
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Execute cURL request and get the response
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    } else {
        $data = json_decode($response, true);
        if (isset($data['id'])) {
            $alert = '<div class="alert success">Program added successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to add program.</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show the updated programs list
    header("Refresh:0");
    exit;
}
?>

<div class="container">
    <h2>Manage Programs</h2>

    <?php if ($alert) echo $alert; ?>

    <!-- Form to add a new program -->
    <div class="form-container">
        <h3>Add New Program</h3>
        <form id="program-form" method="post" action="manage_program.php">
            <div class="form-group">
                <label for="service_id">Service ID:</label>
                <input type="number" id="service_id" name="service_id" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="timeslot">Timeslot (YYYY-MM-DD HH:MM:SS):</label>
                <input type="text" id="timeslot" name="timeslot" class="form-control" required>
            </div>
            <button type="submit" class="btn-submit">Add Program</button>
        </form>
    </div>

    <h3>Existing Programs</h3>
    <!-- Display existing programs -->
    <div class="programs-container" id="programs-container">
        <?php
        // URL of the Flask API endpoint for programs
        $url = "http://127.0.0.1:5000/api/programs";

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
                foreach ($data as $program) {
                    echo '<div class="program-card" id="program-' . htmlspecialchars($program['id']) . '">';
                    echo '<h3 class="program-service-name">Service: ' . htmlspecialchars($program['service']['name']) . '</h3>';
                    echo '<p class="program-timeslot">Timeslot: ' . htmlspecialchars($program['timeslot']) . '</p>';
                    echo '<form method="post" style="display:inline;">
                              <input type="hidden" name="delete_id" value="' . htmlspecialchars($program['id']) . '">
                              <button type="submit" class="btn-delete" style="background-color:red;color:white;">Delete</button>
                          </form>';
                    echo '</div>';
                    echo '<br>';
                }
            } else {
                echo '<div class="no-programs">No programs found</div>';
            }
        }

        // Close cURL session
        curl_close($curl);
        ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>