<?php include('../includes/header.php'); ?>
<?php
// // Initialize session
// session_start();

// Define the API URL to get users
$apiUrl = 'http://127.0.0.1:5000/api/users';

// Fetch user data from the API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Check for cURL errors
if ($response === false) {
    die('cURL Error: ' . curl_error($ch));
}

// Decode the JSON response
$users = json_decode($response, true);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';


    $userFound = false;

    // Validate user credentials and approval status
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            if ($user['approved']) {
                // Successful login
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header('Location: ../index.php');
                exit;
            } else {
                $error = 'Your account is not approved.';
            }
            $userFound = true;
            break;
        } else {
            $error = 'Wrong credentials';
        }
    }

    if (!$userFound) {
        $error = 'Invalid username or password';
    }
}
?>

<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <div class="form-group">
            <label for="role">Select Role:</label>
            <select id="role" name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit">Login</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>