# syntax=docker/dockerfile:1.19.0

FROM postgres:18-alpine3.22 AS main

ENV TZ=America/Sao_Paulo PGTZ=America/Sao_Paulo

RUN set -eux;\
    apk update;\
    apk add --no-cache tzdata nano ca-certificates;\
    apk add --no-cache postgresql-contrib postgis;\
    ln -snf /usr/share/zoneinfo/"${TZ}" /etc/localtime;\
    echo "${TZ}" > /etc/timezone;\
    update-ca-certificates;\
    rm -rf /var/cache/apk/*;
