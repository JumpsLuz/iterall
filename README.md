# ITERALL

**Plataforma de gestión de versiones para artistas digitales**

ITERALL es una aplicación web que permite a artistas digitales documentar y gestionar el proceso creativo de sus obras, guardando múltiples iteraciones (versiones) de cada trabajo con notas, tiempo dedicado y comparaciones visuales.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![Cloudinary](https://img.shields.io/badge/Cloudinary-API-3448C5?style=flat&logo=cloudinary&logoColor=white)

---

## Características

- **Gestión de Iteraciones**: Sube múltiples versiones de un trabajo y documenta los cambios
- **Comparador Visual**: Compara versiones lado a lado con slider interactivo
- **Organización por Proyectos**: Agrupa trabajos en proyectos y mini-proyectos
- **Categorías y Etiquetas**: Clasifica tus obras para encontrarlas fácilmente
- **Perfiles de Artista**: Perfil público con nombre artístico, avatar y redes sociales
- **Colecciones**: Guarda trabajos de otros artistas que te inspiren
- **Explorador Público**: Descubre el trabajo de otros artistas
- **Almacenamiento en la Nube**: Imágenes almacenadas en Cloudinary

---

## 🛠️ Requisitos

- **XAMPP** (o similar) con:
  - PHP 8.0 o superior
  - MySQL 8.0 o superior
  - Apache
- **Composer** (gestor de dependencias PHP)
- **Cuenta en Cloudinary** (gratuita) para almacenamiento de imágenes

---

## Instalación

### 1. Clonar el repositorio

```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-usuario/iterall.git
cd iterall
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar Cloudinary

Crea un archivo `.env` en la raíz del proyecto:

```env
CLOUDINARY_CLOUD_NAME=tu_cloud_name
CLOUDINARY_API_KEY=tu_api_key
CLOUDINARY_API_SECRET=tu_api_secret
```

> Obtén estas credenciales en [cloudinary.com](https://cloudinary.com) → Dashboard → API Keys

### 4. Configurar la Base de Datos

#### 4.1 Crear la base de datos

Abre phpMyAdmin (`http://localhost/phpmyadmin`) y ejecuta:

```sql
CREATE DATABASE iterall_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 4.2 Configurar credenciales

Edita el archivo `app/Config/Database.php`:

```php
$host = 'localhost';
$db = 'iterall_db';
$user = 'root';        // Tu usuario de MySQL
$pass = '';            // Tu contraseña de MySQL (vacía por defecto en XAMPP)
```

#### 4.3 Importar estructura

Importa el archivo SQL de la base de datos.

### 5. Iniciar el servidor

1. Abre XAMPP Control Panel
2. Inicia **Apache** y **MySQL**
3. Accede a `http://localhost/iterall/public/`

---

## 📁 Estructura del Proyecto

```
iterall/
├── app/
│   ├── Config/           # Configuración (BD, Cloudinary, Auth)
│   ├── Controllers/      # Controladores de la lógica
│   ├── Helpers/          # Funciones auxiliares
│   └── Models/           # Modelos de datos
├── docs/                 # Documentación adicional
├── public/               # Archivos públicos (punto de entrada)
│   ├── css/              # Estilos CSS
│   ├── js/               # Scripts JavaScript
│   ├── includes/         # Componentes reutilizables (sidebar, etc.)
│   ├── views/            # Vistas organizadas por rol
│   └── *.php             # Páginas principales
└── vendor/               # Dependencias (generado por Composer)
```

---

## 🔐 Roles de Usuario

| Rol | Descripción |
|-----|-------------|
| **Cliente** | Puede explorar trabajos públicos y guardar en colecciones |
| **Artista** | Todas las funciones: crear proyectos, posts, iteraciones |

> Un cliente puede convertirse en artista desde su perfil.

---

## 📸 Límites

- Máximo **50 imágenes** por post (suma de todas las iteraciones)
- Formatos permitidos: JPG, PNG, GIF, WEBP
- Tamaño máximo por imagen: 10MB (configurado en Cloudinary)

---

## 🤝 Tecnologías Utilizadas

- **Backend**: PHP 8+ (sin framework)
- **Base de Datos**: MySQL con PDO
- **Frontend**: HTML5, CSS3, JavaScript Vanilla
- **Almacenamiento**: Cloudinary (SDK PHP)
- **Iconos**: Font Awesome 6
- **Entorno**: XAMPP (Apache + MySQL)

---

## 👤 Autor

**Joaquín Villalón**  
📧 jvemillapele@outlook.com

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.
