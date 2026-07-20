# 🏫 Sistema de Gestión – IEP Corazón de Jesús

Sistema web de administración académica para la Institución Educativa Privada **Corazón de Jesús College**. Prototipo funcional construido con HTML, CSS y JavaScript puro (Vanilla JS), sin dependencias externas ni frameworks.

---

## ✨ Características principales

| Módulo | Rol | Descripción |
|--------|-----|-------------|
| **Información Personal** | Docente / Admin | Ficha de datos del usuario autenticado |
| **Mis Cursos** | Docente | Lista de cursos asignados con detalle de alumnos y calificaciones |
| **Horarios y Actividades** | Docente | Calendario de eventos académicos y reuniones |
| **Registro de Asistencia** | Docente | Control de asistencia diaria (Presente / Tardanza / Falta) |
| **Incidencias** | Docente / Admin | Registro y seguimiento de incidencias disciplinarias |
| **Reportes** | Docente | Estadísticas y resúmenes de rendimiento estudiantil |
| **Reporte Docentes** | Admin | Evaluación y calificación del desempeño docente |
| **Mensajería y UGEL** | Docente / Admin | Comunicación interna y mensajes de la UGEL (Solo admi) |
| **Plantillas UGEL** | Admin | Descarga de documentos oficiales (currículo, sílabos, sesiones, etc) |
| **Gestión Económica** | Admin | Dashboard con métricas financieras del colegio |

## 🛡️ Autenticación

El sistema incluye un módulo de autenticación basado en `localStorage` con fallback por parámetro URL (`?u=`), ideal para funcionar tanto en servidor local como abriendo los archivos directamente (`file://`).

**Credenciales de prueba:**

| Usuario | Rol | Vista |
|---------|-----|-------|
| `docen` | Docente | Portal Docente |
| `dire` | Director / Administrativo | Portal Administrativo |

## 🗂️ Estructura del proyecto

```
Sistema_Gestion_IE/
├── index.html          # Punto de entrada (redirección por rol)
├── login.html          # Página de inicio de sesión
├── admin.html          # Dashboard administrativo (Director)
├── docente.html        # Dashboard docente
├── css/
│   ├── variables.css   # Tokens de diseño (colores, fuentes, espaciado)
│   ├── common.css      # Estilos base compartidos
│   ├── login.css       # Estilos de la página de login
│   ├── dashboard.css   # Layout del dashboard (sidebar + navbar)
│   └── components.css  # Componentes reutilizables (tablas, cards, modales)
├── js/
│   ├── auth.js         # Módulo de autenticación y guardias de ruta
│   ├── router.js       # SPA hash-router para navegación sin recarga
│   ├── mockData.js     # Base de datos simulada con LocalStorage
│   ├── admin.js        # Lógica y vistas del portal administrativo
│   └── docente.js      # Lógica y vistas del portal docente
├── docs/
│   ├── curriculo.pdf   # Plantilla de currículo
│   └── syllabus.pdf    # Plantilla de sílabo
└── LICENSE             # AGPL-3.0
```

## 🚀 Cómo ejecutar

No requiere instalación ni dependencias. Simplemente abre `index.html` en tu navegador:

```bash
# Opción 1: Abrir directamente
xdg-open index.html        # Linux
open index.html             # macOS
start index.html            # Windows

# Opción 2: Servidor local (recomendado)
python3 -m http.server 8080
# Luego visita http://localhost:8080
```

## 🛠️ Tecnologías

- **HTML5** — Estructura semántica
- **CSS3** — Variables CSS, Flexbox, Grid, diseño responsivo
- **JavaScript ES6** — Vanilla JS, sin frameworks ni librerías externas
- **LocalStorage** — Persistencia de datos simulada en el navegador
- **SVG** — Iconografía inline sin dependencias de paquetes de iconos

## 📄 Licencia

Este proyecto está licenciado bajo la **GNU Affero General Public License v3.0** (AGPL-3.0). Consulta el archivo [LICENSE](LICENSE) para más detalles.
