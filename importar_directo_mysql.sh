#!/bin/bash

# Script para importar directamente con MySQL transformando la base de datos antigua
# Este script modifica el dump SQL para adaptarlo a la nueva estructura

echo "=== IMPORTADOR DIRECTO MYSQL ==="
echo "Preparando archivo SQL transformado..."

# Variables de conexión
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="egresados_db"
DB_USER="user"
DB_PASS="password"

# Archivo original
ORIG_FILE="bdwvexa_backup.sql"
TEMP_FILE="bdwvexa_transformado_temp.sql"

# Crear archivo temporal transformado
echo "Transformando estructura de tablas..."

# Comenzar archivo transformado
echo "USE egresados_db;" > "$TEMP_FILE"
echo "SET FOREIGN_KEY_CHECKS=0;" >> "$TEMP_FILE"
echo "" >> "$TEMP_FILE"

# Extraer SOLO los INSERT INTO de las tablas que necesitamos
echo "Extrayendo datos de dirigidas..."
grep -A 100000 "INSERT INTO \`dirigidas\` VALUES" "$ORIG_FILE" | sed '/INSERT INTO/!d;/dirigidas/!d' | head -1 >> "$TEMP_FILE" || true

echo "Extrayendo datos de generaciones..."
grep -A 100000 "INSERT INTO \`generaciones\` VALUES" "$ORIG_FILE" | sed '/INSERT INTO/!d;/generaciones/!d' | head -1 | sed 's/`generaciones`/`generacion`/g' >> "$TEMP_FILE" || true

echo "Extrayendo datos de ciclo..."  
grep -A 100000 "INSERT INTO \`ciclo\` VALUES" "$ORIG_FILE" | sed '/INSERT INTO/!d;/ciclo/!d' | head -1 | sed 's/`ciclo`/`ciclo_escolar`/g' >> "$TEMP_FILE" || true

echo "" >> "$TEMP_FILE"
echo "SET FOREIGN_KEY_CHECKS=1;" >> "$TEMP_FILE"

echo "Archivo transformado creado: $TEMP_FILE"
echo ""
echo "Importando a MySQL..."

# Importar usando docker exec
docker exec -i db mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$TEMP_FILE"

if [ $? -eq 0 ]; then
    echo "✓ Importación exitosa!"
else
    echo "✗ Error en la importación"
    exit 1
fi

echo "Limpiando archivo temporal..."
# rm -f "$TEMP_FILE"

echo "=== PROCESO COMPLETADO ==="
