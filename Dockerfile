FROM php:8.3.0-cli-alpine3.19

RUN mkdir /app

VOLUME ["/app"]
WORKDIR /app