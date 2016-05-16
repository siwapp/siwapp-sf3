Siwapp
======

## Installation

    $ git clone git@github.com:ParisLiakos/siwapp-sf3.git
    $ cd siwapp-sf3

The following will ask you to setup the database info, so make sure you have one ready.

    $ composer install
    $ php bin/console assetic:dump --env=prod

Make that the `var/` folder is writable by the webserver:
sudo chown www-data:www-data -R var/

or check [this](https://symfony.com/doc/current/book/installation.html#book-installation-permissions).

Thats it!
The siwapp installation should be reachable and working now.

### Creating the first user
    $ php bin/console fos:user:create admin mail@example.com 1234 --super-admin

### Loading demo data
    $ php bin/console doctrine:fixtures:load
