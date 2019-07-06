#!/bin/bash

cp /app/.env.example /app/.env
/opt/bitnami/php/bin/php /app/artisan key:generate
