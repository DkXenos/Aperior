<?php
session_start();
require '../db_connect.php';

$message = '';

// Create developer table if it doesn't exist
$create_table_query = "
CREATE TABLE IF NOT EXISTS developers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    company_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE
)";
$conn->query($create_table_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $company_name = $_POST['company_name'];

    if (empty($username) || empty($password) || empty($email) || empty($company_name)) {
        $message = "Please fill in all required fields.";
    } else {
        // Check if username or email already exists in both users and developers tables
        $stmt_check_users = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check_users->bind_param("ss", $username, $email);
        $stmt_check_users->execute();
        $stmt_check_users->store_result();

        $stmt_check_devs = $conn->prepare("SELECT id FROM developers WHERE username = ? OR email = ?");
        $stmt_check_devs->bind_param("ss", $username, $email);
        $stmt_check_devs->execute();
        $stmt_check_devs->store_result();

        if ($stmt_check_users->num_rows > 0 || $stmt_check_devs->num_rows > 0) {
            $message = "Username or email already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare("INSERT INTO developers (username, password, email, company_name) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $username, $hashed_password, $email, $company_name);

            if ($stmt_insert->execute()) {
                $message = "Developer registration successful! You can now <a href='login.php' class='text-purple-700 hover:underline'>login</a>.";
            } else {
                $message = "Error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check_users->close();
        $stmt_check_devs->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Registration - Aperior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-gradient-to-br from-[#E6E6FA] to-[#DDA0DD] flex items-center justify-center min-h-screen py-8">
    <div class="bg-white/90 backdrop-blur-md p-8 md:p-10 rounded-xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <img src="../assets/aperior.svg" alt="Aperior Logo" class="w-20 h-20 mx-auto mb-2"/>
            <h1 class="text-3xl font-bold text-purple-600 apply-custom-title-font">Developer Registration</h1>
            <p class="text-sm text-purple-500 mt-2">Join Aperior as a game developer</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($message, 'successful') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-purple-700">Username *</label>
                <input type="text" name="username" id="username" required
                       class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-purple-700">Email *</label>
                <input type="email" name="email" id="email" required
                       class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-purple-700">Password *</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
            </div>
            
            <div>
                <label for="company_name" class="block text-sm font-medium text-purple-700">Company/Studio Name *</label>
                <input type="text" name="company_name" id="company_name" required
                       class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
            </div>
            
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                    Register as Developer
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Already have a developer account? <a href="login.php" class="font-medium text-purple-600 hover:text-purple-500 hover:underline">Login here</a>
        </p>
        <p class="mt-2 text-center text-sm text-gray-600">
            Want to register as a user instead? <a href="../register.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">User Registration</a>
        </p>
        <p class="mt-2 text-center text-sm text-gray-600">
            <a href="../index.php" class="font-medium text-purple-600 hover:text-purple-500 hover:underline">Back to Home</a>
        </p>
    </div>
</body>
</html>