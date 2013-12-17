<?php

use Illuminate\Support\Facades\Event;
use GovTribe\LaravelKinvey\Facades\Kinvey;

/*
|--------------------------------------------------------------------------
| Package Events
|--------------------------------------------------------------------------
|
*/

//Store the Kinvey auth token in the user's session, and clear it on logout.
Event::listen('auth.login', function($user)
{
	Session::put('kinvey', $user->_kmd['authtoken']);
});

Event::listen('auth.logout', function($user)
{
	Session::forget('kinvey');
});
