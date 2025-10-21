#!/bin/bash

# Script para probar el endpoint de limpieza de logs

echo "🧪 Probando endpoint de limpieza de logs..."
echo ""

# Obtener token CSRF
echo "1. Obteniendo página de logs para extraer CSRF token..."
RESPONSE=$(curl -s -c /tmp/cookies.txt http://127.0.0.1:8001/logs)
CSRF_TOKEN=$(echo "$RESPONSE" | grep -oP 'csrf-token" content="\K[^"]+')

if [ -z "$CSRF_TOKEN" ]; then
    echo "❌ Error: No se pudo obtener el CSRF token"
    exit 1
fi

echo "✅ CSRF Token obtenido: ${CSRF_TOKEN:0:20}..."
echo ""

# Hacer backup del log actual
echo "2. Haciendo backup del log actual..."
cp storage/logs/laravel.log storage/logs/laravel.log.backup
echo "✅ Backup creado"
echo ""

# Intentar limpiar el log
echo "3. Intentando limpiar el log..."
CLEAR_RESPONSE=$(curl -s -X POST \
    -H "Content-Type: application/json" \
    -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
    -H "Accept: application/json" \
    -b /tmp/cookies.txt \
    -d '{"file":"laravel.log"}' \
    http://127.0.0.1:8001/logs/clear)

echo "Respuesta del servidor:"
echo "$CLEAR_RESPONSE" | jq . 2>/dev/null || echo "$CLEAR_RESPONSE"
echo ""

# Verificar si el log se limpió
if [ -f storage/logs/laravel.log ]; then
    SIZE=$(stat -f%z storage/logs/laravel.log 2>/dev/null || stat -c%s storage/logs/laravel.log 2>/dev/null)
    if [ "$SIZE" -eq 0 ]; then
        echo "✅ Log limpiado correctamente (tamaño: 0 bytes)"
    else
        echo "⚠️  El log no se limpió (tamaño: $SIZE bytes)"
    fi
else
    echo "❌ El archivo de log no existe"
fi
echo ""

# Restaurar el backup
echo "4. Restaurando backup..."
mv storage/logs/laravel.log.backup storage/logs/laravel.log
echo "✅ Backup restaurado"
echo ""

# Limpiar archivos temporales
rm -f /tmp/cookies.txt

echo "🎉 Prueba completada"
