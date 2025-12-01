<?php
session_start();
include '../include/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id_admin, password, username FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        if ($password === $row['password']) { // ⚠️ Ingat, ini tidak aman
            $_SESSION['admin_id'] = $row['id_admin'];
            $_SESSION['admin_username'] = $row['username'];
            header('Location: dashboard.php');
            exit();
        }
    }
    header('Location: index.php?error=1');
    exit();
}
?>