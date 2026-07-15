<div align="center">

# OfficeHub

### Plataforma interna para equipos de tecnología

Gestión centralizada de documentación, incidencias, repositorios, tareas y colaboración técnica.

<br>

![OfficeHub Preview](docs/screenshots/cover.jpg)

<br>

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?logo=javascript&logoColor=black)
![Apache](https://img.shields.io/badge/Apache-XAMPP-D22128?logo=apache&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC-4F46E5)
![Status](https://img.shields.io/badge/Status-Portfolio_Showcase-16A34A)

</div>

---

## Sobre el proyecto

**OfficeHub** es una plataforma web diseñada para centralizar distintas tareas habituales de una oficina de sistemas.

El proyecto reúne en una misma aplicación la gestión de documentación técnica, incidencias, tableros de trabajo, usuarios, permisos y repositorios.

Esta versión fue preparada especialmente como muestra de portfolio. Conserva componentes representativos de la arquitectura, la interfaz y la lógica general, pero algunas integraciones y reglas avanzadas fueron simplificadas para proteger la implementación completa del producto.

---

## El problema

En los equipos de tecnología, la información suele quedar distribuida entre documentos, mensajes, tableros y herramientas independientes.

Esto puede provocar:

- documentación difícil de encontrar;
- poca trazabilidad de los cambios;
- tareas dispersas;
- problemas para compartir procedimientos;
- dificultad para conocer el estado de cada proyecto;
- dependencia de varias plataformas externas.

OfficeHub propone reunir estos procesos en un único entorno organizado.

---

## Funcionalidades principales

### Documentación técnica

- Creación y edición de artículos.
- Organización mediante categorías.
- Archivos adjuntos.
- Búsqueda de procedimientos.
- Control administrativo del contenido.

### Gestión de incidencias

- Registro de solicitudes y problemas.
- Seguimiento del estado.
- Comentarios y archivos adjuntos.
- Organización por categorías.

### Tablero Kanban

- Listas y tarjetas de trabajo.
- Comentarios y responsables.
- Historial de tareas completadas.
- Adjuntos y trazabilidad.

### Repositorios y colaboración

- Catálogo centralizado de repositorios.
- Gestión de permisos.
- Visualización de archivos y commits.
- Flujo demostrativo de pull requests.
- Guías para trabajar con Git.

### Administración

- Gestión de usuarios.
- Áreas y roles.
- Permisos de acceso.
- Registro de actividad.
- Notificaciones internas.

---

## Capturas

### Panel principal

![Dashboard](docs/screenshots/dashboard.png)

### Documentación

![Documentation](docs/screenshots/documentation.png)

### Tablero de trabajo

![Kanban](docs/screenshots/kanban.png)

### Repositorios

![Repositories](docs/screenshots/repositories.png)

### Soporte e incidencias

![Support](docs/screenshots/support.png)

---

## Tecnologías

| Capa                 | Tecnología             |
| -------------------- | ---------------------- |
| Backend              | PHP 8                  |
| Frontend             | HTML, CSS y JavaScript |
| Base de datos        | MySQL                  |
| Servidor local       | Apache / XAMPP         |
| Arquitectura         | MVC personalizado      |
| Persistencia         | PDO                    |
| Control de versiones | Git                    |

---

## Arquitectura

El proyecto utiliza una arquitectura MVC construida sin frameworks externos.

```text
officehub-portfolio/
├── config/             # Configuración de la aplicación
├── public/             # Punto de entrada y recursos públicos
├── sql/                # Esquema demostrativo de base de datos
├── src/
│   ├── Controllers/    # Controladores
│   ├── Core/           # Router, base de datos, sesiones y vistas
│   ├── Models/         # Acceso y representación de datos
│   ├── Services/       # Servicios e integraciones demostrativas
│   └── Views/          # Interfaz de usuario
└── storage/            # Archivos generados por la aplicación
```
