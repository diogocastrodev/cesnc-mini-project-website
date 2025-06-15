<?php
include_once 'php/mysql_connection.php';
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
$error = '';
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        // Prepare and execute the SQL statement
        $stmt = $conn->prepare("SELECT id,email, first_name, last_name, role, password, is_active FROM users WHERE email = ?;");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, set session variables
            $user = $result->fetch_assoc();

            if (!$user['is_active']) {
                $error = "Your account is inactive. Please contact support.";
                $stmt->close();
                header("Location: login.php?error=" . urlencode($error));
                exit();
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $error = "Invalid email or password.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                $stmt->close();

                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
    header("Location: login.php?error=" . urlencode($error));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CESNC - Login</title>
    <script src="./js/tailwind.js"></script>
</head>

<body>
    <div id="root" class="flex flex-col min-h-screen">
        <nav id="nav-root" class="bg-gray-300 text-black p-4 shadow-md">
            <div class="container mx-auto flex justify-between items-center">
                <div class="text-lg font-bold">CESNC Example Project</div>
                <ul class="flex space-x-4">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin.php" class="hover:underline">Admin</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php" class="hover:underline">Profile</a></li>
                        <li><a href="dashboard.php" class="hover:underline">Dashboard</a></li>
                        <li><a href="logout.php" class="hover:underline">Logout</a></li>
                    <?php else: ?>
                        <li><a href="index.php" class="hover:underline">Home</a></li>
                        <li><a href="login.php" class="hover:underline">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>

        </nav>
        <main id=" main-root" class="container mx-auto flex-1 p-4 flex justify-center items-center">
            <div class="px-6 py-6 bg-gray-50 shadow-md rounded-md">
                <div class="text-center font-bold mb-3 text-xl">Login</div>
                <form action="login.php" method="POST" class="flex flex-col items-center gap-y-4">
                    <div class="">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required class="w-64 mt-1 block border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>
                    <div class="">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required class="mt-1 block w-64 border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>
                    <button type="submit" class="w-64 bg-blue-500 text-white p-2 rounded-md shadow-sm">Login</button>
                    <div class="text-red-500 text-center text-sm"><?php if (isset($error)) echo $error; ?></div>
                </form>
            </div>

        </main>
        <footer id="footer-root" class="flex justify-center items-center h-16 bg-gray-100 shadow-inner ">
            <div class="">© <?php echo date("Y"); ?> CESNC. All rights reserved.</div>
        </footer>
    </div>
</body>

</html>