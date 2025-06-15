<?php
include_once 'php/mysql_connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recipient = $_POST['recipient'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Validate input
    if (empty($recipient) || empty($subject) || empty($message)) {
        header("Location: new-message.php?error=" . urlencode("All fields are required."));
        exit();
    }


    // Split recipients by comma and trim whitespace
    $recipients = array_map('trim', explode(',', $recipient));
    $validRecipients = [];

    foreach ($recipients as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validRecipients[] = strtolower(trim($email));
        }
    }

    // Avoid multiple recipients being the same
    $validRecipients = array_unique($validRecipients);

    if (empty($validRecipients)) {
        header("Location: new-message.php?error=" . urlencode("No valid email addresses provided."));
        exit();
    }

    // Get the ids of the valid recipients
    $validRecipientIds = [];
    $placeholders = implode(',', array_fill(0, count($validRecipients), '?'));
    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) IN ($placeholders)");
    $stmt->bind_param(str_repeat('s', count($validRecipients)), ...$validRecipients);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $validRecipientIds[] = $row['id'];
    }
    $stmt->close();
    if (empty($validRecipientIds)) {
        header("Location: new-message.php?error=" . urlencode("No valid recipients found."));
        exit();
    }
    // Insert the message into the messages table
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, subject, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], $subject, $message);
    if (!$stmt->execute()) {
        header("Location: new-message.php?error=" . urlencode("Failed to send message."));
        exit();
    }
    $messageId = $stmt->insert_id;
    $stmt->close();
    // Insert the recipients into the message_recipients table
    $stmt = $conn->prepare("INSERT INTO message_recipients (message_id, recipient_id) VALUES (?, ?)");
    foreach ($validRecipientIds as $recipientId) {
        $stmt->bind_param("ii", $messageId, $recipientId);
        if (!$stmt->execute()) {
            // If any insert fails, we can log the error but continue with the others
            error_log("Failed to insert recipient with ID $recipientId for message ID $messageId: " . $stmt->error);
        }
    }
    $stmt->close();
    header("Location: dashboard.php?success=" . urlencode("Message sent successfully."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CESNC - New Message</title>
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
        <form id="main-root" class="container mx-auto flex-1 p-4 flex flex-col" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="h-12 my-3 flex flex-row space-x-2">
                <div class="flex items-center pl-3">
                    <div class="flex flex-row space-x-2 items-center">
                        <svg xmlns=" http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>

                        <span class="text-2xl font-semibold">New Message</span>
                    </div>
                </div>
                <div class="ml-auto flex flex-row space-x-2">
                    <button type="submit" href="new-message.php" class="cursor-pointer bg-blue-500 text-white p-2 rounded-md flex flex-row space-x-2 items-center">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </div>
                        <span class="text-md">Send Message</span>
                    </button>
                </div>
            </div>
            <div class="bg-gray-100 flex-1 rounded-md shadow-md p-2 w-full flex flex-col space-y-2">
                <div class="flex flex-col">
                    <label for="recipient" class="block font-medium text-gray-800 text-xl">Recipient</label>
                    <input type="text" id="recipient" name="recipient" required class="w-full mt-1 block border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter recipient's email(s)" />
                    <div class="text-xs pl-2">You can send to multiple recipients by separating emails with commas. Invalid emails, will not be delivered.</div>
                </div>
                <div class="flex flex-col">
                    <label for="subject" class="block font-medium text-gray-800 text-xl">Subject</label>
                    <input type="text" id="subject" name="subject" required class="w-full mt-1 block border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter subject" />
                </div>
                <div class="flex-1 flex flex-col h-full">
                    <label for="message" class="block font-medium text-gray-800 text-xl">Message</label>
                    <textarea id="message" name="message" required class="flex-1 w-full h-full mt-1 block border border-gray-300 rounded-md shadow-sm p-2 resize-none overflow-y-scroll" rows="4" placeholder="Enter your message"></textarea>
                </div>
            </div>
        </form>
        <footer id="footer-root" class="flex justify-center items-center h-16 bg-gray-100 shadow-inner ">
            <div class="">© <?php echo date("Y"); ?> CESNC. All rights reserved.</div>
        </footer>
    </div>
</body>

</html>