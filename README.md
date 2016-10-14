# CasPHP

Clear And Straight-forward PHP Webapp Framework

It helps developers quickly write simple and powerful web application & APIs. Emphasizing in cleanliness and simplicity minimizes the duration of understanding framework and allows user start his first line of codes quickly.

### Features

* Powerful router
    * Standard and custom HTTP methods
    * Route parameters with wildcards and conditions
    * Route redirect
    * RESTful web service
* Template rendering with custom views
* Simple configuration

## Getting started

### Web server

#### Apache

Ensure the `.htaccess` and `index.php` files are in the same public-accessible directory. The `.htaccess` file
should contain this code:

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]

Additionally, make sure your virtual host is configured with the AllowOverride option so that the .htaccess rewrite rules can be used:

AllowOverride All
   
#### Google app engine

Two steps are required to successfully run your web application on Google App Engine. First, ensure the `app.yaml` file includes a default handler to `index.php`:

    application: your-app-name
    version: 1
    runtime: php
    api_version: 1
    
    handlers:
    # ...
    - url: /.*
      script: public_html/index.php

### Configuration

If you place the working directory `index.php` inside a folder in server root, re-define `WORK_DIR` with your `/folder-name`. For production environment, change `ENV` to `live`. Model-View-Controller (MVC) folder name are also allowed to change.

/config/inc.config.php:

    define('ENV', 'live');
    define('WORK_DIR', '/casphp');
    define('ROUTER_ROOT', M_ROOT.'/controller');
    define('MODEL_ROOT', M_ROOT.'/model');
    define('TEMPLATE_DIR_NAME', '/templates');
    define('TEMPLATE_TYPE', '/default');
    define('TEMPLATE_ASSET', '/_include');
    
### MVC Structure

#### Controller

Default sub-folder name and file name are `public` and `index` respectively. Child-config file that is located inside each sub-folder will be executed first before controller script. The file name must follow its parent folder name.

/controller/api/inc.api.php

    try{
        //config file that loads before main file
    }
    catch(exception $e){
        switch($e->getCode()){
            //bad request
            default:
                $app->setStatus('400');
                echo $app->getMessageForCode('400');
                break;
        }

        exit;
    }
    
/controller/api/index.php

    $app->post('/login/:param', function ($param) use ($app) {
        $header = $app->getHeaders(); //header data
        $post = $app->postRequest(); //post data
        $get = $app->getRequest(); //get data
    
        $feedback = array(
            'header' => $header,
            'get' => $get,
            'post' => $post,
            'param' => $param,
        );
        
        $app->setStatus('200');
        $app->contentType('application/json');
        echo json_encode($feedback);
    });

#### Model

Classes must be registered via `spl_autoload_register` in root file `inc.include.php` to define its location. 

    spl_autoload_register(function ($class) {
        $classname = strtolower($class);
        if(strstr($classname, 'core') !== false){
            $path = MODEL_ROOT . '/core';
            include($path . '/' . $class . '.class.php');
        }
        elseif(strstr($classname, 'util') !== false){
            $path = MODEL_ROOT . '/util';
            include($path . '/' . $class . '.class.php');
        }
    });
    
    $core =  new Core();

#### View

Templates are accessible via controller file. `TEMPLATE_TYPE` (@see Configuration) is used to separate your template version.

By default, `TEMPLATE_DIR_NAME` and `TEMPLATE_TYPE` are set `/templates` and `/default` respectively and the file path will be `/root/templates/default`.
    
    $app->notFound(function () use ($app) {
        $app->render('/404.html'); //relative path
    });
    
    $app->get('/', function () use ($app) {
        $app->render('/public/index.html'); //relative path
    });
    
`<%include_path%>` is used to substitute the absolute path of assets. `TEMPLATE_ASSET` (@see Configuration) is changeable for different folder name.

    <script src="<%include_path%>/js/main.js"></script>
    
`<%%template-path%%>` is used to nest more template files together in order to effectively re-use the same template. It uses underscore (_) to separate the directory level.
    
    <%%public_include_document-head.html%%>
    <%%public_include_header.html%%>
    <div class="container">
        Hi CasPHP
    </div>
    <%%public_include_footer.html%%>

## Amazon Web Service (AWS)

### AWS Simple Storage Service (S3)

### Configuration

/config/inc.config.php:

    $vars['aws']['s3']['bucket_name'] = '### BUCKET NAME ###';
    $vars['aws']['s3']['aws_access_key_id'] = '### ACCESS KEY ID ###'; //for production
    $vars['aws']['s3']['aws_secret_access_key'] = '### ACCESS KEY ###'; //for production
    $vars['aws']['s3']['profile'] = 'default'; ### CREDENTIALS PROFILE ### //for development
    
For local development stage, store your AWS credentials data `/.aws/credentials` outside your working root directory to prevent your access key from being accidentally commited into remote repository. Refer [AWS documentation](http://docs.aws.amazon.com/cli/latest/userguide/cli-chap-getting-started.html#cli-config-files).

    [default]
    aws_access_key_id = ### ACCESS KEY ID ###
    aws_secret_access_key = ### ACCESS KEY ###

Configuration `PHP.ini`:

    ; Default Value: "EGPCS"
    ; Development Value: "GPCS"
    ; Production Value: "GPCS";
    ; http://php.net/variables-order
    variables_order = "EGPCS"

***Note**: For windows 10 users, please place your credentials data `/.aws/credentials` in public HOMEPATH `/Users/public` instead of `/Users/{username}`.

### Usage

Examples are included in `/controller`.

## How can I contribute?

* Fork it to become yours
* Make your changes to improve
* Submit pull request and let's discuss on your efforts
* I will merge it as you rock it!

## Credits

* Slim Framework v2.6.1 (http://www.slimframework.com)
* Neo Wong

## License

The CasPHP Framework is released under the MIT public license.