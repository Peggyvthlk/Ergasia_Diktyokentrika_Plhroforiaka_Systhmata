<?php
include('../../includes/header.php');

// Initialize alert message variable
$alert = '';

// Function to handle redirection and stop script execution
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect('../index.php');
}

// Redirect if the user is a regular user
if ($_SESSION['role'] == 'User') {
    redirect('../../user/announcement.php');
}

// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $url = "http://127.0.0.1:5000/api/announcements/" . $deleteId;

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
        if ($data['result'] === 'Announcement deleted') {
            $alert = '<div class="alert success">Announcement deleted successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to delete announcement.</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show the updated announcements list
    // Use output buffering to avoid headers already sent issue
    ob_start();
    header("Refresh:0");
    ob_end_flush();
    exit;
}

// Handle the form submission for adding a new announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $url = "http://127.0.0.1:5000/api/announcements";
    $data = [
        'title' => $title,
        'content' => $content
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
            $alert = '<div class="alert success">Announcement added successfully!</div>';
        } else {
            $alert = '<div class="alert error">Failed to add announcement.</div>';
        }
    }

    // Close cURL session
    curl_close($curl);

    // Refresh the page to show the updated announcements list
    // Use output buffering to avoid headers already sent issue
    ob_start();
    header("Refresh:0");
    ob_end_flush();
    exit;
}
?>

<div class="container">
    <h2>Διαχείριση Ανακοινώσεων και Προσφορών</h2>

    <!-- Display alert message -->
    <?php if ($alert) echo $alert; ?>

    <!-- Form to add a new announcement -->
    <div class="form-container">
        <h3>Προσθήκη Νέας Ανακοίνωσης</h3>
        <form id="announcement-form" method="post">
            <div class="form-group">
                <label for="title">Τίτλος:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="content">Περιεχόμενο:</label>
                <textarea id="content" name="content" class="form-input" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn-submit">Προσθήκη Ανακοίνωσης</button>
        </form>
    </div>

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
                    echo '<form method="post" style="display:inline;">
                              <input type="hidden" name="delete_id" value="' . htmlspecialchars($announcement['id']) . '">
                              <button type="submit" class="btn-delete" style="background-color:red;color:white;">Delete</button>
                          </form>';
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
</div>

<?php include('../../includes/footer.php'); ?>