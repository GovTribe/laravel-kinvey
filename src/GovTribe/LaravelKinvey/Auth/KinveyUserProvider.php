<?php namespace GovTribe\LaravelKinvey\Auth;

use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException;
use GovTribe\LaravelKinvey\Client\KinveyClient;
use GovTribe\LaravelKinvey\Database\Connection;

class KinveyUserProvider implements UserProviderInterface {

    protected $connection;
    protected $kinvey;
    protected $modelClassName;
    

    public function __construct(Connection $connection, KinveyClient $kinvey, $modelClassName)
    {
        $this->kinvey = $kinvey;
        $this->connection = $connection;
        $this->modelClassName = $modelClassName;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        $this->kinvey->setAuthMode('admin');

        $result = $this->connection->collection('user')->where('_id', $identifier)->first();

        $this->kinvey->setAuthMode('app');

        $user = new $this->modelClassName;

        $user->setRawAttributes($result);

        return $user;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $this->kinvey->setAuthMode('admin');

        $result = $this->connection->collection('user')->where('username', reset($credentials))->first();

        $this->kinvey->setAuthMode('app');

        if ($result)
        {
            $user = new $this->modelClassName;
            $user->setRawAttributes($result);

            return $user;
        }
        else return null;
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByToken($identifier, $token)
    {
        //
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Auth\UserInterface  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserInterface $user, $token)
    {
        //
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Auth\UserInterface  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        try
        {
            $result = $this->kinvey->login($credentials);
            $user->_kmd = $result['_kmd'];
        }
        catch (KinveyResponseException $e)
        {
            if ($e->getResponse()->getStatusCode() === 401)
            {
                return false;
            }
            else
            {
                throw $e;
            }
        }

        return true;
    }
}