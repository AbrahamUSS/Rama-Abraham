# Sistema de Gestión — IEP Corazón de Jesús

Sistema web de administración académica para la Institución Educativa Privada **Corazón de Jesús College**. Construido con arquitectura **MVC (Modelo-Vista-Controlador)** utilizando PHP 8.2 en el backend, PDO para la base de datos MySQL/MariaDB, y HTML, CSS y JavaScript (Vanilla JS / jQuery) en el frontend.

---

## 📋 Características Principales

| Módulo | Rol | Descripción |
|--------|-----|-------------|
| **Autenticación y RBAC** | Todos | Login con contraseñas encriptadas (bcrypt) y control de acceso basado en roles (`Director`, `Docente`) |
| **Información Personal** | Docente / Admin | Ficha de datos personales del usuario autenticado |
| **Gestión de Incidencias** | Docente / Admin | Registro y seguimiento de incidencias disciplinarias estudiantiles |
| **Reporte de Docentes** | Admin | Evaluación y calificación del desempeño docente |
| **Añadir Docentes** | Admin | Alta y registro de nuevo personal docente |
| **Gestión de Cursos** | Docente / Admin | Asignación de cursos, horarios y materias por grado |
| **Horarios y Actividades** | Docente | Calendario de sesiones académicas y registro de asistencia |
| **Mensajería y UGEL** | Docente / Admin | Buzón interno de mensajes y comunicados institucionales |
| **Plantillas UGEL** | Admin | Descarga de documentos oficiales (currículo, sílabos, planificaciones) |
| **Gestión Económica** | Admin | Dashboard financiero con pensiones, gastos y sueldos |

---

## 🏗️ Arquitectura MVC

El proyecto implementa un punto de entrada único (**Front Controller** en `index.php`):

- **Controlador Principal (`index.php`):** Despacha peticiones dinámicas a controladores (`/auth/login`, `/usuario/registrar`), sirve archivos estáticos (`/public/`) y enruta vistas principales (`/admin`, `/docente`, `/login`).
- **Modelos (`models/`):** Encapsulan la lógica de acceso a la base de datos relacional `colegio_db` mediante transacciones y consultas preparadas PDO.
- **Controladores (`controllers/`):** Reciben las peticiones, coordinan las validaciones con `Security.php` y retornan respuestas JSON o vistas.
- **Vistas (`views/`):** Interfaces dinámicas PHP/HTML (`login.php`, `admin.php`, `docente.php`).
- **Core (`core/`):** Singleton de conexión PDO (`database.php`), tokens CSRF, sanitización y guardias de sesión (`security.php`).

---

## 🐳 Ejecución Rápida con Docker (Recomendado)

Ejecuta todo el proyecto con un solo comando sin necesidad de instalar XAMPP ni configurar MySQL manualmente:

### 1. Iniciar contenedores

```bash
docker compose up -d
```

> **Servicios creados:**
> - 🌐 **Web (PHP 8.2 + Apache):** `http://localhost:7070/`
> - 🗄️ **Base de Datos (MySQL 8.0):** Puerto `3307` (Base de datos `colegio_db` e importación automática de `corazonJesus.sql`).

### 2. Poblar los usuarios iniciales (Seeder)

```bash
docker compose exec web php seed_usuarios.php
```

---

## 💻 Ejecución Tradicional con XAMPP

1. Copiar la carpeta del proyecto en `htdocs/Sistema_Gestion_IE`.
2. En phpMyAdmin, crear la base de datos `colegio_db`.
3. Importar el archivo SQL unificado **`corazonJesus.sql`**.
4. Ejecutar el script `seed_usuarios.php` desde la terminal (`php seed_usuarios.php`) o ingresando en el navegador a `http://localhost:8080/Sistema_Gestion_IE/seed_usuarios.php`.
5. Acceder a `http://localhost:8080/Sistema_Gestion_IE/`.

---

## 🔑 Credenciales de Acceso Creadas

| Rol | Usuario (`username`) | Contraseña (`password`) | Redirección |
|-----|----------------------|------------------------|-------------|
| **Director / Admin** | `director` | `director123` | Portal Administrativo (`/admin`) |
| **Docente** | `docente` | `docente123` | Portal Docente (`/docente`) |

---

## 📁 Estructura del Proyecto

```
Sistema_Gestion_IE/
├── controllers/                # Controladores MVC (AuthController, UsuarioController, etc.)
├── core/                       # Núcleo del sistema (database.php, security.php, config.php)
├── models/                     # Modelos PDO (UsuarioModel, DocenteModel, RolModel, etc.)
├── views/                      # Vistas principales PHP
│   ├── auth/login.php          # Formulario de Inicio de Sesión
│   ├── admin.php               # Portal Administrativo (Director)
│   └── docente.php             # Portal Docente
├── public/                     # Recursos estáticos públicos
│   ├── css/                    # Variables CSS, layout y componentes
│   ├── js/                     # Módulos JS (auth.js, router.js, admin.js, docente.js)
│   └── img/                    # Logos e imágenes del colegio
├── corazonJesus.sql            # Script unificado de base de datos MySQL
├── seed_usuarios.php           # Script para poblar usuarios y roles iniciales
├── index.php                   # Front Controller unificado
├── Dockerfile                  # Imagen Docker PHP 8.2 + Apache
├── docker-compose.yml          # Orquestación de servicios Web + MySQL
├── README_DOCKER.md            # Guía detallada de Docker
└── LICENSE                     # Licencia AGPL-3.0
```

---

## 🛠️ Tecnologías Utilizadas

- **PHP 8.2** — Backend MVC, PDO, control de sesiones y CSRF
- **MySQL 8.0 / MariaDB** — Base de datos relacional normalizada (esquema `corazonJesus.sql`)
- **Docker & Docker Compose** — Contenerización de entorno de desarrollo
- **HTML5 & CSS3** — Flexbox, CSS Grid y variables de diseño responsivas
- **JavaScript (Vanilla / jQuery)** — SPA hash-router e interacciones AJAX

---

## 📄 Licencia

Este proyecto está licenciado bajo la **GNU Affero General Public License v3.0** (AGPL-3.0). Consulta el archivo [LICENSE](LICENSE) para más detalles.
