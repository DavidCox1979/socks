@ECHO OFF
php C:/wamp/bin/php/php5.3.0/phpunit --verbose --bootstrap F:\dev\socks\application\bootstrap-tests.php F:\dev\socks\library\PhpStats\%*
REM %*