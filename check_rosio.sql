-- Revisar egresados sin carreras asignadas en tabla egresado_carrera
SELECT 
  e.id, 
  e.nombre, 
  e.apellidos, 
  e.email, 
  e.carrera_id,
  COUNT(ec.id) as num_carreras
FROM egresado e
LEFT JOIN egresado_carrera ec ON e.id = ec.egresado_id
WHERE e.nombre LIKE '%ROSIO%' OR e.apellidos LIKE '%RODRIGUEZ%'
GROUP BY e.id;
