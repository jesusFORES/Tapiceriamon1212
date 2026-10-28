# Tapimon
Aplicación de ejemplo para gestión de presupuestos de tapicería.

Servicios:
- PHP 8.1 (Apache)
- MySQL 8.0

Arranque con Docker Compose:

```bash
docker compose up --build
```

Después de que la base de datos esté lista, ejecutar el seeder:

```bash
# Desde otro terminal (o dentro del contenedor web)
php sql/seeds.php
```

Credenciales iniciales (seeder):
- admin / admin123 (Administrador)
- empleado / empleado123 (Empleado)
- cliente / cliente123 (Cliente)

Acceder a: http://localhost:8080
