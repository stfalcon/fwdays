FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-prod:v1.3
COPY . /app
WORKDIR /app
RUN cp app/config/parameters.yml.stag app/config/parameters.yml
RUN unxz geodata/GeoLiteCity.dat.xz
RUN composer.phar install --no-dev --optimize-autoloader
RUN php bin/console assets:install web --env=prod
RUN php bin/console assetic:dump --env=prod
RUN npm install
RUN npm run gulp-prod
RUN chown -R www-data:www-data /app/var /app/web
