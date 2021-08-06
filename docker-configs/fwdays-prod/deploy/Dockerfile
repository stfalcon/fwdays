FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-prod:v2.6
COPY . /app
WORKDIR /app
RUN unxz geodata/GeoLiteCity.dat.xz
RUN composer.phar install --optimize-autoloader
RUN php bin/console assets:install public --env=prod
RUN npm install
RUN npm run gulp-prod
RUN echo > /app/.env
RUN chown -R www-data:www-data /app/var /app/public
