<?php
$serviceBuilder = array(

	/*
	| -----------------------------------------------------------------------------
	| Kinvey REST API Service Builder
	| -----------------------------------------------------------------------------
	*/

	'services' => array(
		'abstract_client' => array(
			'params' => array(
				'appName'			=> $this->app['config']['kinvey::appName'],
				'baseURL'    		=> $this->app['config']['kinvey::hostEndpoint'],
				'appKey' 	 		=> $this->app['config']['kinvey::appKey'],
				'appSecret'	 		=> $this->app['config']['kinvey::appSecret'],
				'masterSecret'		=> $this->app['config']['kinvey::masterSecret'],
				'version'			=> $this->app['config']['kinvey::version'],
				'defaultAuthMode'	=> $this->app['config']['kinvey::defaultAuthMode'],
				'logging'			=> $this->app['config']['kinvey::logging'],
			)
		),
		'KinveyClient' => array(
			'extends' => 'abstract_client',
			'class'   => 'GovTribe\LaravelKinvey\Client\KinveyClient',
		),
	),
);