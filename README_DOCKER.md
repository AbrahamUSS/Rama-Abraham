# 🐳 Guía de Despliegue con Docker - IEP Corazón de Jesús

Este repositorio incluye todo lo necesario para ejecutar el proyecto en la máquina de cualquier compañero con **un solo comando**, sin necesidad de configurar PHP, Apache o XAMPP manualmente.

---

## 🚀 Requisitos Previos

Solo necesitan tener instalado:
* **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** (en Windows/Mac) o **Docker + Docker Compose** (en Linux).

---

## 🛠️ Instrucciones de Ejecución

### 1. Iniciar la aplicación y la Base de Datos

Según la versión instalada en la máquina (v1 con guion o v2 sin guion), ejecuta en una terminal dentro de la raíz del proyecto:

**Con Docker Compose v2:**
```bash
docker compose up -d --build
```

**Con Docker Compose v1 (o tradicional):**
```bash
docker-compose up -d --build
```

> 💡 **Nota para Linux:** Si no tienes el plugin de compose instalado, puedes instalarlo en tu sistema con:
> `sudo apt install docker-compose-plugin` (Ubuntu/Debian) o `sudo pacman -S docker-compose` (Arch).

Esto hará lo siguiente automáticamente:
1. Construirá la imagen PHP 8.2 + Apache con `pdo_mysql` y `mod_rewrite`.
2. Iniciará el contenedor de MySQL 8.
3. **Importará automáticamente** la base de datos `corazonJesus.sql` con todas las tablas y roles.

---

### 2. Poblar los usuarios iniciales (Seeder)

Una vez iniciados los contenedores, ejecuta el seeder para crear los usuarios **Director** y **Docente**:

```bash
docker compose exec web php seed_usuarios.php
```

O también puedes abrir la siguiente URL en tu navegador:
`http://localhost:7070/seed_usuarios.php`

---

### 3. Acceder al Sistema

Abre tu navegador e ingresa a:
👉 **`http://localhost:7070/`**

#### 🔑 Credenciales de Acceso Creadas:

| Rol | Usuario (Username / Correo) | Contraseña |
|---|---|---|
| **Director / Admin** | `director` | `director123` |
| **Docente** | `docente` | `docente123` |

---

## ⏹️ Detener o Reiniciar los Contenedores

* **Detener los contenedores:**
  ```bash
  docker compose down
  ```

* **Detener y borrar la base de datos (para hacer una instalación limpia):**
  ```bash
  docker compose down -v
  ```
