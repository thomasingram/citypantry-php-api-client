<?php

namespace CityPantry;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client as GuzzleClient;

class ApiClient
{
    const ENDPOINT_PROD = 'https://api.citypantry.com';

    const ENDPOINT_DEV = 'http://api.citypantry.dev';

    private $userId;

    private $salt;

    private $endpoint;

    private $guzzleClient;

    /**
     * @param  Request $request
     * @param  string  $endpoint One of `ApiClient::ENDPOINT_*`.
     * @return ApiClient
     */
    public static function createClientFromRequestCookies(Request $request, $endpoint)
    {
        $userId = $request->cookies->get('userId');
        $salt = $request->cookies->get('salt');

        return new ApiClient($userId, $salt, $endpoint);
    }

    /**
     * @todo Once we have a real auth token field, use that instead of the salt.
     * @param string $endpoint One of `ApiClient::ENDPOINT_*`.
     */
    public function __construct($userId, $salt, $endpoint)
    {
        $this->userId = $userId;
        $this->salt = $salt;
        $this->endpoint = $endpoint;
        $this->guzzleClient = new GuzzleClient();
    }

    public function isLoggedIn()
    {
        return $this->userId && $this->salt;
    }

    public function getAuthenticatedUser()
    {
        return $this->request('GET', '/users/get-authenticated-user');
    }

    public function request($method, $path)
    {
        $method = strtoupper($method);
        $path = '/' . ltrim($path, '/');
        $url = $this->endpoint . $path;

        $request = $this->guzzleClient->createRequest($method, $url, [
            'headers' => [
                'X-CityPantry-UserId' => $this->userId,
                'X-CityPantry-AuthToken' => $this->salt,
            ],
            'exceptions' => false,
        ]);

        return $this->guzzleClient->send($request);
    }
}
