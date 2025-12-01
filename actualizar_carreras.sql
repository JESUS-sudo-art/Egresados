-- Primero eliminar relaciones existentes
DELETE FROM unidad_carrera;
DELETE FROM egresado_carrera;

-- Eliminar carreras actuales
DELETE FROM carrera;

-- Insertar nuevas carreras
INSERT INTO carrera (nombre, nivel, tipo_programa, estatus) VALUES
('Medicina y Cirugía', 'Licenciatura', 'Escolarizado', 'A'),
('Enfermería y Obstetricia', 'Licenciatura', 'Escolarizado', 'A'),
('Economía', 'Licenciatura', 'Escolarizado', 'A'),
('Contaduría y Administración', 'Licenciatura', 'Escolarizado', 'A'),
('Ciencias Químicas', 'Licenciatura', 'Escolarizado', 'A'),
('Idiomas', 'Licenciatura', 'Escolarizado', 'A'),
('Arquitectura 5 de Mayo', 'Licenciatura', 'Escolarizado', 'A');
