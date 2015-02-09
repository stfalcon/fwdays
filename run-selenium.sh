echo '' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "memory_limit=4096M" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
export ADDITIONAL_PATH=`php -i | grep -F --color=never 'Scan this dir for additional .ini files'`
echo 'xdebug.max_nesting_level=9999' | sudo tee ${ADDITIONAL_PATH:42}/symfony2.ini
export MINK_EXTENSION_PARAMS='base_url=http://fwdays.dev/app_test.php'
app/console doctrine:database:create --env=test > /dev/null
app/console doctrine:schema:create --env=test > /dev/null
app/console doctrine:fixtures:load --no-interaction --env=test > /dev/null
app/console cache:warmup --env=test > /dev/null
sh -e /etc/init.d/xvfb start
export DISPLAY=:99
curl http://selenium-release.storage.googleapis.com/2.41/selenium-server-standalone-2.41.0.jar > selenium.jar
java -jar selenium.jar > /dev/null &
sleep 5
