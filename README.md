# Simple PHP SSO integration for Laravel

[![Latest Stable Version](https://poser.pugx.org/zefy/laravel-sso/v/stable)](https://packagist.org/packages/zefy/laravel-sso)
[![Total Downloads](https://poser.pugx.org/zefy/laravel-sso/downloads)](https://packagist.org/packages/zefy/laravel-sso)
[![Latest Unstable Version](https://poser.pugx.org/zefy/laravel-sso/v/unstable)](https://packagist.org/packages/zefy/laravel-sso)
[![License](https://poser.pugx.org/zefy/laravel-sso/license)](https://packagist.org/packages/zefy/laravel-sso)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zefy/laravel-sso/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zefy/laravel-sso/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/zefy/laravel-sso/badges/build.png?b=master)](https://scrutinizer-ci.com/g/zefy/laravel-sso/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/zefy/laravel-sso/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>


This package based on [Simple PHP SSO skeleton](https://github.com/zefy/php-simple-sso) package and made suitable for Laravel framework.
### Requirements
* Laravel 5.5+
* PHP 7.1+

### Words meanings
* ***SSO*** - Single Sign-On.
* ***Server*** - page which works as SSO server, handles authentications, stores all sessions data.
* ***Broker*** - your page which is used visited by clients/users.
* ***Client/User*** - your every visitor.

### How it works?
Client visits Broker and unique token is generated. When new token is generated we need to attach Client session to his session in Broker so he will be redirected to Server and back to Broker at this moment new session in Server will be created and associated with Client session in Broker's page. When Client visits other Broker same steps will be done except that when Client will be redirected to Server he already use his old session and same session id which associated with Broker#1.

# Installation
### Server
Install this package using composer.
```shell
$ composer require zefy/laravel-sso
```


Copy config file to Laravel project `config/` folder.
```shell
$ php artisan vendor:publish --provider="Zefy\LaravelSSO\SSOServiceProvider"
```


Create table where all brokers will be saved.
```shell
$ php artisan migrate --path=vendor/zefy/laravel-sso/database/migrations
```


Edit your `app/Http/Kernel.php` by removing throttle middleware and adding sessions middleware to `api` middlewares array.
This is necessary because we need sessions to work in API routes and throttle middleware can block connections which we need.
```php
'api' => [
    'bindings',
    \Illuminate\Session\Middleware\StartSession::class,
],
```


Now you should create brokers.
You can create new broker using following Artisan CLI command:
```shell
$ php artisan sso:broker:create {name}
```

----------

### Broker
Install this package using composer.
```shell
$ composer require zefy/laravel-sso
```


Copy config file to Laravel project `config/` folder.
```shell
$ php artisan vendor:publish --provider="Zefy\LaravelSSO\SSOServiceProvider"
```


Change `type` value in `config/laravel-sso.php` file from `server`
 to `broker`.

 

Set 3 new options in your `.env` file:
```shell
SSO_SERVER_URL=
SSO_BROKER_NAME=
SSO_BROKER_SECRET=
```
`SSO_SERVER_URL` is your server's http url without trailing slash. `SSO_BROKER_NAME` and `SSO_BROKER_SECRET` must be data which exists in your server's `brokers` table.



Edit your `app/Http/Kernel.php` by adding `\Zefy\LaravelSSO\Middleware\SSOAutoLogin::class` middleware to `web` middleware group. It should look like this:
```php
protected $middlewareGroups = [
        'web' => [
            ...
            \Zefy\LaravelSSO\Middleware\SSOAutoLogin::class,
        ],

        'api' => [
            ...
        ],
    ];
```



Last but not least, you need to edit `app/Http/Controllers/Auth/LoginController.php`. You should add two functions into `LoginController` class which will authenticate your client through SSO server but not your Broker page.
```php
protected function attemptLogin(Request $request)
{
    $broker = new \Zefy\LaravelSSO\LaravelSSOBroker;
    
    $credentials = $this->credentials($request);
    return $broker->login($credentials[$this->username()], $credentials['password']);
}

public function logout(Request $request)
{
    $broker = new \Zefy\LaravelSSO\LaravelSSOBroker;
    
    $broker->logout();
    
    $this->guard()->logout();
    
    $request->session()->invalidate();
    
    return redirect('/');
}
```


That's all. For other Broker pages you should repeat everything from the beginning just changing your Broker name and secret in configuration file.




Example `.env` options:
```shell
SSO_SERVER_URL=https://server.test
SSO_BROKER_NAME=site1
SSO_BROKER_SECRET=892asjdajsdksja74jh38kljk2929023
```