# urlShortener

## AIM

* Allow to generate minified URLs  through a page with a form that will take a URL as sole input and will return a minified URL.
* Allow the redirection from minified URL to original URL: when a minified URL get requested, the application should redirect to the unminified URL.

## Technologies
* PHP 7.x
* Symfony 3.2.x
* Elasticsearch 5.x

## Install
* git clone git@github.com:RedBoool/urlShortener.git
* cd urlShortener
* composer install
* Create schema on elasticsearch
```
curl -X PUT \
  http://localhost:9200/url_shortener_v1/ \
  -H 'cache-control: no-cache' \
  -H 'postman-token: 94b32b4a-5381-6b83-fdbb-fe12fd7ca9b8' \
  -d '{
  "settings": {
    "refresh_interval": "1s",
    "number_of_shards" : 1
  },
  "aliases": {
    "url_shortener": {}
  },
  "mappings": {
    "url": {
      "properties": {
        "slug": {
          "type": "keyword"
        },
        "url": {
          "type": "keyword"
        },
        "created_at": {
          "type": "date"
        },
        "updated_at": {
          "type": "date"
        }
      }
    }
  }
}'
```
* Add content in elasticsearch
```
curl -X PUT \
  http://localhost:9200/url_shortener/url/pouet \
  -d '{
  "url": "http://www.google.fr",
  "slug": "pouet",
  "created_at": "2016-12-28T20:44:53+01:00",
  "updated_at": "2016-12-28T20:44:53+01:00"
}'
curl -X PUT \
  http://localhost:9200/url_shortener/url/youpi \
  -d '{
  "url": "https://www.etsy.com/fr/",
  "slug": "youpi",
  "created_at": "2016-12-28T20:44:53+01:00",
  "updated_at": "2016-12-28T20:44:53+01:00"
}'
```
* Configure NGinx (for exemple on domain urlshortener.dev)
```
# File /etc/nginx/sites-available/urlshortener.dev 

server {
    server_name urlshortener.dev;
    root /home/redboool/www/urlShortener/web;

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /app.php$is_args$args =404;
    }
    # DEV
    # This rule should only be placed on your development environment
    # In production, don't include this and don't deploy app_dev.php or config.php
    location ~ ^/(app_dev|config)\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        # When you are using symlinks to link the document root to the
        # current version of your application, you should pass the real
        # application path instead of the path to the symlink to PHP
        # FPM.
        # Otherwise, PHP's OPcache may not properly detect changes to
        # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
        # for more information).
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
    # PROD
    location ~ ^/app\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
       # When you are using symlinks to link the document root to the
       # current version of your application, you should pass the real
       # application path instead of the path to the symlink to PHP
       # FPM.
       # Otherwise, PHP's OPcache may not properly detect changes to
       # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
       # for more information).
       fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
       fastcgi_param DOCUMENT_ROOT $realpath_root;
       # Prevents URIs that include the front controller. This will 404:
       # http://domain.tld/app.php/some-path
       # Remove the internal directive to allow URIs like this
       internal;
   }

   # return 404 for all other php files not matching the front controller
   # this prevents access to other php files you don't want to be accessible.
   location ~ \.php$ {
     return 404;
   }

   error_log /var/log/nginx/urlshortener_error.log;
   access_log /var/log/nginx/urlshortener_access.log;
}

```
* Test a redirection (Ex: http://urlshortener.dev/app_dev.php/r/youpi)
