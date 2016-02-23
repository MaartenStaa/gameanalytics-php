<?php namespace MaartenStaa\GameAnalytics;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass \MaartenStaa\GameAnalytics\Message
 */
class MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getEndpoint
     * @covers ::getClient
     */
    public function testConstruction()
    {
        $client = new Client('key', 'secret');
        $message = new Message('http://www.example.com/', $client);

        $this->assertEquals('http://www.example.com/', $message->getEndpoint());
        $this->assertSame($client, $message->getClient());
    }

    /**
     * @covers ::getPayload
     * @covers ::set
     */
    public function testSetAndGetPayload()
    {
        $message = new Message('http://www.example.com/', new Client('key', 'secret'));

        $this->assertEquals(array(), $message->getPayload());

        $this->assertSame($message, $message->set('key', 'value'));
        $this->assertEquals(array('key' => 'value'), $message->getPayload());

        $this->assertSame($message, $message->set(array(
            'foo1' => 'bar',
            'foo2' => 'baz',
        )));
        $this->assertEquals(array(
            'key' => 'value',
            'foo1' => 'bar',
            'foo2' => 'baz',
        ), $message->getPayload());
    }

    /**
     * @covers ::send
     * @covers ::buildRequest
     * @covers ::getGzippedBody
     * @covers ::getAuthorization
     */
    public function testSend()
    {
        $container = array();
        $history = Middleware::history($container);

        $mockHandler = new MockHandler(array(
            new Response(200, array(), 'Everything OK'),
        ));

        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);
        // $stack->push($mockHandler);

        $http = new GuzzleAdapter(new GuzzleClient(array(
            'handler' => $stack,
        )));
        $client = new Client('aaaaa', 'bbbbb', $http);

        $message = new Message('http://www.example.com/', $client);
        $message->set('foo', 'bar');

        // Call send() and ensure the right response was returned (which we set up above)
        $response = $message->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Everything OK', $response->getBody());

        // Ensure the request has everything that should be there
        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('http://www.example.com/', (string) $request->getUri());
        $this->assertEquals('gzip', $request->getHeaderLine('Content-Encoding'));
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('2Isi0VhuV8oniANJzAZVBEZ3DSAKmP0hQrVh3jbUNaQ=', $request->getHeaderLine('Authorization'));
        $this->assertEquals('H4sIAAAAAAAAA6tWSsvPV7JSSkosUqoFAO/1K/4NAAAA', base64_encode((string) $request->getBody()));
    }
}
