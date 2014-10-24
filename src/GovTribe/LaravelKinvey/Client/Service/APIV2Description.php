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

		// Base operations
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
				'authMode' => array(
					'type' => 'string',
					'description' => 'Authentication mode',
					'required' => true,
				),
			),
		),
		'entityOperation' => array(
			'extends' => 'authOperation',
			'uri' => '/appdata/{appKey}/{collection}/{_id}',
			'parameters' => array(
				'collection' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Collection name',
					'required' => true,
				),
			),
		),

		// Handshake
		'pingAuth' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/getting-started#handshake',
			'httpMethod' => 'GET',
			'uri' => '/appdata/{appKey}',
		),
		'pingAnon' => array(
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/getting-started#handshake',
			'httpMethod' => 'GET',
			'uri' => '/appdata/',
		),

		// Users
		'login' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#login',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/login',
			'parameters' => array(
				'username' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Username',
					'required' => true,
				),
				'password' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Password',
					'required' => true,
				),
			),
		),
		'loginOAuth' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#login',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/login',
			'parameters' => array(
				'_socialIdentity' => array(
					'location' => 'json',
					'type' => 'array',
					'description' => 'Social identity',
					'required' => true,
				),
			),
		),
		'logout' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#logout',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/_logout',
			'parameters' => array(
				'contentType' => array(
					'location' => 'header',
					'type' => 'string',
					'sentAs' => 'Content-Type',
					'default' => 'application/x-www-form-urlencoded',
					'required' => true,
				),
			),
		),
		'restore' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#delete',
			'httpMethod' => 'POST',
			'uri' => '/user/{appKey}/{_id}/_restore',
			'parameters' => array(
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
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
		'me' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#login',
			'httpMethod' => 'GET',
			'uri' => '/user/{appKey}/_me',
		),
		'checkUsernameExists' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#userexists',
			'httpMethod' => 'POST',
			'uri' => '/rpc/{appKey}/check-username-exists',
			'parameters' => array(
				'username' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Username to check',
					'required' => true,
				),
			),
		),
		'resetPassword' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#passwordreset',
			'httpMethod' => 'POST',
			'uri' => '/rpc/{appKey}/{username}/user-password-reset-initiate',
			'parameters' => array(
				'username' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Username or email address',
					'required' => true,
				),
				'body' => array(
					'location' => 'body',
					'required' => true,
					'default' => "{}",
				),
			),
		),
		'verifyEmail' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#emailverification',
			'httpMethod' => 'POST',
			'uri' => '/rpc/{appKey}/{username}/user-email-verification-initiate',
			'parameters' => array(
				'username' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Username or email address',
					'required' => true,
				),
				'body' => array(
					'location' => 'body',
					'required' => true,
					'default' => "{}",
				),
			),
		),

		//Entities
		'createEntity' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#create',
			'httpMethod' => 'POST',
			'additionalParameters' => array(
				'location' => 'json',
			),
			'parameters' => array(
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
					'required' => false,
				),
				'username' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Username',
					'required' => false,
				),
				'password' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Password',
					'required' => false,
				),

			),
		),
		'updateEntity' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#Saving',
			'httpMethod' => 'PUT',
			'additionalParameters' => array(
				'location' => 'json',
			),
			'parameters' => array(
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
					'required' => false,
				),
				'username' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Username',
					'required' => false,
				),
				'query' => array(
					'location' => 'query',
					'type' => 'array',
					'description' => 'Query',
					'required' => false,
					'default' => array(),
					'filters' => array(
						'json_encode',
					),
				),
			),
		),
		'retrieveEntity' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#Fetching',
			'httpMethod' => 'GET',
			'parameters' => array(
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
					'required' => true,
				),
			),
		),
		'deleteEntity' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#Deleting',
			'httpMethod' => 'DELETE',
			'parameters' => array(
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
					'required' => true,
				),
				'contentType' => array(
					'location' => 'header',
					'type' => 'string',
					'sentAs' => 'Content-Type',
					'default' => 'application/x-www-form-urlencoded',
					'required' => true,
				),
				'hard' => array(
					'location' => 'query',
					'type' => 'string',
					'default' => 'false',
				),
			),
		),
		'remove' => array(
			'extends' => 'deleteEntity',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#Deleting',
			'httpMethod' => 'DELETE',
			'parameters' => array(
				'hard' => array(
					'location' => 'query',
					'type' => 'string',
					'default' => 'true',
				),
			),
		),
		'deleteCollection' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#deletecollection',
			'httpMethod' => 'POST',
			'uri' => '/rpc/{appKey}/remove-collection',
			'parameters' => array(
				'X-Kinvey-Delete-Entire-Collection' => array(
					'location' => 'header',
					'type' => 'string',
					'default' => 'true',
					'required' => true,
				),
				'collection' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'Name of the collection to delete',
					'required' => true,
					'sentAs' => 'collectionName',
				),
			),
		),
		'query' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#Querying',
			'httpMethod' => 'GET',
			'parameters' => array(
				'query' => array(
					'location' => 'query',
					'type' => 'array',
					'description' => 'Query',
					'required' => false,
					'default' => array(),
					'filters' => array(
						'json_encode',
					),
				),
				'limit' => array(
					'location' => 'query',
					'type' => 'integer',
					'description' => 'Limit the results returned',
					'required' => false,
				),
				'skip' => array(
					'location' => 'query',
					'type' => 'integer',
					'description' => 'Skip a number of results',
					'required' => false,
				),
				'sort' => array(
					'location' => 'query',
					'type' => 'array',
					'description' => 'Sort the returned results',
					'required' => false,
					'filters' => array(
						'json_encode',
					),
				),
				'fields' => array(
					'location' => 'query',
					'type' => 'array',
					'description' => 'Limit the returned fields.',
					'required' => false,
					'filters' => array(
						array(
							'method' => 'implode',
							'args' => array(
								',', '@value'
							),
						),
					),
				),
			),
		),
		'group' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#aggregation',
			'httpMethod' => 'POST',
			'uri' => '/appdata/{appKey}/{collection}/_group',
			'parameters' => array(
				'key' => array(
					'location' => 'json',
					'type' => 'array',
					'description' => 'An object that selects the field to group by',
					'required' => true,
				),
				'initial' => array(
					'location' => 'json',
					'type' => 'array',
					'description' => 'An object containing the initial structure of the document to be returned',
					'required' => true,
				),
				'reduce' => array(
					'location' => 'json',
					'type' => 'string',
					'description' => 'A JavaScript function that will be used to reduce down the result set',
					'required' => true,
				),
				'condition' => array(
					'location' => 'json',
					'type' => 'array',
					'description' => 'An optional filter applied to the result set before it is fed to the MapReduce operation',
					'required' => false,
				),
			),
		),
		'count' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#counting',
			'httpMethod' => 'GET',
			'uri' => '/appdata/{appKey}/{collection}/_count',
		),
	),
);
