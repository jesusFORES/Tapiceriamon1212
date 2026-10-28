<?php
function save_chat_message($pdo, $presupuesto_id, $user, $message, $attachmentPath = null) {
    $messageText = trim((string)($message ?? ''));
    if ($messageText === '' && $attachmentPath === null) {
        return ['ok' => false, 'message' => 'Escribe un mensaje o adjunta una imagen.'];
    }

    $stmt = $pdo->prepare('INSERT INTO presupuesto_chat_messages (presupuesto_id, sender_user_id, sender_role, sender_name, message, attachment_path, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        (int)$presupuesto_id,
        (int)$user['id'],
        $user['role'],
        $user['full_name'] ?? $user['username'],
        $messageText,
        $attachmentPath
    ]);
    return ['ok' => true];
}

function get_chat_messages($pdo, $presupuesto_id) {
    $stmt = $pdo->prepare('SELECT * FROM presupuesto_chat_messages WHERE presupuesto_id = ? ORDER BY created_at ASC');
    $stmt->execute([(int)$presupuesto_id]);
    return $stmt->fetchAll();
}
