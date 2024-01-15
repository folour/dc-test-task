### Installation

```bash
docker build -t dc-backend .

docker run -it --rm -v .:/app dc-backend ./composer install

docker run -it --rm -v .:/app dc-backend bin/console parser:run
```