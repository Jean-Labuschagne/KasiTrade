<?php
$pageTitle = 'Messages';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('auth/login.php', 'Please login', 'warning');
}

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $receiver_id = filter_var($_POST['receiver_id'], FILTER_VALIDATE_INT);
    $listing_id = filter_var($_POST['listing_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $content = clean($_POST['content']);

    if ($receiver_id && $content) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, listing_id, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $receiver_id, $listing_id, $content]);
    }
}

// Fetch conversations
$stmt = $pdo->prepare("SELECT DISTINCT 
    CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_id,
    u.username, u.first_name, u.last_name,
    (SELECT content FROM messages WHERE 
        (sender_id = ? AND receiver_id = other_id) OR 
        (sender_id = other_id AND receiver_id = ?)
     ORDER BY created_at DESC LIMIT 1) as last_message,
    (SELECT created_at FROM messages WHERE 
        (sender_id = ? AND receiver_id = other_id) OR 
        (sender_id = other_id AND receiver_id = ?)
     ORDER BY created_at DESC LIMIT 1) as last_time,
    (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_id = other_id AND is_read = FALSE) as unread
    FROM messages m
    JOIN users u ON u.user_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY last_time DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], 
                $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'],
                $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$conversations = $stmt->fetchAll();

$active_chat = filter_var($_GET['to'] ?? 0, FILTER_VALIDATE_INT);
$active_listing = filter_var($_GET['listing'] ?? 0, FILTER_VALIDATE_INT);

$messages = [];
if ($active_chat) {
    $msg_stmt = $pdo->prepare("SELECT m.*, u.username as sender_name 
                              FROM messages m
                              JOIN users u ON m.sender_id = u.user_id
                              WHERE (m.sender_id = ? AND m.receiver_id = ?) OR 
                                    (m.sender_id = ? AND m.receiver_id = ?)
                              ORDER BY m.created_at ASC");
    $msg_stmt->execute([$_SESSION['user_id'], $active_chat, $active_chat, $_SESSION['user_id']]);
    $messages = $msg_stmt->fetchAll();

    // Mark as read
    $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ?")
        ->execute([$active_chat, $_SESSION['user_id']]);
}

// Fetch listing info if applicable
$listing_info = null;
if ($active_listing) {
    $list_stmt = $pdo->prepare("SELECT title, price FROM listings WHERE listing_id = ?");
    $list_stmt->execute([$active_listing]);
    $listing_info = $list_stmt->fetch();
}
?>

<div class="container py-4">
    <div class="row">
        <!-- Conversations List -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-kasitrade text-white">
                    <h6 class="mb-0"><i class="bi bi-chat"></i> Conversations</h6>
                </div>
                <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                    <?php foreach ($conversations as $conv): ?>
                    <a href="messages.php?to=<?= $conv['other_id'] ?>" 
                       class="list-group-item list-group-item-action <?= $active_chat == $conv['other_id'] ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0"><?= clean($conv['first_name'] . ' ' . $conv['last_name']) ?></h6>
                                <p class="mb-0 small text-truncate" style="max-width: 150px;"><?= clean($conv['last_message']) ?></p>
                            </div>
                            <?php if ($conv['unread'] > 0): ?>
                            <span class="badge bg-danger"><?= $conv['unread'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($conversations)): ?>
                    <div class="text-center py-4 text-muted">No conversations yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-md-8">
            <?php if ($active_chat): ?>
            <div class="card shadow h-100">
                <div class="card-header bg-light">
                    <?php if ($listing_info): ?>
                    <small class="text-muted">About: <?= clean($listing_info['title']) ?> (<?= formatPrice($listing_info['price']) ?>)</small>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="height: 400px; overflow-y: auto;" id="chatArea">
                    <?php foreach ($messages as $msg): ?>
                    <div class="mb-2 <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'text-end' : '' ?>">
                        <div class="d-inline-block p-2 rounded <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-kasitrade text-white' : 'bg-light' ?>" style="max-width: 70%;">
                            <p class="mb-0"><?= clean($msg['content']) ?></p>
                            <small class="<?= $msg['sender_id'] == $_SESSION['user_id'] ? 'text-white-50' : 'text-muted' ?>">
                                <?= timeAgo($msg['created_at']) ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <form method="POST" class="d-flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                        <input type="hidden" name="receiver_id" value="<?= $active_chat ?>">
                        <?php if ($active_listing): ?>
                        <input type="hidden" name="listing_id" value="<?= $active_listing ?>">
                        <?php endif; ?>
                        <input type="text" name="content" class="form-control" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" class="btn btn-kasitrade"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow h-100">
                <div class="card-body text-center d-flex align-items-center justify-content-center">
                    <div>
                        <i class="bi bi-chat-dots display-1 text-muted"></i>
                        <p class="text-muted mt-3">Select a conversation to start chatting</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of chat
document.addEventListener('DOMContentLoaded', function() {
    const chatArea = document.getElementById('chatArea');
    if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;
});
</script>

<?php require_once 'includes/footer.php'; ?>