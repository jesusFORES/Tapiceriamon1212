#!/bin/bash
set -e

# Wait for MySQL to be available
DB_HOST=${DB_HOST:-db}
DB_USER=${DB_USER:-tapimon}
DB_PASS=${DB_PASS:-tapimonpass}
DB_NAME=${DB_NAME:-tapimon}
PORT=${PORT:-80}

if [ -f /etc/apache2/ports.conf ]; then
  sed -i "s/^Listen .*$/Listen ${PORT}/" /etc/apache2/ports.conf || echo "Listen ${PORT}" >> /etc/apache2/ports.conf
fi

if [ -f /etc/apache2/sites-available/000-default.conf ]; then
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

echo "Esperando a que MySQL esté disponible en $DB_HOST..."
for i in {1..30}; do
  php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=3306;dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS')); echo 'ok'; } catch (Exception \$e) { exit(1); }" >/dev/null 2>&1 && break || sleep 2
  echo "Intento $i: esperando..."
done

# Inicializar esquema si hace falta
if [ -f /var/www/html/sql/schema.sql ]; then
  echo "Comprobando esquema de base de datos..."
  php -r "try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=3306;dbname=' . getenv('DB_NAME') . ';charset=utf8mb4', getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    \$hasRoles = \$pdo->query(\"SHOW TABLES LIKE 'roles'\")->fetchAll();
    if (!count(\$hasRoles)) {
      echo 'Esquema no encontrado. Cargando schema.sql...\n';
      \$sql = file_get_contents('/var/www/html/sql/schema.sql');
      foreach (array_filter(array_map('trim', explode(';', \$sql))) as \$statement) {
        if (\$statement !== '') {
          \$pdo->exec(\$statement);
        }
      }
      echo 'Esquema cargado.\n';
    } else {
      echo 'Esquema existente.\n';
    }
  } catch (Exception \$e) {
    echo 'DB init error: ' . \$e->getMessage() . "\n";
    exit(1);
  }"
fi

# Ejecutar create_user_roles si existe para esquemas existentes sin la tabla
if [ -f /var/www/html/sql/create_user_roles.php ]; then
  echo "Ejecutando create_user_roles.php..."
  php /var/www/html/sql/create_user_roles.php || true
fi

# Ejecutar seeder de PHP si existe
if [ -f /var/www/html/sql/seeds.php ]; then
  echo "Ejecutando seeder PHP..."
  php /var/www/html/sql/seeds.php || true
fi

echo "Apache listo para escuchar en el puerto $PORT"
# Lanzar apache en primer plano
exec apache2-foreground
