<?php

namespace filsh\yii2\oauth2server\grants;

use \OAuth2\Storage\ClientCredentialsInterface;
use \OAuth2\Storage\UserCredentialsInterface;

class UserAuthCredentials extends \OAuth2\ClientAssertionType\HttpBasic implements \OAuth2\GrantType\GrantTypeInterface
{
    protected $userStorage;
    
    public function __construct(UserCredentialsInterface $userStorage, ClientCredentialsInterface $storage, array $config = array())
    {
        $this->userStorage = $userStorage;
        parent::__construct($storage, $config);
    }
    
    public function getQuerystringIdentifier()
    {
        return 'user_authkey_credentials';
    }
    
    public function createAccessToken(\OAuth2\ResponseType\AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }
    
    public function getUserId()
    {
        return $this->userInfo['user_id'];
    }

    public function getScope()
    {
        return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : null;
    }

    public function validateRequest(\OAuth2\RequestInterface $request, \OAuth2\ResponseInterface $response)
    {
        if (!$request->request('authkey') || !$request->request('username')) {
            $response->setError(400, 'invalid_request', 'Missing parameters: "authkey" and "username" required');
            return null;
        }

        if (!$this->userStorage->findIdentityByAccessToken($request->request('authkey'))) {
            $response->setError(401, 'invalid_grant', 'Invalid user authkey');
            return null;
        }

        $userInfo = $this->userStorage->getUserDetails($request->request('username'));

        if (empty($userInfo)) {
            $response->setError(400, 'invalid_grant', 'Unable to retrieve user information');
            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException('you must set the user_id on the array returned by getUserDetails');
        }

        $this->userInfo = $userInfo;

        return parent::validateRequest($request, $response);
    }
}