# Integration between Laravel 4 and [Kinvey](http://www.kinvey.com)

This pacakge provides integration between Kinvey's great backend as a service platform and Laravel 4. It's based on version 2 of the [REST API](http://devcenter.kinvey.com/rest/guides/getting-started).

- [Install](#installation)
- [Configure](#installation)
- [Supported Endpoints](#supported-endpoints)

## Install

Add laravel-kinvey to your composer.json file:
```
"require": {
  "govtribe/laravel-kinvey": "dev-master"
}
```

Run composer update:
```
$ composer update
```

Publish the package's configuration:
```
$ php artisan config:publish govtribe/laravel-kinvey
```

## Configure
You'll now have a blank configuration file under app/config/packages/govtribe/laravel-kinvey:
```php
return array(

	/*
	| -----------------------------------------------------------------------------
	| Kinvey App Key, App Secret & Master Secret
	| -----------------------------------------------------------------------------
	|
	| These are available via your Kinvey console.
	|
	*/

	'appName' => '',
	'appKey' => '',
	'appSecret' => '',
	'masterSecret' => '',

	/*
	| -----------------------------------------------------------------------------
	| Kinvey REST API Host Endpoint & Version
	| -----------------------------------------------------------------------------
	|
	| The base endpoint and API version to use for all Kinvey requests.
	|
	*/

	'hostEndpoint' => 'https://baas.kinvey.com/',
	'version' => 2,
);
```
Add your application's name, key, secret and master secret.

## Supported Endpoints

More endpoints will be supported soon, but so far I've implemented:

 - Handshake
   - [pingAuth](http://devcenter.kinvey.com/rest/guides/getting-started#handshake)
   - [pingAnon](http://devcenter.kinvey.com/rest/guides/getting-started#handshake)
   
- User
  - [signUp]('http://devcenter.kinvey.com/rest/guides/users#signup')
  - [retrieveUser](http://devcenter.kinvey.com/rest/guides/users#retrieve)
  - [updateUser](http://devcenter.kinvey.com/rest/guides/users#update)
  - [deleteUser](http://devcenter.kinvey.com/rest/guides/users#delete)
  - [login](http://devcenter.kinvey.com/rest/guides/users#login)
  - [logout](http://devcenter.kinvey.com/rest/guides/users#logout)
  - [me](http://devcenter.kinvey.com/rest/guides/users#login)
