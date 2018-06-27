# Bet 360
This is a sample Laravel project that has Laravel API consumed by a client made with Vue.js
# Installation

Copy **.env.example** to **.env** and update (at least) **APP_URL** and **DB_*** with your own values. The API can be consumed by any other REST clients.

Install dependencies:
```
composer install
```
```
npm i
```

Build 
```
npm prod
```

Run DB migrations
```
php artisan migrate --seed
```

Add test data
```
php artisan db:seed
```

Create REST API clients
```
php artisan passport:install --force
```

# App Client
Go to **/login** to log in with an existing user or **/register** to register a new user. 

Demo user (from the test data):
```
demo@demo.com | demo
```

# Tests
Run tests with
```
composer test
```

# External API Access
To consume API by an external client, please log in to app and go to API tab. Click on ***Create New Token*** to generate a new API token.

Copy token and use it with your REST client. Add **"Bearer {API_TOKEN}"** to the header of your requests. 