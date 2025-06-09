<?php
session_start();
require '../db_connect.php';

$message = '';

if (isset($_SESSION['developer_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, company_name FROM developers WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $developer = $result->fetch_assoc();
            if (password_verify($password, $developer['password'])) {
                $_SESSION['developer_id'] = $developer['id'];
                $_SESSION['developer_username'] = $developer['username'];
                $_SESSION['company_name'] = $developer['company_name'];
                
                // Redirect with success
                $_SESSION['success_message'] = "Welcome back, " . $developer['company_name'] . "!";
                header("Location: dashboard.php");
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
    <title>Developer Login - Aperior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-gradient-to-br from-[#E6E6FA] to-[#DDA0DD] flex items-center justify-center min-h-screen">
    <div class="bg-white/90 backdrop-blur-md p-8 md:p-10 rounded-xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <img src="../assets/aperior.svg" alt="Aperior Logo" class="w-20 h-20 mx-auto mb-2"/>
            <h1 class="text-3xl font-bold text-purple-600 apply-custom-title-font">Developer Login</h1>
            <p class="text-sm text-purple-500 mt-2">Access your developer dashboard</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700 border border-red-300">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-purple-700">Username</label>
                <input type="text" name="username" id="username" required
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-purple-700">Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
            </div>
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                    Login
                </button>
            </div>
        </form>
        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have a developer account? <a href="register.php" class="font-medium text-purple-600 hover:text-purple-500 hover:underline">Register here</a>
        </p>
        <p class="mt-2 text-center text-sm text-gray-600">
            <a href="../index.php" class="font-medium text-purple-600 hover:text-purple-500 hover:underline">Back to Home</a>
        </p>
    </div>
</body>
</html>