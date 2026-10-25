# 🏨 MisterPlan - Gestor de Reservas (PHP Nativo)

Prueba técnica desarrollada en **PHP nativo**, **Docker**, **MySQL** y **Vanilla JS / Bootstrap**, empleando **Clever Cloud** para la BBDD y **Render** para el despliegue orquestado, implementando una arquitectura limpia en capas, API REST y un sistema de trazabilidad por eventos (Timeline).

---

## 🚀 Características Principales
* **PHP Nativo (Sin Frameworks)**: Cumplimiento estricto de las restricciones de la prueba utilizando una estructura modular limpia.
* **Arquitectura en Capas**: Separación clara entre Rutas (`index.php`), Servicios de Negocio (`ReservaService`), Repositorios (`ReservaRepository`) y Base de Datos (Patrón Singleton con PDO).
* **Interfaz Web y API REST**: Panel visual basado en Bootstrap y Twig con actualizaciones asíncronas mediante JavaScript (Fetch API).
* **Trazabilidad (Timeline)**: Historial detallado de eventos por cada reserva.
* **Sistema de Testing Nativo**: Tests unitarios y de integración desarrollados en PHP puro (con gestión de transacciones Rollback para no dejar residuos en BBDD).

---

## 🐳 Requisitos Previos
Asegúrate de tener instalado en tu equipo:
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)

---

### 🚀 Despliegue en Vivo

Puedes acceder a la aplicación funcionando en producción a través del siguiente enlace:

* **URL de la App:** [https://misterplangestionreservas.onrender.com/](https://misterplangestionreservas.onrender.com/)

---

## 🛠️ Puesta en Marcha (Instalación)

1. **Clonar o descomprimir** el proyecto en tu equipo local.
2. **Levantar los contenedores** con Docker Compose:
   ```bash
   docker compose up --build -d
   ```
3. **Acceso a la aplicación**:
   Navega a `http://localhost:8000/`.

---

## 🧪 Ejecución de Pruebas (Tests Nativos)

El proyecto incluye una suite de pruebas nativas (tanto unitarias como de integración con rollback automático) que no requieren dependencias externas de frameworks de testing.

Para ejecutarlas dentro del entorno Docker:

* **Ejecutar Test Unitario (Validaciones de negocio y excepciones):**
  ```bash
  docker exec reservas_web php tests/ReservaServiceTest.php
  ```

* **Ejecutar Test de Integración (Conexión PDO, Servicio y Rollback en BBDD):**
  ```bash
  docker exec reservas_web php tests/ReservaIntegracionTest.php
  ```

---

## 📁 Estructura del Proyecto

```text
├── public/             # Archivos públicos y punto de entrada web
├── src/                # Clases de negocio, repositorios y servicios
│   ├── Repository/     # Capa de acceso a datos (PDO)
│   └── Service/        # Capa de lógica de negocio y validaciones
├── templates/          # Vistas Twig (index, detalle, base y modal de creacion)
├── tests/              # Suite de pruebas nativas (unitarias e integración)
├── vendor/             # Archivos de Composer
├── composer.json       # Librerias del proyecto
├── composer.lock       # Dependencias (Generado por Composer)
├── database.sql        # Creacion e insercion de datos en BBDD
├── db.php              # Conexión Singleton
├── docker-compose.yml  # Orquestación de contenedores
├── Dockerfile          # Configuración del entorno PHP
└── README.md           # Documentación del proyecto
```