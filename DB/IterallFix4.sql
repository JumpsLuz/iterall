DROP DATABASE IF EXISTS iterall_db;
CREATE DATABASE iterall_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE iterall_db;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE roles_usuario (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre_rol VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles_usuario(id),
  INDEX idx_usuario_email (email)
);

CREATE TABLE perfiles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT UNIQUE NOT NULL,
  nombre_artistico VARCHAR(100),
  biografia TEXT,
  avatar_url VARCHAR(2048),
  banner_url VARCHAR(2048),
  perfil_publico BOOLEAN DEFAULT TRUE,
  redes_sociales_json JSON,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE catalogo_especialidades (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE perfil_especialidades (
  perfil_id INT NOT NULL,
  especialidad_id INT NOT NULL,
  PRIMARY KEY (perfil_id, especialidad_id),
  FOREIGN KEY (perfil_id) REFERENCES perfiles(id) ON DELETE CASCADE,
  FOREIGN KEY (especialidad_id) REFERENCES catalogo_especialidades(id) ON DELETE CASCADE
);

CREATE TABLE estados_proyecto (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre_estado VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE categorias (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre_categoria VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE etiquetas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre_etiqueta VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE proyectos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  creador_id INT NOT NULL,
  categoria_id INT NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT,
  avatar_url VARCHAR(2048),
  banner_url VARCHAR(2048),
  estado_id INT NOT NULL,
  es_publico BOOLEAN DEFAULT TRUE,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (creador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (estado_id) REFERENCES estados_proyecto(id),
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  INDEX idx_proyecto_creador (creador_id)
);

CREATE TABLE miniproyectos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  creador_id INT NOT NULL,
  proyecto_id INT NULL,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (creador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE SET NULL,
  INDEX idx_miniproy_creador (creador_id)
);

CREATE TABLE roles_colaborador (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre_rol VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE colaboradores (
  id INT PRIMARY KEY AUTO_INCREMENT,
  proyecto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  rol_colaborador_id INT NOT NULL,
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (rol_colaborador_id) REFERENCES roles_colaborador(id),
  UNIQUE KEY unique_colaborador_proyecto (proyecto_id, usuario_id)
);

CREATE TABLE posts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  creador_id INT NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  categoria_id INT NOT NULL,
  proyecto_id INT NULL,
  miniproyecto_id INT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (creador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (miniproyecto_id) REFERENCES miniproyectos(id) ON DELETE CASCADE,
  CONSTRAINT chk_solo_un_padre CHECK (
    (proyecto_id IS NOT NULL AND miniproyecto_id IS NULL)
    OR
    (proyecto_id IS NULL AND miniproyecto_id IS NOT NULL)
  )
);

CREATE TABLE iteraciones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  post_id INT NOT NULL,
  numero_version INT NOT NULL,
  notas_cambio TEXT,
  tiempo_dedicado_min INT,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  UNIQUE KEY unique_version_post (post_id, numero_version)
);

CREATE TABLE imagenes_iteracion (
  id INT PRIMARY KEY AUTO_INCREMENT,
  iteracion_id INT NOT NULL,
  url_archivo VARCHAR(2048) NOT NULL,
  cloud_id VARCHAR(255),
  es_principal BOOLEAN DEFAULT FALSE,
  orden_visual INT DEFAULT 0,
  FOREIGN KEY (iteracion_id) REFERENCES iteraciones(id) ON DELETE CASCADE
);

CREATE TABLE post_etiquetas (
  post_id INT NOT NULL,
  etiqueta_id INT NOT NULL,
  PRIMARY KEY (post_id, etiqueta_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
);

CREATE TABLE proyecto_etiquetas (
  proyecto_id INT NOT NULL,
  etiqueta_id INT NOT NULL,
  PRIMARY KEY (proyecto_id, etiqueta_id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
);

CREATE TABLE colecciones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  es_privada BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE items_coleccion (
  id INT PRIMARY KEY AUTO_INCREMENT,
  coleccion_id INT NOT NULL,
  post_id INT NOT NULL,
  fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (coleccion_id) REFERENCES colecciones(id) ON DELETE CASCADE,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  UNIQUE KEY unique_item_coleccion (coleccion_id, post_id)
);

INSERT INTO categorias (nombre_categoria) VALUES
('Ilustración 2D'),
('Modelado 3D'),
('Animación'),
('Concept Art'),
('Arte Digital'),
('Arte Tradicional'),
('Diseño de Personajes'),
('Diseño de Escenarios'),
('Game Art'),
('Otro');

INSERT INTO estados_proyecto (nombre_estado) VALUES
('Activo'),
('En Pausa'),
('Finalizado'),
('Cancelado');

INSERT INTO etiquetas (nombre_etiqueta) VALUES ('Destacado') 
ON DUPLICATE KEY UPDATE nombre_etiqueta = 'Destacado';

INSERT INTO etiquetas (nombre_etiqueta) VALUES ('#@#_no_mini_proyecto_#@#') 
ON DUPLICATE KEY UPDATE nombre_etiqueta = '#@#_no_mini_proyecto_#@#';

INSERT INTO roles_usuario (nombre_rol) VALUES ('Artista'), ('Cliente');

SET FOREIGN_KEY_CHECKS = 1;
