<?php
//Our Python Flask REST API endpoint for users
$apiBaseUrl = 'http://127.0.0.1:5000/api/users';
$user = null;
// Check if an ID is provided to edit a user,
// if the ID is not provided or its not in the correct format inform the administrator 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userId = intval($_GET['id']);
    // Fetch user details from the api
    $apiUrl = "$apiBaseUrl/$userId";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Get the JSON response
    $responseData = json_decode($response, true);
    // Check if the user data was returned, else inform the admin
    if ($responseData && isset($responseData['id'])) {
        $user = $responseData;
    } else {
        //Practically exit
        die('User not found.');
    }
}

//Form submission 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        "id" => $_POST['id'],
        "name" => $_POST['name'],
        "surname" => $_POST['surname'],
        "country" => $_POST['country'],
        "address" => $_POST['address'],
        "email" => $_POST['email'],
        "username" => $_POST['username'],
        "password" => $_POST['password'],
        "role_id" => $_POST['role_id'],
        "approved" => $_POST['approved']
    ];

    // We need to send the user data to the API in JSON format, so we convert our PHP array ($data) to a JSON string using json_encode().
    $jsonData = json_encode($data);

    // Initialize curl session for updating the user
    $ch = curl_init("$apiBaseUrl/" . $data['id']); // API endpoint for user update
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Get the response as a string
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Specify the request method as PUT
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // Set the content type to JSON
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Attach the JSON data to the request
    // Execute the cURL request and close the session
    $response = curl_exec($ch);
    curl_close($ch);

    //Handle possible errors
    if ($response === false) {
        die('cURL Error: ' . curl_error($ch));
    }

    // Redirect to the user list page after update
    header('Location: user_list.php');
    exit();
}
?>


<!-- User management html -->
<!-- In the html bellow there is a form that allows the admin to change/upadte user data -->
<!-- User data will be updated by using the PUT endpoint above -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>

<body>

    <h1>Edit User</h1>

    <form action="manage_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>

        <label for="surname">Surname:</label>
        <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required><br><br>

        <label for="country">Country:</label>
        <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" required><br><br>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password"><br><br>

        <label for="role_id">Role ID:</label>
        <input type="text" id="role_id" name="role_id" value="<?php echo htmlspecialchars($user['role_id']); ?>" required><br><br>

        <label for="approved">Approved:</label>
        <input type="text" id="approved" name="approved" value="<?php echo htmlspecialchars($user['approved']); ?>"><br><br>

        <input type="submit" value="Update">
    </form>

</body>

</html>