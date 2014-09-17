FROM jekakm/frameworksdays_web
ADD . /code
WORKDIR /code
USER www-data
RUN curl -s https://getcomposer.org/installer | php
