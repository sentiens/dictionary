## Dictionary

Test your russian/english with symfony3, angularjs and tdd. Example app.

![Code coverage](/coverage.png)

### Requirements
- php7
- sqlite

###  Installation
	$ git clone https://github.com/tonknaf/dictionary.git
	$ composer install
	$ php ./bin/console doctrine:database:create
	$ php ./bin/console doctrine:schema:create
	$ php ./bin/console doctrine:fixtures:load
	$ ( cd web/angular ; npm install )

### Run server
	$ php bin/console server:run
Open http://127.0.0.1:8000 in browser

### Run backend tests
	$ ./vendor/bin/phpunit

### Run angular unit tests
	$ ( cd web/angular ; npm test )