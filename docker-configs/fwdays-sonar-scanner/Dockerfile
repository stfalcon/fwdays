FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-dev:v3
USER root
ENV SONAR_VERSION 3.3.0.1492
RUN cd / && \
    curl -SLO https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-$SONAR_VERSION-linux.zip && \
    unzip /sonar-scanner-cli-$SONAR_VERSION-linux.zip -d / && \
    cp -R /sonar-scanner-$SONAR_VERSION-linux/* / && \
    rm /sonar-scanner-cli-$SONAR_VERSION-linux.zip && \
    rm -rf /sonar-scanner-$SONAR_VERSION-linux
