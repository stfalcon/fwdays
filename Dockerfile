FROM jekakm/frameworksdays_web
ADD . /code
WORKDIR /code
USER www-data
RUN curl -s https://getcomposer.org/installer | php
RUN sed -i s/'database_host     = localhost'/'database_host     = db_1'/ app/config/parameters.ini
