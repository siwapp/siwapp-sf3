# Siwapp

[![Latest Version](https://img.shields.io/github/release/siwapp/siwapp-sf3.svg?style=flat-square)](https://github.com/siwapp/siwapp-sf3/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/siwapp/siwapp-sf3.svg?style=flat-square)](https://travis-ci.org/siwapp/siwapp-sf3)

**Online Invoice Management**

## Installation

See [here](https://github.com/siwapp/siwapp-sf3/wiki/Installation).

### Upgrading from v0.4.x

Replace the DB_* values with the one of your old database and then run:

    $ php bin/console siwapp:upgrade-db:0.4-1.0 DB_DRIVER DB_USER DB_PASSWORD DB_NAME


### Loading demo data

    $ php bin/console doctrine:fixtures:load

### Overriding templates

See [here](https://github.com/siwapp/siwapp-sf3/wiki/The-templates).

### Automating recurring invoices generation

Just add a cronjob that runs `php bin/console siwapp:recurring:generate-pending`

### Interface language

To have the siwapp interface in another language you will need the php-intl extension installed.

Visit your profile page, change your locale and then log out. When you log back in the interface language should be switched.

Siwapp is translated to Spanish, Greek, and Romanian for now, feel free to contribute more translations!
