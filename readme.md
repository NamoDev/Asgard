# Asgard
The community version of *Project Elyria*, a PHP/Laravel-based *Looking-Glass* server implementation!

### Requirements
* PHP >= 7.0.0
* OpenSSL, PDO, Mbstring, Tokenizer and XML PHP extensions
* A compatible backend database: either MySQL, PostgreSQL or Microsoft SQL Server. (SQLite should also work for those cases that you'd like to avoid having to use an external database, but that is untested)
* The Composer dependency manager

### Installation
* Of course, the first step is to `git clone` this repository into a working directory of your choice.
* Copy the `.env.example` file over to `.env`, make configuration changes in here as necessary. Be sure your database configuration is correct, it's very important!
* Run `composer install` to install the dependencies required.
* Make sure that the `bootstrap/cache` and `storage` directories are writable by the web server.
* Run `php artisan key:generate` and `php artisan migrate` to generate application keys and prime the database for usage.
* Finally, you'll need to setup a cronjob and queued worker (task runner). Refer to [this guide of setting up supervisord](https://laravel.com/docs/5.1/queues#supervisor-configuration) and [this guide of setting up the scheduler](https://laravel.com/docs/5.5/scheduling#introduction) for more information. **THE APP WILL NOT WORK WITHOUT THIS!**
* Oh, and don't forget to point your webserver to the app's public directory and create rewrites. [See this guide for more info.](https://laravel.com/docs/5.5/installation#web-server-configuration)
