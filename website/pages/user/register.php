<?php include('../../includes/header.php'); ?>

<?php
// Initialize country options
$countries = [];

// API URL with query parameters
$apiUrl = 'https://countriesnow.space/api/v0.1/countries?limit=100&order=asc&orderBy=name';

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true); // Set request method to GET

// Execute cURL request and get response
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    die('cURL Error: ' . curl_error($ch));
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$responseData = json_decode($response, true);

// Check if the response contains the 'data' key
if (isset($responseData['data'])) {
    foreach ($responseData['data'] as $country) {
        $countries[] = $country['country'];
    }
} else {
    echo '<div class="alert error">No country data found.</div>';
    exit(); // Exit if no country data is available
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $formData = [
        "name" => $_POST['name'],
        "surname" => $_POST['surname'],
        "country" => $_POST['country'],
        "address" => $_POST['address'],
        "email" => $_POST['email'],
        "username" => $_POST['username'],
        "password" => $_POST['password'],
        "role_id" => $_POST['role_id'],
        "approved" => false  // Set 'approved' to false
    ];

    // Convert data to JSON format
    $jsonData = json_encode($formData);

    // Initialize cURL session for form submission
    $ch = curl_init('http://127.0.0.1:5000/api/users');

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        echo '<div class="alert error">cURL Error: ' . curl_error($ch) . '</div>';
    } else {
        header('Location: ../user/register_success.php');
        exit(); // Ensure that no further code is executed after redirect
    }

    // Close cURL session
    curl_close($ch);
}
?>

<div class="container">
    <h3>Φόρμα Εγγραφής</h3>
    <form action="register.php" method="POST" class="registration-form">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="surname">Surname:</label>
            <input type="text" id="surname" name="surname" required>
        </div>

        <div class="form-group">
            <label for="country">Country:</label>
            <select id="country" name="country" required>
                <option value="">Select a country</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>"><?php echo htmlspecialchars($country); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="text" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="role_id">Role ID:</label>
            <input type="text" id="role_id" name="role_id" required>
        </div>

        <!-- Hidden input field for 'approved' -->
        <input type="hidden" name="approved" value="false">

        <button type="submit" class="btn-submit">Submit</button>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>

<!-- Add CSS for form styling -->
<style>
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .registration-form {
        display: flex;
        flex-direction: column;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .form-group select {
        height: 40px;
    }

    .btn-submit {
        padding: 10px 20px;
        background-color: blue;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-submit:hover {
        background-color: black;
    }

    .alert {
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }

    .alert.error {
        background-color: #f44336;
        color: white;
    }

    .alert.success {
        background-color: #4CAF50;
        color: white;
    }
</style>