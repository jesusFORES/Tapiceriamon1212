<?php
// Seeder script: run after database is created to insert initial roles, permissions and users
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

    // Evitar insertar si ya existen roles
    $count = $pdo->query("SELECT COUNT(*) as c FROM roles")->fetch();
    if (empty($count['c'])) {
        $roles = ['Administrador','Empleado','Cliente'];
        $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        foreach ($roles as $r) {
            $stmt->execute([$r, "$r role"]);
        }
    }

    $permCount = $pdo->query("SELECT COUNT(*) as c FROM permissions")->fetch();
    if (empty($permCount['c'])) {
        $permissions = [
            ['manage_users', 'Gestionar usuarios'],
            ['manage_clients', 'Gestionar clientes'],
            ['delete_clients', 'Eliminar clientes'],
            ['create_presupuestos', 'Crear presupuestos'],
            ['view_presupuestos', 'Ver presupuestos'],
            ['view_own_presupuestos', 'Ver mis presupuestos'],
            ['delete_presupuestos', 'Eliminar presupuestos'],
            ['chat_presupuesto', 'Chat de presupuesto'],
        ];
        $stmt = $pdo->prepare("INSERT INTO permissions (name, description) VALUES (?, ?)");
        foreach ($permissions as $perm) {
            $stmt->execute([$perm[0], $perm[1]]);
        }
    }

    $rolePermCount = $pdo->query("SELECT COUNT(*) as c FROM role_permissions")->fetch();
    if (empty($rolePermCount['c'])) {
        $roleIds = [];
        $roles = ['Administrador', 'Empleado', 'Cliente'];
        $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
        foreach ($roles as $roleName) {
            $roleStmt->execute([$roleName]);
            $row = $roleStmt->fetch();
            $roleIds[$roleName] = $row['id'];
        }

        $permIds = [];
        $permStmt = $pdo->prepare("SELECT id FROM permissions WHERE name = ? LIMIT 1");
        $permissionNames = [
            'manage_users',
            'manage_clients',
            'delete_clients',
            'create_presupuestos',
            'view_presupuestos',
            'view_own_presupuestos',
            'delete_presupuestos',
            'chat_presupuesto',
        ];
        foreach ($permissionNames as $permissionName) {
            $permStmt->execute([$permissionName]);
            $row = $permStmt->fetch();
            $permIds[$permissionName] = $row['id'];
        }

        $insertRp = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        $adminPerms = $permissionNames;
        foreach ($adminPerms as $permName) {
            $insertRp->execute([$roleIds['Administrador'], $permIds[$permName]]);
        }

        $employeePerms = [
            'manage_clients',
            'create_presupuestos',
            'view_presupuestos',
            'chat_presupuesto',
        ];
        foreach ($employeePerms as $permName) {
            $insertRp->execute([$roleIds['Empleado'], $permIds[$permName]]);
        }

        $clientPerms = [
            'view_own_presupuestos',
            'chat_presupuesto',
        ];
        foreach ($clientPerms as $permName) {
            $insertRp->execute([$roleIds['Cliente'], $permIds[$permName]]);
        }
    }

    // RBAC modules and pages
    $moduloCount = $pdo->query("SELECT COUNT(*) as c FROM modulo")->fetch();
    if (empty($moduloCount['c'])) {
        $modulos = [
            ['Clientes', 'Administración de clientes'],
            ['Presupuestos', 'Gestión de presupuestos'],
        ];
        $insertModulo = $pdo->prepare("INSERT INTO modulo (nombre, descripcion) VALUES (?, ?)");
        foreach ($modulos as $modulo) {
            $insertModulo->execute([$modulo[0], $modulo[1]]);
        }
    }

    $paginaCount = $pdo->query("SELECT COUNT(*) as c FROM rm_pagina")->fetch();
    if (empty($paginaCount['c'])) {
        $pageStmt = $pdo->prepare("SELECT id FROM modulo WHERE nombre = ? LIMIT 1");
        $pageStmt->execute(['Clientes']);
        $clientesModuloId = $pageStmt->fetchColumn();
        $pageStmt->execute(['Presupuestos']);
        $presupuestosModuloId = $pageStmt->fetchColumn();

        $insertPagina = $pdo->prepare("INSERT INTO rm_pagina (modulo_id, nombre, ruta, descripcion) VALUES (?, ?, ?, ?)");
        if ($clientesModuloId) {
            $insertPagina->execute([$clientesModuloId, 'Listado de clientes', '/clientes.php', 'Página de clientes']);
        }
        if ($presupuestosModuloId) {
            $insertPagina->execute([$presupuestosModuloId, 'Presupuestos', '/presupuestos.php', 'Página de presupuestos']);
        }
    }

    // Users
    $createUser = $pdo->prepare("INSERT INTO users (role_id, username, password, full_name, email) VALUES (?, ?, ?, ?, ?)");
    $passwordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
    $passwordEmp = password_hash('empleado123', PASSWORD_DEFAULT);
    $passwordCli = password_hash('cliente123', PASSWORD_DEFAULT);

    $uCount = $pdo->query("SELECT COUNT(*) as c FROM users")->fetch();
    if (empty($uCount['c'])) {
        $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
        $roleStmt->execute(['Administrador']);
        $adminRoleId = $roleStmt->fetchColumn();
        $roleStmt->execute(['Empleado']);
        $empleadoRoleId = $roleStmt->fetchColumn();
        $roleStmt->execute(['Cliente']);
        $clienteRoleId = $roleStmt->fetchColumn();

        $createUser->execute([$adminRoleId, 'admin', $passwordAdmin, 'Admin Tapimon', 'admin@tapimon.local']);
        $adminUserId = $pdo->lastInsertId();
        $createUser->execute([$empleadoRoleId, 'empleado', $passwordEmp, 'Empleado Tapimon', 'empleado@tapimon.local']);
        $empleadoUserId = $pdo->lastInsertId();
        $createUser->execute([$clienteRoleId, 'cliente', $passwordCli, 'Cliente Demo', 'cliente@tapimon.local']);
        $clienteUserId = $pdo->lastInsertId();

        $insertUserRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $insertUserRole->execute([$adminUserId, $adminRoleId]);
        $insertUserRole->execute([$empleadoUserId, $empleadoRoleId]);
        $insertUserRole->execute([$clienteUserId, $clienteRoleId]);

        $createClient = $pdo->prepare("INSERT INTO clients (user_id, company, contact_name, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)");
        $createClient->execute([$clienteUserId, 'Cliente Demo S.A.', 'Cliente Demo', '555-0000', 'cliente@tapimon.local', 'Calle Falsa 123']);
    }

    echo "Seeds inserted successfully\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
