Sesile V4 - dev 
===============

### Prerequisites

Requires a web server with :
* **PHP >= 5.6**
* **MySQL**
* **Latest Composer**
* **Latest Node & Yarn**

### Installing
1. Clone this repository

2. Install Composer dependencies 

    `$ composer install `
  
3. Install Node dependencies 

    `$ yarn install `
    
4. Configuration
    * Modify database parameters acces in `/app/config/parameters.yml`

5. Populate database

Sessile home page required 'collectivité' informations, you mut define this informations in 'Collectivite' table.

[Mysql request exemple](https://forge.sictiam.fr/snippets/1)

### Running 
* Run webpack watch 
`$ yarn watch `

Open [http://sesile.dev/app_dev.php]()

### Utils links
* [Webpack encore](http://symfony.com/doc/current/frontend.html) /  API to integrate Webpack into your Symfony application
* [ReactBundle](https://github.com/Limenius/ReactBundle/blob/master/Resources/doc/index.md) / server and client-side React rendering 
* [Symfony React Sandbox](https://github.com/Limenius/symfony-react-sandbox) / This sandbox provides an example of usage of ReactBundle
