Sesile V4
===============

### Prerequisites

Requires a web server with :
* **PHP >= 5.6**
* **Node 8.10.0 & npm 5.6.0 and Yarn**
* **Docker**
* **Docker-copmpose >= 1.17.1 ([official install doc](https://docs.docker.com/compose/install/#install-compose))**  

### Installing
1. Clone this repository

2. Install Composer dependencies

    * `$ php composer.phar self-update`
    * Before dependencies install, ensure to set the setasign credentials in `auth.json`
    * `$ php composer.phar install` (there can be connection limits between composer and Github, just generate a token from github and put it in `auth.json` file)
  
3. Install Node dependencies 

    * `$ yarn install`
    
4. Docker configuration

    * `cp .env.dist .env`
    * Build (only first time) and run containers
        ```bash
            $ docker-compose build
            $ docker-compose up -d
        ```
    * Setting up or fixing symfony file permissions
        ```bash
           $ docker-compose exec php setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX /var/www/symfony/app
           $ docker-compose exec php setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX /var/www/symfony/app
        ``` 
    * Update your system host file (ex: add nginxIpAddress sesile.dev), obtain nginx ip address from docker :
        ```bash
           $ sudo echo $(sudo docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+')
        ```

5. Populate database

Sesile require 'collectivité' informations and a user, you must define this informations in Ozwillo.

The user to use in sesile must exist in Ozwillo.
The 'Collectivité' in sesile must exist in Ozwillo as a service

In Ozwillo, when you create your app you have to define informations : 
 - Redirect uri: http://you-url/login/check-ozwillo
 - Provisionning service: http://you-url/collectivite/new
 - Update service: http://you-url/collectivite/update

Ozwillo will give you back a secret token. You have to put it in your config.yml file :
 - parameters.ozwillo_secret: your-ozwillo-secret

Access to phMyAdmin [http://localhost:8080](http://localhost:8080) or nginx docker image ip adress with port 8080

### Dev running 
* Run webpack watch 
`$ yarn watch `

Open [http://sesile.dev]() or ip adress docker nginx `sudo echo $(sudo docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+')`

### Cron Jobs
* Think about setting up these cron jobs :

    - `* * * * * php /var/www/symfony/app/console sesile:user:delayedClasseurs`
    - TODO: Add all Sesile cron job 

### Sesile command list

* Notify users when their classeurs are delayed : `php app/console sesile:user:delayedClasseurs`
* TODO: Add all Sesile command


### Utils links
* [Webpack encore](http://symfony.com/doc/current/frontend.html) /  API to integrate Webpack into your Symfony application
* [ReactBundle](https://github.com/Limenius/ReactBundle/blob/master/Resources/doc/index.md) / server and client-side React rendering 
* [Symfony React Sandbox](https://github.com/Limenius/symfony-react-sandbox) / This sandbox provides an example of usage of ReactBundle
* [Symfony permissions files](https://symfony.com/doc/current/setup/file_permissions.html)
* [Docker Symfony](https://github.com/maxpou/docker-symfony)
* [Ozwillo Doc](https://doc.ozwillo.com)
