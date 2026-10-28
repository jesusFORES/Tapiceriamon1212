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

    $pdo->exec("CREATE TABLE IF NOT EXISTS modulo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        descripcion VARCHAR(255),
        estado TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS rm_modulo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        descripcion VARCHAR(255),
        estado TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS rm_pagina (
        id INT AUTO_INCREMENT PRIMARY KEY,
        modulo_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        ruta VARCHAR(255) NOT NULL,
        descripcion VARCHAR(255),
        estado TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_modulo_ruta (modulo_id, ruta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS rm_per (
        id INT AUTO_INCREMENT PRIMARY KEY,
        modulo_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        permiso VARCHAR(100) NOT NULL UNIQUE,
        descripcion VARCHAR(255),
        estado TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("ALTER TABLE rm_pagina DROP FOREIGN KEY IF EXISTS fk_rm_pagina_modulo;");
    $pdo->exec("ALTER TABLE rm_per DROP FOREIGN KEY IF EXISTS fk_rm_per_modulo;");
} catch (PDOException $e) {
    // MySQL does not support DROP FOREIGN KEY IF EXISTS in all versions through PDO,
    // fallback to manual constraint removal when needed.
}

function ensureForeignKey(PDO $pdo, string $table, string $column, string $referencedTable, string $referencedColumn, string $constraintName): void
{
    $sql = "SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM information_schema.key_column_usage WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$table, $column]);
    $fk = $stmt->fetch();

    if ($fk) {
        if ($fk['REFERENCED_TABLE_NAME'] !== $referencedTable) {
            $pdo->exec("ALTER TABLE {$table} DROP FOREIGN KEY {$fk['CONSTRAINT_NAME']}");
            $pdo->exec("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} FOREIGN KEY ({$column}) REFERENCES {$referencedTable}({$referencedColumn}) ON DELETE CASCADE");
            echo "Updated foreign key for {$table}.{$column} to reference {$referencedTable}({$referencedColumn})\n";
        }
    } else {
        $pdo->exec("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} FOREIGN KEY ({$column}) REFERENCES {$referencedTable}({$referencedColumn}) ON DELETE CASCADE");
        echo "Added foreign key for {$table}.{$column} -> {$referencedTable}.{$referencedColumn}\n";
    }
}

try {
    ensureForeignKey($pdo, 'rm_pagina', 'modulo_id', 'modulo', 'id', 'fk_rm_pagina_modulo');
    ensureForeignKey($pdo, 'rm_per', 'modulo_id', 'modulo', 'id', 'fk_rm_per_modulo');
    echo "Modulo linkage ensured for rm_pagina and rm_per.\n";
} catch (PDOException $e) {
    echo "Error ensuring modulo linkage: " . $e->getMessage() . "\n";
    exit(1);
}
