<!-- pages/manage_requests.php -->
<?php include('../../includes/header.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

?>
<div class="container">
    <h2>Διαχείριση Αιτημάτων Εγγραφής</h2>
    <div class="item">
        <p>Λίστα με τα αιτήματα εγγραφής εδώ...</p>
        <!-- Fetch and display registration requests -->
    </div>
</div>

<?php include('../../includes/footer.php'); ?>