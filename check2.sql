-- Usuario
SELECT '=== USUARIO ===' as '';
SELECT id, email, name FROM users WHERE email = 'armando345@gmail.com';

-- Egresado
SELECT '=== EGRESADO ===' as '';
SELECT id, email, nombre, apellidos FROM egresado WHERE email = 'armando345@gmail.com';

-- Respuestas
SELECT '=== RESPUESTAS ===' as '';
SELECT r.id, r.egresado_id, r.encuesta_id, r.pregunta_id, r.creado_en
FROM respuesta r
JOIN egresado e ON r.egresado_id = e.id  
WHERE e.email = 'armando345@gmail.com'
LIMIT 10;

-- Conteo de respuestas
SELECT '=== CONTEO RESPUESTAS POR ENCUESTA ===' as '';
SELECT encuesta_id, COUNT(*) as total
FROM respuesta r
JOIN egresado e ON r.egresado_id = e.id
WHERE e.email = 'armando345@gmail.com'
GROUP BY encuesta_id;
