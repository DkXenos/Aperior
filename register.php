<?php
session_start();
require 'db_connect.php'; //

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email']; 

    
    if (empty($username) || empty($password) || empty($email)) {
        $message = "Please fill in all fields.";
    } else {
        
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Username or email already taken.";
        } else {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            
            $stmt_insert = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $hashed_password, $email);

            if ($stmt_insert->execute()) {
                $message = "Registration successful! You can now <a href='login.php' class='text-pink-700 hover:underline'>login</a>.";
            } else {
                $message = "Error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css"> 
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] flex items-center justify-center min-h-screen">
    <div class="bg-white/90 backdrop-blur-md p-8 md:p-10 rounded-xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-20 h-20 mx-auto mb-2"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Register</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($message, 'successful') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-pink-700">Username</label>
                <input type="text" name="username" id="username" required
                       class="mt-1 block w-full px-3 py-2 border border-pink-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-pink-700">Email</label>
                <input type="email" name="email" id="email" required
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
                    Register
                </button>
            </div>
        </form>
        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account? <a href="login.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Login here</a>
        </p>
         <p class="mt-2 text-center text-sm text-gray-600">
            <a href="index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a>
        </p>
    </div>
</body>
</html>