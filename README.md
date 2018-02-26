Sesile V4 - dev 
===============

### Prerequisites

Requires a web server with :
* **PHP >= 5.6**
* **Latest Node & Yarn**
* **Docker**
* **Docker-copmpose >= 1.17.1 ([official install doc](https://docs.docker.com/compose/install/#install-compose))**  

### Installing
1. Clone this repository

2. Install Composer dependencies 

    `$ php composer.phar self-update`
    `$ php composer.phar install`
  
3. Install Node dependencies 

    `$ yarn install`
    `$ yarn dev`
    
4. Docker configuration
    1. `cp .env.dist .env`
    2. Build/run containers with (with and without detached mode) 
        ```bash
            $ docker-compose build
            $ docker-compose up -d
        ```
    3. Setting up or fixing symfony file permissions
        ```bash
           $ docker-compose exec php setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX /var/www/symfony/app
           $ docker-compose exec php setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX /var/www/symfony/app
        ``` 
    4. Update your system host file (ex: add nginxIpAdresse sesile.dev), obtain nginx ip adresse :
        ```bash
           $ sudo echo $(sudo docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+')
        ```

5. Populate database

Sesile require 'collectivité' informations and a user, you must define this informations in Collectivite and User tables.

The user to use in sesile must exist in the CAS.

[Mysql request example](https://forge.sictiam.fr/snippets/1)

Access to phMyAdmin [http://sesile.dev:8080](http://sesile.dev:8080) or nginx docker image ip adress with port 8080

### Running 
* Run webpack watch 
`$ yarn watch `

Open [http://sesile.dev]() or ip adress docker nginx `sudo echo $(sudo docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+')`

### Cron Jobs
* Think about setting up these cron jobs :

    - `* * * * * php /var/www/symfony/app/console sesile:user:delayedClasseurs`

### Sesile command list

* Notify users when their classeurs are delayed : `php app/console sesile:user:delayedClasseurs`


### Utils links
* [Webpack encore](http://symfony.com/doc/current/frontend.html) /  API to integrate Webpack into your Symfony application
* [ReactBundle](https://github.com/Limenius/ReactBundle/blob/master/Resources/doc/index.md) / server and client-side React rendering 
* [Symfony React Sandbox](https://github.com/Limenius/symfony-react-sandbox) / This sandbox provides an example of usage of ReactBundle
* [Symfony permissions files](https://symfony.com/doc/current/setup/file_permissions.html)
* [Docker Symfony](https://github.com/maxpou/docker-symfony)
