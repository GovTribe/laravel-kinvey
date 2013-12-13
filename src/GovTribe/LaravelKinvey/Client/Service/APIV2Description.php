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
					'default'  => 'app'
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
		'createGroup' => array(
			'extends' => 'authOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/users#usergroupscreate',
			'httpMethod' => 'POST',
			'uri' => '/group/{appKey}/',
			'parameters' => array(
				'group' => array(
					'location' => 'json',
					'type' => 'object',
					'instanceOf' => 'KinveyUserGroup',
					'description' => 'Group Object',
					'required' => true,
				),
			),

		),

		//Entities
		'createEntity' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#create',
			'httpMethod' => 'POST',
			'parameters' => array(
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
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
					'required' => false,
				),
			),
		),
		'updateEntity' => array(
			'extends' => 'entityOperation',
			'documentationUrl' => 'http://devcenter.kinvey.com/rest/guides/datastore#Saving',
			'httpMethod' => 'PUT',
			'parameters' => array(
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
				'_id' => array(
					'location' => 'uri',
					'type' => 'string',
					'description' => 'Entity ID',
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
	),
);