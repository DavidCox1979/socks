@ECHO OFF
php C:/wamp/bin/php/php5.3.0/phpunit --testdox-html=E:\dev\socks-story.html --verbose --bootstrap E:\dev\socks\application\bootstrap-tests.php E:\dev\socks\library\PhpStats\%*
REM %*