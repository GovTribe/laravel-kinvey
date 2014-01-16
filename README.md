[![Build Status](https://travis-ci.org/GovTribe/laravel-kinvey.png?branch=master)](https://travis-ci.org/GovTribe/laravel-kinvey)
[![Coverage Status](https://coveralls.io/repos/GovTribe/laravel-kinvey/badge.png?branch=master)](https://coveralls.io/r/GovTribe/laravel-kinvey?branch=master)

# laravel-kinvey

This package provides integration between Kinvey's great back end as a service platform and Laravel 4. It's based on version 2 of their [REST API](http://devcenter.kinvey.com/rest/guides/getting-started).

[BaaS](http://en.wikipedia.org/wiki/Backend_as_a_service) providers like Kinvey can serve as a one stop shop for infrastructure. You give up a little in flexibility, but gain the simplicity of having one service provide several features across your app. I tailored this specifically to Laravel because I think that to get the most value you can out of a service like Kinvey it needs to be tied closely to the framework you use.

- [Install](#installation)
- [Configure](#configure)
- [Use](#use)

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

Add these lines to the 'providers' array in your 'app/config/app.php' file:
```
'GovTribe\LaravelKinvey\LaravelKinveyServiceProvider',
'GovTribe\LaravelKinvey\LaravelKinveyAuthServiceProvider',
```

In the same file, add this line to the 'aliases' array:
```
'Kinvey' => 'GovTribe\LaravelKinvey\Facades\Kinvey',
```

In your 'app/config/database.php' file, change the default connection name to 'kinvey':
```
'default' => 'kinvey',
```

In your 'app/config/auth' file, change the driver to 'eloquent':
```
'driver' => 'eloquent'
```

In the same file, change the authentication model to 'GovTribe\LaravelKinvey\Database\Eloquent\User' (or use your own model that extends this one):
```
'model' => 'GovTribe\LaravelKinvey\Database\Eloquent\User',
```

Finally, publish the package's configuration:
```
$ php artisan config:publish govtribe/laravel-kinvey
```

## Configure
You'll now have a blank configuration file under app/config/packages/govtribe/laravel-kinvey that will look something like this:
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

	/*
	| -----------------------------------------------------------------------------
	| Settings
	| -----------------------------------------------------------------------------
	|
	| Control the default authentication mode, logging etc.
	|
	*/

	'defaultAuthMode' => 'app',
	'logging' => false,
);
```
### Configuration Options

#### appName, appKey, appSecret and masterSecret
You'll need to add your application's name, key, secret and master secret. All of these are available via your application's [console](https://console.kinvey.com).

#### defaultAuthMode
You can set the default auth mode to either 'app' or 'admin'. If you use 'app', all requests wil default to using the using the Kinvey app secret. If you use admin, the default will be the Kinvey master secret. Regardless, you can change the auth mode on the fly with the client:

```php
Kinvey::setAuthMode('admin'); // master secret
Kinvey::setAuthMode('app'); // app secret
```

#### logging
Setting this to true will tell the client to log detailed information to Laravel's log system. This is mainly useful for debugging.

# Use
The Eloquent, Database, and Auth components should map closely to the existing [Laravel documentation](http://laravel.com/docs). All of the usage examples should be documented in the package's test coverage.
