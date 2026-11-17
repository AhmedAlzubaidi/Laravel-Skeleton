FROM postgres:18

RUN apt-get update && apt-get upgrade -y \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && rm /usr/local/bin/gosu

# The entrypoint only needs gosu when starting as root.
USER postgres
