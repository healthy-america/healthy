##  Healthy - Magento 2.4.5-p1 

### Requirements

Software dependencies | Version 
--- | --- 
Composer | 2.2
Elasticsearch | 7.17
OpenSearch | 1.2
MariaDB | 10.4
MySQL | 8.0
PHP | 8.1
RabbitMQ | 3.9
Redis | 6.2
Varniz | 7.0
nginx | 1.18
AWS Aurora (MySQL)	| 8.0
AWS S3 | 
AWS MQ | 3.9.13
AWS ElastiCache | Redis 6
Elasticsearch de AWS | 7.9
AWS OpenSearch | 1.2

### Theme - Claue v2.1.9

### Install project

1. Clone repository
```bash
git clone http://git.allers.com.co:2940/eMage/healthy.git
```

2. Enter the project directory
```bash
cd yalitech
```

3. Install dependencies
```bash
composer install
```

5. Import database if exists
```bash
mysql -uroot -proot magento_healthy < local_initial_magento_healthy.sql  
```

6. Enable configuration files
```bash
cp app/etc/env.php.sample app/etc/env.php
cp app/etc/config.php.sample app/etc/config.php
```
