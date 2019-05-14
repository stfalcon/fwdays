FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-prod:v21
COPY . /app
WORKDIR /app
RUN cp app/config/parameters.ini.stag app/config/parameters.ini
RUN sed -i "s/database_password =.*/database_password = qwerty/" app/config/parameters.ini
RUN unxz geodata/GeoLiteCity.dat.xz
RUN composer.phar install --no-dev --optimize-autoloader
RUN php app/console assets:install web --symlink
RUN php app/console assetic:dump
RUN npm install
RUN npm run gulp-prod
RUN chown -R www-data:www-data /app/app/cache /app/app/logs /app/web
