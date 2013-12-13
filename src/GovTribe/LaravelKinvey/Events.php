<?php

use Illuminate\Support\Facades\Event;
use GovTribe\LaravelKinvey\Facades\Kinvey;

/*
|--------------------------------------------------------------------------
| Package Events
|--------------------------------------------------------------------------
|
*/

// Listen to restore events so the Kinvey client's restore command can be called.
Event::listen('eloquent.restored:*', function($model)
{
	if (is_subclass_of($model, 'GovTribe\LaravelKinvey\Eloquent\User') || $model instanceof GovTribe\LaravelKinvey\Eloquent\User)
	{
		$response = Kinvey::restore(array(
			'_id' => $model->_id,
			'authMode' => 'admin',
		));
	}
});

// Store the Kinvey auth token in the user's session, and clear it on logout.
Event::listen('auth.login', function($user)
{
	Session::put('kinvey', $user->_kmd['authtoken']);
});

Event::listen('auth.logout', function($user)
{
	Session::forget('kinvey');
});
