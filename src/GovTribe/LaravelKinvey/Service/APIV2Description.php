<?php
$APIV2Description = array(

	/*
	| -----------------------------------------------------------------------------
	| Kinvey REST API V2 Service Description
	| -----------------------------------------------------------------------------
	*/

	'name' => 'Kinvey REST API',
	'apiVersion' => '2',
	'operations' => array(
		'authOperation' => array(
			'parameters' => array(
				'appKey' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Kinvey app key',
					'required' => true,
				),
				'authHeader' => array(
					'location' => 'header',
					'type' => 'string',
					'description' => 'Kinvey basic authorization header',
					'required' => true,
					'sentAs' => 'Authorization',
				),
				'username' => array(
					'type' => 'string',
					'description' => 'User name',
				),
				'password' => array(
					'type' => 'string',
					'description' => 'Password',
				),
				'token' => array(
					'type' => 'string',
					'description' => 'Session token',
				),
			),
		),
		'pingAuth' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/getting-started#handshake',
			'httpMethod' => 'GET',
			'uri' => '/appdata/{appKey}',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'app'
				),
			),
		),
		'pingAnon' => array(
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/getting-started#handshake',
			'httpMethod' => 'GET',
			'uri' => '/appdata/',
		),
		'signUp' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#signup',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'app'
				),
				'data' => array(
					'location' => 'body',
					'type' => 'array',
					'description' => 'Request body',
					'required' => true,
					'sentAs' => 'body',
					'filters' => array(
						'json_encode',
					),
				),
			),
		),
		'retrieveUser' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#retrieve',
			'httpMethod' => 'GET',
			'uri' => '/user/{appKey}/{id}',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'user'
				),
				'id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'User id',
					'required' => true,
				),
			),
		),
		'retrieveUserAsAdmin' => array(
			'extends' => 'retrieveUser',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'admin'
				),
			),
		),
		'updateUser' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#signup',
			'httpMethod' => 'PUT',
			'uri' => '/user/{appKey}/{id}',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'user'
				),
				'id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'User id',
					'required' => true,
				),
				'data' => array(
					'location' => 'body',
					'type' => 'array',
					'description' => 'Request body',
					'required' => true,
					'sentAs' => 'body',
					'filters' => array(
						'json_encode',
					),
				),
			),
		),
		'updateUserAsAdmin' => array(
			'extends' => 'updateUser',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'admin'
				),
			),
		),
		'deleteUser' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#delete',
			'httpMethod' => 'DELETE',
			'uri' => '/user/{appKey}/{id}',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'user'
				),
				'id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'User id',
					'required' => true,
				),
				'contentType' => array(
					'location' => 'header',
					'type' => 'string',
					'sentAs' => 'Content-Type',
					'default' => 'application/x-www-form-urlencoded',
					'required' => true,
				),
			),
		),
		'deleteUserAsAdmin' => array(
			'extends' => 'deleteUser',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'admin'
				),
				'hard' => array(
					'location' => 'query',
					'type' => 'string',
					'default' => 'false',
				),
			),
		),
		'login' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#login',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/login',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => true,
					'default' => 'app'
				),
				'data' => array(
					'location' => 'body',
					'type' => 'array',
					'description' => 'Request body',
					'required' => true,
					'sentAs' => 'body',
					'filters' => array(
						'json_encode',
					),
				),
			),
		),
		'logout' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#logout',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/_logout',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'static' => false,
					'default' => 'session'
				),
				'contentType' => array(
					'location' => 'header',
					'type' => 'string',
					'sentAs' => 'Content-Type',
					'default' => 'application/x-www-form-urlencoded',
					'required' => true,
				),
			),
		),
		'me' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#login',
			'httpMethod' => 'GET',
			'uri' => '/user/{appKey}/_me',
			'parameters' => array(
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'required' => true,
					'default' => 'session'
				),
			),
		),
	),
);