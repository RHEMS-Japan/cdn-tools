#!/bin/bash
/usr/sbin/rsyslogd &
/usr/sbin/cron -f &
service postfix start
/usr/sbin/apache2 -D NO_DETACH -D FOREGROUND

