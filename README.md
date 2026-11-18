# proxy
Forward http proxy using PHP's cURL APIs

https://www.php.net/manual/en/book.curl.php

```shell
docker rm -f proxy-9000; \
docker run \
--name proxy-9000 \
--network net \
--log-driver local \
-dit \
-p 9000:8000 \
-v ~/Documents/proxy:/usr/src/app \
-w /usr/src/app \
php:5-alpine \
/bin/sh -c "php -S 0.0.0.0:8000 2>/dev/null"


# nginx
docker rm -f nginx-8001; \
docker run \
--name nginx-8001 \
--network net \
--log-driver local \
-dit \
-p 8001:80 \
-v ~/Documents/proxy:/usr/share/nginx/html:ro \
-v ~/Documents/proxy/nginx.conf:/etc/nginx/nginx.conf:ro \
nginx:alpine


# oha
docker rm oha-8002; \
docker run \
--name oha-8002 \
--network net \
--log-driver local \
-dit \
alpine \
/bin/sh

wget -O oha https://github.com/hatoo/oha/releases/download/v1.17.0/oha-linux-arm64 && chmod +x oha && mv oha /usr/local/bin/oha && which oha && oha --version

wget -O- http://nginx-8001
oha -n 500000 -m GET "http://nginx-8001"

wget -O- http://proxy-9000:8000/proxy?url=http://nginx-8001
oha -n 500000 -m GET "http://proxy-9000:8000/proxy?url=http://nginx-8001"



```
