# CasPHP

Clear and And Straight-forward PHP Webapp Framework

It helps developers quickly write simple and powerful web application & APIs. Emphasizing in cleanliness and simplicity allows new learners understand the framework fast and start writing codes.

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


## License

The CasPHP Framework is released under the MIT public license.
