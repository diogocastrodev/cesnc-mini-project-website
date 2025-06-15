<?php
include_once 'php/mysql_connection.php';
include_once 'php/utils.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);
    $password = trim($_POST['password']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($address) || empty($phone) || empty($dob)) {
        header("Location: profile.php?id=" . urlencode($user_id) . "&error=" . urlencode("All fields are required."));
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: profile.php?id=" . urlencode($user_id) . "&error=" . urlencode("Invalid email format."));
        exit();
    }
    $hashed_password = null;
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    $email = strtolower($email);
    // Check if the email already exists for another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header("Location: profile.php?id=" . urlencode($user_id) . "&error=" . urlencode("Email already exists for another user."));
        exit();
    }
    $stmt->close();

    // Update user in the database
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, phone = ?, date_of_birth = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $address, $phone, $dob, $user_id);
    if ($hashed_password) {
        $stmt->execute();
        // Update password if provided
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=" . urlencode("User updated successfully."));
        exit();
    } else {
        header("Location: profile.php?id=" . urlencode($user_id) . "&error=" . urlencode("Failed to update user."));
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, address, role, phone, date_of_birth FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: dashboard.php?error=" . urlencode("User not found."));
        exit();
    }

    $user = $result->fetch_assoc();
} else {
    header("Location: dashboard.php?error=" . urlencode("Invalid request method."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CESNC - Main page</title>
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
        <main id=" main-root" class="container mx-auto flex-1 p-4">
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            <form action="profile.php" method="post" class="px-8 pt-6 pb-8 mb-4">
                <h2 class="text-2xl font-bold mb-4">Edit User</h2>

                <div class="flex flex-row space-x-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
                <div class="flex flex-row space-x-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
                <div class="flex flex-row space-x-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? "") ?>" required class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ""); ?>" required class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
                <div class="flex flex-row space-x-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                        <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-64 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="role">Role</label>
                        <div id="role" name="role" required class="w-64 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" <?php if ($_SESSION['user_id'] === $user['id'] && $user['role'] === 'admin') echo 'disabled'; ?>>
                            <span><?php echo htmlspecialchars(capitalize($user['role'])); ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update User</button>
                </div>


            </form>
        </main>
        <footer id="footer-root" class="flex justify-center items-center h-16 bg-gray-100 shadow-inner ">
            <div class="">© <?php echo date("Y"); ?> CESNC. All rights reserved.</div>
        </footer>
    </div>
</body>

</html>