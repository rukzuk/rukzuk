#!/usr/bin/env bash
set -e

kill $(cat /var/lib/mysql/rz-mysql-init.pid)
rm /var/lib/mysql/rz-mysql-init.pid
