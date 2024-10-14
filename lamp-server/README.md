# EazyPay
Group payment management web application

## Build the image
First, you have to build the docker image. This process has to be done only once.
```
git clone https://github.com/pablodorrio/EazyPay.git
cd EazyPay
docker build . -t eazypay
```

## Running the server

#### On Linux (bash)
```
cd eazypay
docker run -it -e APACHE_ROOT=www -e PHP_DISPLAY_ERRORS=On \
 -e DOCKER_USER_ID=`id -u \`whoami\`` -p "80:80" -v ${PWD}:/app \
 -v ${PWD}/mysql:/var/lib/mysql --name eazypay-1 --rm eazypay
```
#### On Windows (powershell)
```
cd .\eazypay
docker run -it -e APACHE_ROOT=www -e PHP_DISPLAY_ERRORS=On -p "80:80" -v ${PWD}:/app -v ${PWD}/mysql:/var/lib/mysql --name eazypay-1 --rm eazypay

```

`eazypay-1` is the name of the docker *container*, i.e., a running instance
of the `eazypay` image.

The first time you run the server in your directory, a MySQL data folder will
be created inside your project directory `./mysql`

A password for the `admin` user of MySQL is shown the first time.

Your server is available at: http://localhost
(if you want to use another port of your host machine, change `-p "80:80"`
by `-p "<another_port>:80"`)

## Using MySQL
1. You can use PHPMyAdmin by going to http://localhost/phpmyadmin. You have
to login with the `admin` user.
2. Alternatively, you can use the MySQL client for a running instance by issuing:
```
docker exec -it eazypay-1 mysql -uroot
```

## Backup a database
You can get an SQL dump of a given database in your running instance by issuing:
```
docker exec -it eazypay-1 mysqldump -uroot [yourdatabasename] > db.sql
```
