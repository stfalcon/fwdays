FROM jekakm/php71-core:201901222
RUN sudo apt-get update
RUN sudo apt-get install libpng-dev -y
RUN sudo apt-get remove nodejs -y
ENV NODE_VERSION 6.11.3
RUN curl -SLO https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.gz
RUN sudo tar -xvzf node-v$NODE_VERSION-linux-x64.tar.gz -C / --strip-components=1
RUN sudo npm install -g grunt
USER root
