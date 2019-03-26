FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-prod:v2
COPY . /app
WORKDIR /app
RUN cp app/config/parameters.ini.stag app/config/parameters.ini
RUN sed -i "s/database_password =.*/database_password = qwerty/" app/config/parameters.ini
RUN curl -sS https://getcomposer.org/installer | php
RUN unxz geodata/GeoLiteCity.dat.xz
RUN php composer.phar install --no-dev --optimize-autoloader
RUN php app/console assets:install web --symlink
RUN php app/console assetic:dump
RUN php app/console lexik:translations:import
RUN npm install
RUN npm run gulp-prod
RUN chown -R www-data:www-data /app
