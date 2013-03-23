Gongo
================================================================
Gongo is a micro web application framework for PHP 5.2 or later.
It is successor to Picowa and PicowaCore framework with GongoDB.

Install
----------------------------------------------------------------
### Using composer:
```
{
    "require": {
        "no22/gongo": "dev-master"
    }
}
```
### Manual install:
Download the library and put it in your include path.

### .htaccess:
```
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?__url__=$1 [QSA,L]
```

### index.php:
```php
    <?php
    define('PATH_TO_SRC', '/path/to/src');
    // using composer
    require PATH_TO_SRC . '/vendor/autoload.php';
    // or require PATH_TO_SRC . "path/to/gongo.php";
    require PATH_TO_SRC . '/apps/your_application/app.php';
```

### Application directory:
See example directory.

* apps
    * your_application
        * app
            * (application specific classes)
        * config
            * config.ini (configuration file)
            * development.ini (configuration file for development)
            * production.ini (configuration file for production)
        * lib
            * (libraries)
        * template
            * php
                * (php template files)
            * twig (template directory for Twig)
            * smarty3 (template directory for Smarty3)
        * work (set writable permission)
        * app.php (front controller)

Hello world (minimum)
----------------------------------------------------------------

```php
    <?php // app.php
    $app = new Gongo_App(__DIR__);
    $app->get('/', function(){
        return "Hello world!";
    });
    $app->run();
```

Hello world (using application class)
----------------------------------------------------------------

GET / or GET /index executes function getIndex($app) in application class.

```php
    <?php // app.php
    class Application extends Gongo_App
    {
        public function getIndex($app)
        {
            return "Hello world!";
        }
    }
    
    $app = new Application(__DIR__);
    $app->init()->run();
```

Hello world (using root controller)
----------------------------------------------------------------

```php
    <?php // app.php
    class Application extends Gongo_App
    {
        public $uses = array(
            'root' => 'Hello_Controller_Root',
        );
    }
    
    $app = new Application(__DIR__);
    $app->init()->run();
```

```php
    <?php // app/Hello/Controller/Root.php
    class Hello_Controller_Root extends Gongo_App_Controller
    {
        public function getIndex($app)
        {
            return "Hello world!";
        }
    }
```

Hello world (using controller)
----------------------------------------------------------------

```php
    <?php // app/Hello/Controller/Root.php
    class Hello_Controller_Root extends Gongo_App_Controller
    {
        public $uses = array(
            // register controller alias and controller class
            '/hello' => 'Hello_Controller_Hello',
        );
    }
```

```php
    <?php // app/Hello/Controller/Hello.php
    class Hello_Controller_Hello extends Gongo_App_Controller
    {
        public function getIndex($app)
        {
            return "Hello world!";
        }
    }
```
GET /hello/index executes function getIndex($app) in Hello_Controller_Hello class.

Hello someone (using parameter)
----------------------------------------------------------------

GET /controller_alias/action/arg1/arg2/arg3 executes function getAction($app, $arg1, $arg2, $arg3) in controller.

```php
    <?php // app/Hello/Controller/Root.php
    class Hello_Controller_Root extends Gongo_App_Controller
    {
        public function getHello($app, $name)
        {
            return "Hello {$name}!";
        }
    }
```

License
------------------------------------------------------------------------
Gongo is dual Licensed MIT and GPLv3. You may choose the license that fits best for your project.
