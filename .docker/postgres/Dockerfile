ARG POSTGRES_VERSION
FROM postgres:${POSTGRES_VERSION}

ARG POSTGRES_VERSION
ARG POSTGIS_VERSION
RUN apt update && apt install --yes postgresql-${POSTGRES_VERSION}-postgis-${POSTGIS_VERSION}
