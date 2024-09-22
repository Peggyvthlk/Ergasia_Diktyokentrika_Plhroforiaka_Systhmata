<?php include('../../includes/header.php'); ?>

<?php
$alert = '';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Handle the delete request for gym services
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $url = "http://127.0.0.1:5000/api/gym_services/" . $deleteId;

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
        if (isset($data['result']) && $data['result'] === 'Service deleted') {
            $alert = '<div class="alert success">Service deleted successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to delete service.</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show the updated services list
    header("Refresh:0");
    exit;
}

// Handle the add new service request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['description'], $_POST['class_type'], $_POST['max_capacity'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $class_type = $_POST['class_type'];
    $max_capacity = intval($_POST['max_capacity']);

    $url = "http://127.0.0.1:5000/api/gym_services";
    $data = [
        'name' => $name,
        'description' => $description,
        'class_type' => $class_type,
        'max_capacity' => $max_capacity
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
        if (isset($data['result']) && $data['result'] === 'Service added') {
            $alert = '<div class="alert success">Service added successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to add service.</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show the updated services list
    header("Refresh:0");
    exit;
}
?>

<div class="container">
    <h2>Διαχείριση Υπηρεσιών Γυμναστηρίου</h2>

    <?php if ($alert) echo $alert; ?>

    <!-- Form to add a new gym service -->
    <div class="form-container">
        <h3>Προσθήκη Νέας Υπηρεσίας</h3>
        <form id="service-form" method="post" action="manage_elements.php">
            <div class="form-group">
                <label for="name">Όνομα Υπηρεσίας:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Περιγραφή:</label>
                <textarea id="description" name="description" class="form-input" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="class_type">Τύπος Μαθήματος:</label>
                <select id="class_type" name="class_type" class="form-control" required>
                    <option value="Group">Group</option>
                    <option value="Personal">Personal</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="max_capacity">Μέγιστη Χωρητικότητα:</label>
                <input type="number" id="max_capacity" name="max_capacity" class="form-control" required>
            </div>
            <button type="submit" class="btn-submit">Προσθήκη Υπηρεσίας</button>
        </form>
    </div>

    <h3>Υπηρεσίες Γυμναστηρίου</h3>
    <!-- Display existing gym services -->
    <div class="services-container" id="services-container">
        <?php
        // URL of the Flask API endpoint for gym services
        $url = "http://127.0.0.1:5000/api/gym_services";

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
                foreach ($data as $service) {
                    echo '<div class="service-card" id="service-' . htmlspecialchars($service['id']) . '">';
                    echo '<h3 class="service-name">' . htmlspecialchars($service['name']) . '</h3>';
                    echo '<p class="service-description">' . nl2br(htmlspecialchars($service['description'])) . '</p>';
                    echo '<p class="service-class-type">Class Type: ' . htmlspecialchars($service['class_type']) . '</p>';
                    echo '<p class="service-max-capacity">Max Capacity: ' . htmlspecialchars($service['max_capacity']) . '</p>';
                    echo '<form method="post" style="display:inline;">
                              <input type="hidden" name="delete_id" value="' . htmlspecialchars($service['id']) . '">
                              <button type="submit" class="btn-delete" style="background-color:red;color:white;">Delete</button>
                          </form>';
                    echo '<form method="get" action="edit_service.php" style="display:inline;">
                              <input type="hidden" name="edit_id" value="' . htmlspecialchars($service['id']) . '">
                              <button type="submit" class="btn-edit" style="background-color:blue;color:white;">Edit</button>
                          </form>';
                    echo '</div>';
                    echo '<br>';
                }
            } else {
                echo '<div class="no-services">No gym services found</div>';
            }
        }

        // Close cURL session
        curl_close($curl);
        ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>