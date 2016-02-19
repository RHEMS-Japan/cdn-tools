#!/bin/sh
(cd /var/www/fuelphp && php oil r cdntools $1 $2 $3 $4 $5 $6 $7 $8 $9)
# patch
chown -R www-data:www-data /var/www/fuelphp


