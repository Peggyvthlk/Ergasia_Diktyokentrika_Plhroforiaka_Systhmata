<?php include('../../includes/header.php'); ?>

<?php
// Ensure the edit_id parameter is present, otherwise the admin will not be able to edit the service
if (!isset($_GET['edit_id'])) {
    // Redirect the admin to the index page in case the id is not present
    header("Location: ../../index.php");
    exit();
}
// Intialize our variables
$editId = intval($_GET['edit_id']); // Get the integer value of edit_id
$serviceToEdit = null;
$alert = '';
//Our Python Flask REST API endpoint for a gym service with id = edit_id
$url = "http://127.0.0.1:5000/api/gym_services/" . $editId;
// Initialize a new curl session using the URL. 
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // This option tells cURL to return the response from the API as a string
curl_setopt($curl, CURLOPT_HTTPGET, true); // This sets the request method to GET
$response = curl_exec($curl); // Execute the cURL session

// Error hadling 
if (curl_errno($curl)) {
    $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
} else {
    $serviceToEdit = json_decode($response, true);
    if (!$serviceToEdit) {
        $alert = '<div class="alert error">Service not found.</div>';
        $serviceToEdit = null;
    }
}

//close curl session 
curl_close($curl);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $class_type = $_POST['class_type'];
    $max_capacity = intval($_POST['max_capacity']);
    $updateId = intval($_POST['update_id']);

    // Make an array from data for PUT request
    $putData = json_encode(array(
        'name' => $name,
        'description' => $description,
        'class_type' => $class_type,
        'max_capacity' => $max_capacity
    ));
    //Our Python Flask REST API endpoint for a gym service with id = updateId
    $url = "http://127.0.0.1:5000/api/gym_services/" . $updateId;
    // Initialize a new curl session using the URL. 
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $putData);
    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $alert = '<div class="alert error">cURL error: ' . curl_error($curl) . '</div>';
    } else {
        $data = json_decode($response, true);
        if (isset($data['id'])) {
            $alert = '<div class="alert success">Service updated successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to update service.</div>';
        }
    }

    curl_close($curl);
}
?>

<div class="container">
    <h2>Επεξεργασία Υπηρεσίας Γυμναστηρίου</h2>

    <?php if ($alert): ?>
        <div class="alert"><?php echo $alert; ?></div>
    <?php endif; ?>

    <!-- Form to edit the gym service -->
    <div class="form-container">
        <form id="edit-service-form" method="post" action="">
            <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($editId); ?>">
            <div class="form-group">
                <label for="name">Όνομα Υπηρεσίας:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($serviceToEdit['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Περιγραφή:</label>
                <textarea id="description" name="description" class="form-input" rows="4" required><?php echo htmlspecialchars($serviceToEdit['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="class_type">Τύπος Μαθήματος:</label>
                <select id="class_type" name="class_type" class="form-control" required>
                    <option value="Group" <?php echo ($serviceToEdit['class_type'] ?? '') === 'Group' ? 'selected' : ''; ?>>Group</option>
                    <option value="Personal" <?php echo ($serviceToEdit['class_type'] ?? '') === 'Personal' ? 'selected' : ''; ?>>Personal</option>
                    <option value="Other" <?php echo ($serviceToEdit['class_type'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="max_capacity">Μέγιστη Χωρητικότητα:</label>
                <input type="number" id="max_capacity" name="max_capacity" class="form-control" value="<?php echo htmlspecialchars($serviceToEdit['max_capacity'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn-submit">Αποθήκευση Αλλαγών</button>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>