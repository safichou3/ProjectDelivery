# Project Delivery Guide

Follow these steps to set up and run the project:

## Setup Environment
Copy example env file and update values (DB, Mail, JWT, etc.):
cp .env.example .env

## Create directory for keys
mkdir -p config/jwt

## Generate private key
openssl genrsa -out config/jwt/private.pem -aes256 4096

## Generate public key
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem

## Create Database and run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

## Composer Install
composer install
symfony server:start

## Install JS packages and start React server:
npm install
npm run dev

## For production:
npm run build


