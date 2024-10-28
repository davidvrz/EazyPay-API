@echo off
set "PWD=%cd%"
docker run -it -e APACHE_ROOT=www -e PHP_DISPLAY_ERRORS=On -p "80:80" -v ${PWD}:/app -v ${PWD}/mysql:/var/lib/mysql --name lampserver-1 --rm lampserver


