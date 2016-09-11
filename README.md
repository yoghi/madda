# madda

[![Author](http://img.shields.io/badge/author-@yoghi-blue.svg?style=flat)](https://twitter.com/yoghi)
[![Code Climate](https://codeclimate.com/github/yoghi/madda/badges/gpa.svg)](https://codeclimate.com/github/yoghi/madda)
[![Code Climate Coverage](https://codeclimate.com/github/yoghi/madda/badges/coverage.svg)](https://codeclimate.com/github/yoghi/madda/coverage)
[![Coverage Status](https://coveralls.io/repos/github/yoghi/madda/badge.svg?branch=master)](https://coveralls.io/github/yoghi/madda?branch=master)
[![Codecov](https://codecov.io/gh/yoghi/madda/branch/master/graph/badge.svg)](https://codecov.io/gh/yoghi/madda)
[![VersionEye](https://www.versioneye.com/user/projects/5759ce3f7757a0003bd4bd0f/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5759ce3f7757a0003bd4bd0f)
[![Build Status](https://travis-ci.org/yoghi/madda.svg?branch=master)](https://travis-ci.org/yoghi/madda)
[![license](https://img.shields.io/aur/license/yaourt.svg?maxAge=2592000&style=flat)](https://github.com/yoghi/madda/blob/master/LICENSE)

Model And Domain Driven Architecture

## Requirements

 * Symfony 3.0+
 * at least php 5.6.8
 * justinrainbow/json-schema 1.6 (need from php-raml-parser -> [blocked by](https://github.com/alecsammon/php-raml-parser/pull/104))

## Installation

```
composer install yoghi/madda
```

## Todo

 - [X] [Packagist](https://packagist.org)
 - [X] [Codecov](https://codecov.io/)
 - [X] [Coveralls](https://coveralls.io/github/yoghi/madda)
 - [X] [VersionEye](https://www.versioneye.com) -> controllo degli aggiornamenti delle dipendeze
 - [ ] [Scrutinizer-ci](https://scrutinizer-ci.com)
 - [X] badge licenza
 - [ ] git tag -a 1.0.0
 - [ ] changelog / release workflow
 - [ ] aggiungere la capacita di applicare php-cs-fixer alla fine di una generazione
 - [ ] wiki
 - [ ] faq
 - [ ] [raml](http://raml.org) to controller
 - [ ] DomainDrivenDesign yaml descriptor to pojo and structural element
 - [ ] integration with existing bus system (for event propagation)
    - [ ] [Symfony](http://symfony.com/)
    - [ ] [Prooph](https://github.com/prooph)
    - [ ] [SimpleBus/MessageBus](https://github.com/SimpleBus/MessageBus)
    - [ ] [Broadway](https://github.com/qandidate-labs/broadway)
    - [ ] [Tactician](http://tactician.thephpleague.com/)
 - [ ] Contrib Guide

## clean code

```
phpcbf **/*.php --standard=PSR2
bin/parallel-lint --exclude app --exclude vendor .
bin/phpcs --colors -wp src --report=summary --standard=PSR2,phpcs.xml
bin/phpunit --coverage-php tests/coverage/phpunit.cov tests
bin/phpspec run --format=pretty --no-code-generation
```

## Debug

con atom si deve configurare la porta di ascolto (via cson o settings normale.)

a livello di php va installato xdebug

```
phpbrew ext install xdebug stable
```

configurato:

```
cd ~/.phpbrew/php/var/db/
```
edit del file **xdebug.ini** sotto la riga "zend_extension=...."

~~~
xdebug.remote_enable=1
xdebug.remote_host=127.0.0.1
xdebug.remote_connect_back=1    # Not safe for production servers
xdebug.remote_port=9000
xdebug.remote_handler=dbgp
xdebug.remote_mode=req
xdebug.remote_autostart=true
~~~

tenerlo abilitato significa rallentare, quindi conviene attivarlo solo quando serve.

```
phpbrew ext disable xdebug
phpbrew ext enable xdebug
```

info :

```
phpbrew ext show xdebug
```

## Contributing

Pull requests are welcome. Please see our CONTRIBUTING guide.

Unit and/or functional tests exist for this bundle. See the Testing documentation for a guide to running the tests.

Thanks to everyone who has contributed already.
