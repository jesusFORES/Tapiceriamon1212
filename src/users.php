<?php
function get_role_id($pdo, $role_name) {
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
    $stmt->execute([$role_name]);
    $role = $stmt->fetch();
    return $role ? (int)$role['id'] : null;
}

function register_account($pdo, array $data) {
    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');
    $confirmPassword = (string)($data['confirm_password'] ?? '');
    $fullName = trim((string)($data['full_name'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $roleName = trim((string)($data['role_name'] ?? 'Cliente'));

    if ($username === '' || $password === '' || $fullName === '' || $email === '') {
        return ['ok' => false, 'message' => 'Completa todos los campos obligatorios.'];
    }

    if (strlen($password) < 6) {
        return ['ok' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    if ($password !== $confirmPassword) {
        return ['ok' => false, 'message' => 'Las contraseñas no coinciden.'];
    }

    $existing = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $existing->execute([$username]);
    if ($existing->fetch()) {
        return ['ok' => false, 'message' => 'El nombre de usuario ya está registrado.'];
    }

    $roleId = get_role_id($pdo, $roleName);
    if ($roleId === null) {
        return ['ok' => false, 'message' => 'Rol inválido.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO users (role_id, username, password, full_name, email, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$roleId, $username, $hashedPassword, $fullName, $email]);
        $userId = (int)$pdo->lastInsertId();

        $stmtUserRole = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
        $stmtUserRole->execute([$userId, $roleId]);

        if ($roleName === 'Cliente') {
            $company = trim((string)($data['company'] ?? ''));
            $contactName = trim((string)($data['contact_name'] ?? ''));
            $phone = trim((string)($data['phone'] ?? ''));
            $address = trim((string)($data['address'] ?? ''));

            $stmtClient = $pdo->prepare('INSERT INTO clients (user_id, company, contact_name, phone, email, address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmtClient->execute([$userId, $company, $contactName, $phone, $email, $address]);
        }

        $pdo->commit();
        return ['ok' => true, 'user_id' => $userId];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'message' => 'No se pudo crear el registro: ' . $e->getMessage()];
    }
}

function delete_client_by_id($pdo, $clientId, $currentUserId) {
    if ($clientId <= 0) {
        return ['ok' => false, 'message' => 'Cliente inválido.'];
    }

    try {
        $pdo->beginTransaction();
        $clientStmt = $pdo->prepare('SELECT user_id FROM clients WHERE id = ? LIMIT 1');
        $clientStmt->execute([$clientId]);
        $client = $clientStmt->fetch();
        if (!$client) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Cliente no encontrado.'];
        }

        if (!empty($client['user_id']) && (int)$client['user_id'] === $currentUserId) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'No puedes eliminar tu propio registro.'];
        }

        if (!empty($client['user_id'])) {
            $deleteUser = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $deleteUser->execute([(int)$client['user_id']]);
        }

        $deleteClient = $pdo->prepare('DELETE FROM clients WHERE id = ?');
        $deleteClient->execute([$clientId]);

        $pdo->commit();
        return ['ok' => true];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'message' => 'No se pudo eliminar el cliente: ' . $e->getMessage()];
    }
}

function delete_user_by_id($pdo, $userId, $currentUserId) {
    if ($userId <= 0) {
        return ['ok' => false, 'message' => 'Usuario inválido.'];
    }

    if ($userId === $currentUserId) {
        return ['ok' => false, 'message' => 'No puedes eliminar tu propia cuenta.'];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Usuario no encontrado.'];
        }

        $roleStmt = $pdo->prepare('SELECT name FROM roles WHERE id = ? LIMIT 1');
        $roleStmt->execute([$user['role_id']]);
        $role = $roleStmt->fetchColumn();

        if ($role === 'Cliente') {
            $clientStmt = $pdo->prepare('DELETE FROM clients WHERE user_id = ?');
            $clientStmt->execute([$userId]);
        }

        $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $deleteStmt->execute([$userId]);
        $pdo->commit();

        return ['ok' => true];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'message' => 'No se pudo eliminar el usuario: ' . $e->getMessage()];
    }
}
