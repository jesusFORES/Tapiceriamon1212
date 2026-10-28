<?php
// Funciones de autenticación y RBAC

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        session_start();
    }
}

function get_user_roles($pdo, $userId) {
    $stmt = $pdo->prepare(
        'SELECT r.name FROM roles r
         JOIN user_roles ur ON ur.role_id = r.id
         WHERE ur.user_id = ?'
    );
    $stmt->execute([$userId]);
    return array_column($stmt->fetchAll(), 'name');
}

function get_user_permissions($pdo, $userId) {
    $stmt = $pdo->prepare(
        'SELECT DISTINCT p.name FROM permissions p
         JOIN role_permissions rp ON rp.permission_id = p.id
         JOIN user_roles ur ON ur.role_id = rp.role_id
         WHERE ur.user_id = ?'
    );
    $stmt->execute([$userId]);
    return array_column($stmt->fetchAll(), 'name');
}

function attempt_login($pdo, $username, $password) {
    $stmt = $pdo->prepare('SELECT u.id, u.password, u.full_name, r.name as role_name, r.id as role_id FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $roles = get_user_roles($pdo, $user['id']);
        if (empty($roles)) {
            $roles = [$user['role_name']];
        }
        $permissions = get_user_permissions($pdo, $user['id']);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'role' => $user['role_name'],
            'role_id' => $user['role_id'],
            'roles' => $roles,
            'username' => $username,
            'permissions' => $permissions,
        ];
        return true;
    }
    return false;
}

function require_login() {
    start_secure_session();
    if (empty($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }
}

function has_permission($permission, $pdo = null) {
    $user = current_user();
    if (!$user) {
        return false;
    }
    if (!empty($user['permissions']) && is_array($user['permissions'])) {
        return in_array($permission, $user['permissions'], true);
    }
    if ($pdo !== null) {
        $permissions = get_user_permissions($pdo, $user['id']);
        $_SESSION['user']['permissions'] = $permissions;
        return in_array($permission, $permissions, true);
    }
    return false;
}

function require_permission($permission, $pdo = null) {
    start_secure_session();
    $user = current_user();
    if (!$user) {
        header('Location: /login.php');
        exit;
    }
    if (!has_permission($permission, $pdo)) {
        http_response_code(403);
        echo 'Acceso denegado';
        exit;
    }
}

function logout() {
    start_secure_session();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function current_user() {
    start_secure_session();
    return $_SESSION['user'] ?? null;
}

function require_role($roles = []) {
    $user = current_user();
    if (!$user) {
        header('Location: /login.php');
        exit;
    }
    $roles = (array)$roles;
    $userRoles = $user['roles'] ?? [$user['role'] ?? null];
    foreach ($userRoles as $userRole) {
        if (in_array($userRole, $roles, true)) {
            return;
        }
    }
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
}

?>
