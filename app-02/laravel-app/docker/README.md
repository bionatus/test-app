Dev Environment  (docker  )
=========
### Installation


1. Install Docker: [Mac](https://docs.docker.com/docker-for-mac/install/) | [Windows](https://docs.docker.com/docker-for-windows/install/)

2. Install Docker Compose: [Guide](https://docs.docker.com/compose/install/)

### Installation: 
Run the following command inside the docker folder
```bash
docker-compose up -d
```

### Usage
1. Create an .env file in the root of the project:

```bash
APP_NAME="Bluon API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://local.bluonenergy.com
APP_API_KEY=
LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REGISTRATION_REDIRECT=http://local.bluonenergy.com?user_registered=true

MAIL_DRIVER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```
2. Load the database:

 a. Ask to the DevOps person for a database dump.

 b. Import the database in your local environment like this:
```bash
mysql -h 127.0.0.1 -u homestead -p homestead < bluon_api.sql
```
3. Update your host file adding this line: [Mac](https://setapp.com/how-to/edit-mac-hosts-file) | [Windows](https://docs.rackspace.com/support/how-to/modify-your-hosts-file/)
```bash
127.0.0.1 local.bluonenergy.com
```
4. Run init commands:

Inside the docker folder run the following commands:

Install packages via composer
```bash
docker-compose run --rm composer install
```

Run migrations
```bash
docker-compose run --rm artisan migrate
```

4. Open the app http://local.bluonenergy.com

#### composer

Inside the docker folder you can run composer commands like this:

```bash
docker-compose run --rm composer
```

####  artisan
Inside the docker folder you can run artisan commands like this:


```bash
docker-compose run --rm artisan
```


####  mailhog

It uses MailHog as the default application for testing email sending and general SMTP work during local development.
When you send an email in your development environment you can check it here

http://local.bluonenergy.com:8025