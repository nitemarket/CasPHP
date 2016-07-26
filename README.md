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
