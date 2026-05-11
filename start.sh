#!/bin/bash

# Railway asigna $PORT dinámicamente, Apache necesita escuchar en ese puerto
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf
fi

# Iniciar Apache en foreground
exec apache2-foreground
