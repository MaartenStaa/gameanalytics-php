<?php namespace MaartenStaa\GameAnalytics;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use Http\Adapter\Guzzle6HttpAdapter;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass \MaartenStaa\GameAnalytics\Client
 */
class ClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getSecret
     * @covers ::getHttp
     */
    public function testConstruction()
    {
        $http = new Guzzle6HttpAdapter(new GuzzleClient(array(
            'handler' => new MockHandler,
        )));
        $client = new Client('aaaaa', 'bbbbb', $http);

        $this->assertEquals('bbbbb', $client->getSecret());
        $this->assertSame($http, $client->getHttp());

        $client = new Client('ccccc', 'ddddd');
        $this->assertEquals('ddddd', $client->getSecret());
        $this->assertNotNull($client->getHttp());
    }

    /**
     * @covers ::getEndpoint
     */
    public function testGetEndpoint()
    {
        $client = new Client('aaaaa', 'bbbbb');

        $this->assertEquals(
            $client::API_ENDPOINT . $client::API_VERSION . '/aaaaa/foo',
            $client->getEndpoint('foo')
        );
    }

    /**
     * @covers ::sandbox
     * @covers ::getEndpoint
     */
    public function testSandbox()
    {
        $client = new Client('aaaaa', 'bbbbb');

        $client->sandbox(true);

        $this->assertEquals(
            $client::API_ENDPOINT_SANDBOX . $client::API_VERSION . '/aaaaa/bar',
            $client->getEndpoint('bar')
        );

        $client->sandbox(false);

        $this->assertEquals(
            $client::API_ENDPOINT . $client::API_VERSION . '/aaaaa/baz',
            $client->getEndpoint('baz')
        );
    }

    /**
     * @covers ::init
     */
    public function testInit()
    {
        $client = new Client('aaaaa', 'bbbbb');

        $init = $client->init();
        $this->assertInstanceOf('MaartenStaa\GameAnalytics\Message', $init);
        $this->assertSame($client, $init->getClient());
        $this->assertStringEndsWith('aaaaa/init', $init->getEndpoint());
    }

    /**
     * @covers ::event
     */
    public function testEvent()
    {
        $client = new Client('aaaaa', 'bbbbb');

        $event = $client->event('foo');
        $this->assertInstanceOf('MaartenStaa\GameAnalytics\Message', $event);
        $this->assertSame($client, $event->getClient());
        $this->assertStringEndsWith('aaaaa/events', $event->getEndpoint());
        $this->assertEquals(['category' => 'foo'], $event->getPayload());
    }

    public function testShortcuts()
    {
        $client = new Client('aaaaa', 'bbbbb');

        foreach (array('user', 'business', 'resource', 'progression', 'design', 'error') as $category) {
            $event = $client->$category();
            $this->assertInstanceOf('MaartenStaa\GameAnalytics\Message', $event);
            $this->assertSame($client, $event->getClient());
            $this->assertStringEndsWith('aaaaa/events', $event->getEndpoint());
            $this->assertEquals(['category' => $category], $event->getPayload());
        }

        // This is the only one with a different function name.
        $event = $client->sessionEnd();
        $this->assertInstanceOf('MaartenStaa\GameAnalytics\Message', $event);
        $this->assertSame($client, $event->getClient());
        $this->assertStringEndsWith('aaaaa/events', $event->getEndpoint());
        $this->assertEquals(['category' => 'session_end'], $event->getPayload());
    }
}
