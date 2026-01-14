SELECT e.id, e.email, COUNT(b.id) as total_bitacoras
FROM egresado e
LEFT JOIN bitacora_encuesta b ON e.id = b.egresado_id
WHERE e.email = 'daniel25012025@gmail.com'
GROUP BY e.id;

SELECT COUNT(*) as egresados_totales FROM egresado;
SELECT COUNT(*) as bitacoras_totales FROM bitacora_encuesta;
