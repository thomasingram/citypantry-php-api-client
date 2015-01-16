<?php

namespace CityPantry;

use Symfony\Component\HttpFoundation\Request;

class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    public function testIsLoggedInAfterCreateClientFromRequestCookies_true()
    {
        $request = new Request();
        $request->cookies->set('userId', 123);
        $request->cookies->set('salt', 'abc');
        $client = ApiClient::createClientFromRequestCookies($request, ApiClient::ENDPOINT_DEV);

        $this->assertTrue($client->isLoggedIn());
    }

    public function testIsLoggedInAfterCreateClientFromRequestCookies_false()
    {
        $request = new Request();
        $client = ApiClient::createClientFromRequestCookies($request, ApiClient::ENDPOINT_DEV);

        $this->assertFalse($client->isLoggedIn());
    }
}
