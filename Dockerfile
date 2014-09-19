FROM jekakm/frameworksdays_web
ADD . /code
WORKDIR /code
RUN curl -s https://getcomposer.org/installer | php
