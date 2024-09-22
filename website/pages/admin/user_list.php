<?php include('../../includes/header.php'); ?>


<?php
// Define the API URL
$apiUrl = 'http://127.0.0.1:5000/api/users';

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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

// Debugging: Print decoded API response
// echo '<pre>Decoded API Response:';
// print_r($responseData);
// echo '</pre>';

// Check if the response contains user data
if (is_array($responseData)) {
    $users = $responseData;
} else {
    die('No user data found or incorrect response format.');
}
?>



<body>

    <h1>Users List</h1>
    <p>Υπενθύμιση: Το Role ID 1 αναφέρεται σε απλούς χρήστες ενώ το Role ID 2 αναφέρεται σε διαχειριστές</p>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Country</th>
                <th>Email</th>
                <th>Username</th>
                <th>Address</th>
                <th>Role ID</th>
                <th>Approved</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['surname']); ?></td>
                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                        <td><?php echo htmlspecialchars($user['role_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['approved'] ? 'Yes' : 'No'); ?></td>
                        <td>
                            <a href="manage_user.php?id=<?php echo htmlspecialchars($user['id']); ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php include('../../includes/footer.php'); ?>