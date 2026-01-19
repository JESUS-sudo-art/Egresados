-- Migrar carreras desde la tabla antigüa academicos a la nueva egresado_carrera

INSERT INTO egresado_carrera (egresado_id, carrera_id, generacion_id, fecha_ingreso, fecha_egreso, tipo_egreso)
SELECT 
  a.egresados_id,
  COALESCE(
    (SELECT c.id FROM carrera c WHERE UPPER(c.nombre) LIKE CONCAT('%', UPPER(SUBSTRING(bc.nombre, 1, 20)), '%') AND c.deleted_at IS NULL LIMIT 1),
    5  -- carrera por defecto
  ),
  a.generaciones_id,
  NULL,
  NULL,
  NULL
FROM bdwvexa.academicos a
LEFT JOIN bdwvexa.carreras bc ON a.carreras_id = bc.id
WHERE NOT EXISTS (
  SELECT 1 FROM egresado_carrera WHERE egresado_id = a.egresados_id 
)
GROUP BY a.egresados_id;

-- Ver resultado
SELECT COUNT(*) as total_relaciones FROM egresado_carrera;
