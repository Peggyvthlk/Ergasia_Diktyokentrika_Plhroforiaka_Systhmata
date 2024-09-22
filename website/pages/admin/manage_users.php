<?php include('../../includes/header.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

?>

<div class="container">
    <h2>Διαχείριση Ρόλων</h2>
    <p>Παρακάτω μπορείτε να δείτε και να διαχειριστείτε τους ρόλους του συστήματος.</p>
    <div class="form-container">
        <!-- Form to add a new role -->
        <h3>Add New Role:</h3>
        <form id="announcement-form" action="" method="post">
            <div class="form-group">
                <label for="role_name">New Role Name:</label>
                <input type="text" id="role_name" name="role_name" class="form-control" required>
            </div>
            <button type="submit" class="btn-submit">Add Role</button>
        </form>
    </div>

    <!-- Handle form submission for adding a new role -->
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // URL of the Flask API endpoint to add a new role
        $url = "http://127.0.0.1:5000/api/roles";

        // Get the new role name from the form
        $new_role_name = $_POST['role_name'];

        // Initialize cURL for POST request
        $curl = curl_init($url);

        // Set cURL options for POST request
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('role_name' => $new_role_name)));

        // Execute cURL request and get the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
        } else {
            // Decode the JSON response
            $data = json_decode($response, true);

            // Display a message based on the response
            if (isset($data['message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($data['message']) . '</div>';
            } elseif (isset($data['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($data['error']) . '</div>';
            }
        }

        // Close cURL session
        curl_close($curl);

        // Refresh the page to show the updated roles list
        header("Refresh:0");
    }
    ?>
    <!-- Section to display roles -->
    <h3>Roles:</h3>
    <div id="roles">
        <?php
        // URL of the Flask API endpoint for roles
        $url = "http://127.0.0.1:5000/api/roles";

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
                echo '<div class="roles-table-container">';
                echo '<table class="roles-table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Role ID</th>';
                echo '<th>Role Name</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                foreach ($data as $role) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($role['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($role['role_name']) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="no-roles">No roles found</div>';
            }
        }

        // Close cURL session
        curl_close($curl);
        ?>
    </div>



    <!-- Button to delete all roles -->
    <h3>Delete All Roles:</h3>
    <form action="" method="post">
        <button type="submit" name="delete_all_roles" class="btn-delete" onclick="return confirm('Are you sure you want to delete all roles? This action cannot be undone.');">Delete All Roles</button>
    </form>

    <!-- Handle form submission for deleting all roles -->
    <?php
    if (isset($_POST['delete_all_roles'])) {
        // URL of the Flask API endpoint to delete all roles
        $url = "http://127.0.0.1:5000/api/roles";

        // Initialize cURL for DELETE request
        $curl = curl_init($url);

        // Set cURL options for DELETE request
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

        // Execute cURL request and get the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
        } else {
            // Decode the JSON response
            $data = json_decode($response, true);

            // Display a message based on the response
            if (isset($data['message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($data['message']) . '</div>';
            } elseif (isset($data['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($data['error']) . '</div>';
            }
        }

        // Close cURL session
        curl_close($curl);

        // Refresh the page to show the updated roles list
        header("Refresh:0");
    }
    ?>
</div>

<?php include('../../includes/footer.php'); ?>