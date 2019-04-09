FROM gitlab.stfalcon.com:4567/stfalcon/fwdays/fwdays-dev:v2
ENV SONAR_VERSION 3.3.0.1492
RUN sudo su && cd / && \
    sudo curl -SLO https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-$SONAR_VERSION-linux.zip && \
    sudo unzip /sonar-scanner-cli-$SONAR_VERSION-linux.zip -d / && \
    sudo cp -R /sonar-scanner-$SONAR_VERSION-linux/* / && \
    sudo rm /sonar-scanner-cli-$SONAR_VERSION-linux.zip && \
    sudo rm -rf /sonar-scanner-$SONAR_VERSION-linux
