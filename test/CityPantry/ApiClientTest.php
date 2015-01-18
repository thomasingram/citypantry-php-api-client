<?php

namespace CityPantry;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message\Response;

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

    public function testRequest()
    {
        $request = new Request();
        $request->cookies->set('userId', 123);
        $request->cookies->set('salt', 'abc');
        $client = ApiClient::createClientFromRequestCookies($request, ApiClient::ENDPOINT_DEV);
        $guzzleClient = $client->getGuzzleClient();

        $mock = new Mock([
            new Response(200),
        ]);
        $guzzleClient->getEmitter()->attach($mock);

        $history = new History();
        $guzzleClient->getEmitter()->attach($history);

        $client->request('PUT', '/get-something');
        $lastRequest = $history->getLastRequest();

        // Should use the correct method and URL.
        $this->assertEquals('PUT', $lastRequest->getMethod());
        $this->assertEquals('http://api.citypantry.dev/get-something', $lastRequest->getUrl());

        // Should add authentication headers.
        $this->assertEquals('123', $lastRequest->getHeaders()['X-CityPantry-UserId'][0]);
        $this->assertEquals('abc', $lastRequest->getHeaders()['X-CityPantry-AuthToken'][0]);
    }
}
