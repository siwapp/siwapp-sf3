# Siwapp

[![Latest Version](https://img.shields.io/github/release/siwapp/siwapp-sf3.svg?style=flat-square)](https://github.com/siwapp/siwapp-sf3/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/siwapp/siwapp-sf3.svg?style=flat-square)](https://travis-ci.org/siwapp/siwapp-sf3)

**Online Invoice Management**

## Installation

The following will ask you to setup the database info, so make sure you have one
ready.

    $ composer create-project --stability=dev siwapp/siwapp-sf3 my_siwapp
    $ php bin/console assetic:dump --env=prod
    $ php bin/console doctrine:schema:create

Creating the first (admin) user:

    $ php bin/console fos:user:create admin mail@example.com 1234 --super-admin

Make sure that the `var/` folder is writable by the webserver:

    $ sudo chown www-data:www-data -R var/

or check [this](https://symfony.com/doc/current/book/installation.html#book-installation-permissions).

Thats it!
The siwapp installation should be reachable and working now.
Check `/config.php` or `/web/config.php` to make sure that everything in your
enviroment is ok.

### Upgrading from v0.4.x

Replace the DB_* values with the one of your old database and then run:

    $ php bin/console siwapp:upgrade-db:0.4-1.0 DB_DRIVER DB_USER DB_PASSWORD DB_NAME


### Loading demo data

    $ php bin/console doctrine:fixtures:load

### Overriding templates

To override templates, eg. the invoice print one, copy
`src/Siwapp/InvoiceBundle/Resources/views/Invoice/print.html.twig` to `app/Resources/SiwappInvoiceBundle/views/Invoice/print.html.twig` and clear the
cache:

    $ php bin/console cache:clear

The above applies to [any template](https://symfony.com/doc/current/book/templating.html#overriding-bundle-templates),
(probably the print and email ones are those that you are more insterested to).

### Automating recurring invoices generation

Just add a cronjob that runs `php bin/console siwapp:recurring:generate-pending`
