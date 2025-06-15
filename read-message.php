<?php
include_once 'php/mysql_connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the message ID from the query string
$messageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($messageId <= 0) {
    header("Location: dashboard.php?error=" . urlencode("Invalid message ID."));
    exit();
}

// Fetch the message details from the database
$stmt = $conn->prepare("SELECT m.id, m.subject, m.content, u.first_name, u.last_name, u.email, m.created_at FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.id = ?");
$stmt->bind_param("i", $messageId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: dashboard.php?error=" . urlencode("Message not found."));
    exit();
}
$message = $result->fetch_assoc();
$stmt->close();
// Mark the message as read
$stmt = $conn->prepare("UPDATE message_recipients SET is_read = 1 WHERE message_id = ? AND recipient_id = ?");
$stmt->bind_param("ii", $messageId, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CESNC - Read Message</title>
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
        <main id=" main-root" class="container mx-auto flex-1 p-4 flex flex-col">
            <div class="h-12 my-3 flex flex-row space-x-2">
                <div class="flex items-center pl-3">
                    <div class="flex flex-row space-x-2 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m7.875 14.25 1.214 1.942a2.25 2.25 0 0 0 1.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 0 1 1.872 1.002l.164.246a2.25 2.25 0 0 0 1.872 1.002h2.092a2.25 2.25 0 0 0 1.872-1.002l.164-.246A2.25 2.25 0 0 1 16.954 9h4.636M2.41 9a2.25 2.25 0 0 0-.16.832V12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 0 1 .382-.632l3.285-3.832a2.25 2.25 0 0 1 1.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0 0 21.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span class="text-2xl font-semibold">Mail Inbox</span>
                    </div>
                </div>
            </div>
            <div class="flex-1 py-2 w-full flex flex-col space-y-2 ">
                <div class="px-4 py-2 bg-gray-50 rounded-md shadow-sm h-full flex-1 flex flex-col space-y-1">
                    <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($message['subject']); ?></h2>
                    <p class="text-sm text-gray-500">From: <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) . ' (' . htmlspecialchars($message['email']) . ')'; ?></p>
                    <p class="text-sm text-gray-500">Sent on: <?php echo date("F j, Y, g:i a", strtotime($message['created_at'])); ?></p>
                    <hr class="my-2">
                    <div class="text-gray-800"><?php echo nl2br(htmlspecialchars($message['content'])); ?></div>
                </div>
            </div>

        </main>
        <footer id="footer-root" class="flex justify-center items-center h-16 bg-gray-100 shadow-inner ">
            <div class="">© <?php echo date("Y"); ?> CESNC. All rights reserved.</div>
        </footer>
    </div>
</body>

</html>