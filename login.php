<?php
session_start();
require 'db_connect.php'; // Include your database connection

$message = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect if already logged in
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php"); // Redirect to homepage or dashboard
                exit();
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css"> <!-- Assuming styles.css is in the root -->
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] flex items-center justify-center min-h-screen">
    <div class="bg-white/90 backdrop-blur-md p-8 md:p-10 rounded-xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-20 h-20 mx-auto mb-2"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Login</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-pink-700">Username</label>
                <input type="text" name="username" id="username" required
                       class="mt-1 block w-full px-3 py-2 border border-pink-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-pink-700">Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full px-3 py-2 border border-pink-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
            </div>
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-400 transition-colors">
                    Login
                </button>
            </div>
        </form>
        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? <a href="register.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Register here</a>
        </p>
        <p class="mt-2 text-center text-sm text-gray-600">
            <a href="index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a>
        </p>
    </div>
</body>
</html>