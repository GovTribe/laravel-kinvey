<?php
$builder = array(

	/*
	| -----------------------------------------------------------------------------
	| Kinvey REST API Service Builder
	| -----------------------------------------------------------------------------
	*/

	'services' => array(
		'abstract_client' => array(
			'params' => array(
				'appName'		=> $this->app['config']['laravel-kinvey::appName'],
				'baseURL'    	=> $this->app['config']['laravel-kinvey::hostEndpoint'],
				'appKey' 	 	=> $this->app['config']['laravel-kinvey::appKey'],
				'appSecret'	 	=> $this->app['config']['laravel-kinvey::appSecret'],
				'masterSecret'	=> $this->app['config']['laravel-kinvey::masterSecret'],
				'version'		=> $this->app['config']['laravel-kinvey::version'],

			)
		),
		'KinveyClient' => array(
			'extends' => 'abstract_client',
			'class'   => 'GovTribe\LaravelKinvey\KinveyClient',
		),
	),

);