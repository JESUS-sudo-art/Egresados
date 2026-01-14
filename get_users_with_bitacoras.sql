SELECT be.egresado_id, COUNT(*) as bitacoras, e.email, e.nombre 
FROM bitacora_encuesta be 
LEFT JOIN egresado e ON be.egresado_id = e.id 
GROUP BY be.egresado_id 
ORDER BY bitacoras DESC 
LIMIT 5;
