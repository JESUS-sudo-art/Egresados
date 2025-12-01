SELECT '=== USUARIO ===' as info;
SELECT id, email, name FROM users WHERE email = 'armando345@gmail.com';

SELECT '=== EGRESADO ===' as info;
SELECT id, email, nombre, apellido_paterno, usuario_id FROM egresado WHERE email = 'armando345@gmail.com';

SELECT '=== EGRESADO POR USUARIO_ID ===' as info;
SELECT e.id, e.email, e.nombre, e.apellido_paterno, e.usuario_id 
FROM egresado e 
JOIN users u ON e.usuario_id = u.id 
WHERE u.email = 'armando345@gmail.com';

SELECT '=== RESPUESTAS POR EGRESADO_ID ===' as info;
SELECT COUNT(*) as total, encuesta_id 
FROM respuesta r
JOIN egresado e ON r.egresado_id = e.id
WHERE e.email = 'armando345@gmail.com'
GROUP BY encuesta_id;

SELECT '=== RESPUESTAS POR USER_ID (INCORRECTO) ===' as info;
SELECT COUNT(*) as total, encuesta_id 
FROM respuesta r
JOIN users u ON r.egresado_id = u.id
WHERE u.email = 'armando345@gmail.com'
GROUP BY encuesta_id;

SELECT '=== ENCUESTAS ASIGNADAS ===' as info;
SELECT ea.encuesta_id, 
       (SELECT COUNT(*) FROM respuesta r WHERE r.encuesta_id = ea.encuesta_id AND r.egresado_id = ea.egresado_id) as respuestas_count
FROM encuesta_asignada ea
JOIN egresado e ON ea.egresado_id = e.id
WHERE e.email = 'armando345@gmail.com';
