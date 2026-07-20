# Sistema de Gestion -- IEP Corazon de Jesus

Sistema web de administracion academica para la Institucion Educativa Privada **Corazon de Jesus College**. Construido con una arquitectura **MVC (Modelo-Vista-Controlador)** utilizando PHP en el backend y HTML, CSS y JavaScript (Vanilla JS) en el frontend, sin frameworks externos.

---

## Caracteristicas principales

| Modulo | Rol | Descripcion |
|--------|-----|-------------|
| **Informacion Personal** | Docente / Admin | Ficha de datos del usuario autenticado |
| **Mis Cursos** | Docente | Lista de cursos asignados con detalle de alumnos y calificaciones |
| **Horarios y Actividades** | Docente | Calendario de eventos academicos y reuniones |
| **Registro de Asistencia** | Docente | Control de asistencia diaria (Presente / Tardanza / Falta) |
| **Incidencias** | Docente / Admin | Registro y seguimiento de incidencias disciplinarias |
| **Reportes** | Docente | Estadisticas y resumenes de rendimiento estudiantil |
| **Reporte Docentes** | Admin | Evaluacion y calificacion del desempeno docente |
| **Mensajeria y UGEL** | Docente / Admin | Comunicacion interna y mensajes de la UGEL (solo admin) |
| **Plantillas UGEL** | Admin | Descarga de documentos oficiales (curriculo, silabos, sesiones, etc.) |
| **Gestion Economica** | Admin | Dashboard con metricas financieras del colegio |

## Arquitectura MVC

El proyecto sigue el patron **Modelo-Vista-Controlador** para separar responsabilidades:

- **Modelo (models/):** Clases PHP que representan las entidades del sistema y encapsulan la logica de acceso a datos.
- **Vista (views/):** Archivos HTML que definen la interfaz de usuario para cada seccion del sistema.
- **Controlador (controllers/):** Clases PHP que reciben las peticiones, coordinan la logica de negocio y seleccionan la vista correspondiente.
- **Core (core/):** Componentes centrales del sistema, como la clase de conexion a base de datos.

## Autenticacion

El sistema incluye un modulo de autenticacion con soporte en `localStorage` y fallback por parametro URL (`?u=`), permitiendo funcionar tanto en servidor local como abriendo los archivos directamente (`file://`).

**Credenciales de prueba:**

| Usuario | Rol | Vista |
|---------|-----|-------|
| `docen` | Docente | Portal Docente |
| `dire` | Director / Administrativo | Portal Administrativo |

## Estructura del proyecto

```
Sistema_Gestion_IE/
├── controllers/                # Controladores (logica de negocio y enrutamiento)
├── core/                       # Componentes centrales del sistema
│   └── database.php            # Clase de conexion a MySQL con PDO
├── models/                     # Modelos (acceso a datos y entidades)
├── views/                      # Vistas (interfaz de usuario)
│   ├── index.html              # Punto de entrada (redireccion por rol)
│   ├── login.html              # Pagina de inicio de sesion
│   ├── admin.html              # Dashboard administrativo (Director)
│   └── docente.html            # Dashboard docente
├── public/                     # Recursos estaticos publicos
│   ├── css/
│   │   ├── variables.css       # Tokens de diseno (colores, fuentes, espaciado)
│   │   ├── common.css          # Estilos base compartidos
│   │   ├── login.css           # Estilos de la pagina de login
│   │   ├── dashboard.css       # Layout del dashboard (sidebar + navbar)
│   │   └── components.css      # Componentes reutilizables (tablas, cards, modales)
│   ├── js/
│   │   ├── auth.js             # Modulo de autenticacion y guardias de ruta
│   │   ├── router.js           # SPA hash-router para navegacion sin recarga
│   │   ├── mockData.js         # Base de datos simulada con LocalStorage
│   │   ├── admin.js            # Logica y vistas del portal administrativo
│   │   └── docente.js          # Logica y vistas del portal docente
│   └── docs/
│       ├── curriculo.pdf       # Plantilla de curriculo
│       └── syllabus.pdf        # Plantilla de silabo
├── LICENSE                     # AGPL-3.0
└── README.md
```

## Como ejecutar

### Requisitos previos

- Servidor web con soporte PHP (Apache, XAMPP, Laragon, etc.)
- MySQL o MariaDB
- Base de datos `colegio_DB` configurada en el servidor

### Ejecucion

1. Clonar o copiar el proyecto en el directorio raiz de tu servidor web (por ejemplo, `htdocs/` en XAMPP).
2. Crear la base de datos `colegio_DB` en MySQL.
3. Acceder desde el navegador a la ruta correspondiente del servidor local:

```
http://localhost/Sistema_Gestion_IE/views/index.html
```

Tambien es posible abrir las vistas directamente en el navegador (`file://`) para pruebas rapidas del frontend, ya que el sistema cuenta con datos simulados mediante `mockData.js` y `localStorage`.

## Tecnologias

- **PHP** -- Backend, conexion a base de datos con PDO, logica del servidor
- **MySQL** -- Base de datos relacional (colegio_DB)
- **HTML5** -- Estructura semantica de las vistas
- **CSS3** -- Variables CSS, Flexbox, Grid, diseno responsivo
- **JavaScript ES6** -- Vanilla JS, SPA con hash-router, sin frameworks
- **LocalStorage** -- Persistencia de datos simulada en el navegador (modo prototipo)
- **SVG** -- Iconografia inline sin dependencias externas

## Licencia

Este proyecto esta licenciado bajo la **GNU Affero General Public License v3.0** (AGPL-3.0). Consulta el archivo [LICENSE](LICENSE) para mas detalles.
