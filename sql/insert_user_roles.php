<?php
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    getenv('DB_HOST') ?: 'db',
    getenv('DB_NAME') ?: 'tapimon'
);
$user = getenv('DB_USER') ?: 'tapimon';
$pass = getenv('DB_PASS') ?: 'tapimonpass';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_role (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $roles = [];
    foreach ($pdo->query('SELECT id, name FROM roles')->fetchAll() as $row) {
        $roles[$row['name']] = $row['id'];
    }

    $userRoleMap = [
        'admin' => 'Administrador',
        'empleado' => 'Empleado',
        'cliente' => 'Cliente',
    ];

    $insert = $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)');
    $count = 0;

    foreach ($userRoleMap as $username => $roleName) {
        if (!isset($roles[$roleName])) {
            echo "Rol no encontrado: $roleName\n";
            continue;
        }
        $roleId = $roles[$roleName];
        $user = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $user->execute([$username]);
        $row = $user->fetch();
        if (!$row) {
            echo "Usuario no encontrado: $username\n";
            continue;
        }
        $insert->execute([$row['id'], $roleId]);
        if ($insert->rowCount()) {
            $count++;
            echo "Asignado role $roleName a usuario $username\n";
        } else {
            echo "Ya existe asignación para $username -> $roleName\n";
        }
    }

    echo "Total asignaciones agregadas: $count\n";
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
