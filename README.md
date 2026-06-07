# proxy
Forward http proxy using PHP's cURL APIs

https://www.php.net/manual/en/book.curl.php

```shell
docker rm -f proxy-9000; \
docker run \
--name proxy-9000 \
--log-driver local \
--restart unless-stopped \
-dit \
-p 9000:8000 \
-v ~/Documents/proxy:/usr/src/app \
-w /usr/src/app \
php:5-alpine \
php -S 0.0.0.0:8000
```
