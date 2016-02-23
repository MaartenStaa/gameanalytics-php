<?php namespace MaartenStaa\GameAnalytics;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;

/**
 * Main GA client.
 *
 * @author Maarten Staa
 */
class Client
{
    const API_ENDPOINT = 'http://api.gameanalytics.com/';
    const API_ENDPOINT_SANDBOX = 'http://sandbox-api.gameanalytics.com/';
    const API_VERSION = 'v2';

    /**
     * The game key from GameAnalytics.
     *
     * @var string
     */
    protected $key;

    /**
     * The game's secret key from GameAnalytics.
     *
     * @var string
     */
    protected $secret;

    /**
     * The HTTP handler that should be used.
     *
     * @var \Http\Client\HttpClient
     */
    protected $http;

    /**
     * The HTTP message factory that should be used.
     *
     * @var \Http\Message\MessageFactory
     */
    protected $factory;

    /**
     * Whether this client should communicate with the sandbox servers instead
     * of the real API endpoints.
     *
     * @var bool
     */
    protected $sandbox = false;

    /**
     * Constructor.
     *
     * @param string $key
     * @param string $secret
     * @param \Http\Client\HttpClient|null $http
     * @param \Http\Message\MessageFactory|null $factory
     */
    public function __construct($key, $secret, HttpClient $http = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->http = $http ?: HttpClientDiscovery::find();
        $this->factory = $factory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Set whether this client should refer to the sandbox endpoint.
     *
     * @param bool $value
     */
    public function sandbox($value)
    {
        $this->sandbox = $value;
    }

    /**
     * Get the configured game's secret key from GameAnalytics.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get the configured HTTP handler.
     *
     * @return \Http\Client\HttpAdapter
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * Get the configured HTTP message factory.
     *
     * @return \Http\Message\MessageFactory
     */
    public function getMessageFactory()
    {
        return $this->factory;
    }

    /**
     * Get the URL that events should be posted to.
     *
     * @return string
     */
    public function getEndpoint($api)
    {
        return ($this->sandbox ? self::API_ENDPOINT_SANDBOX : self::API_ENDPOINT) .
            self::API_VERSION . '/' . $this->key . '/'. $api;
    }

    /**
     * Get a new init message.
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function init()
    {
        return new Message($this->getEndpoint('init'), $this);
    }

    /**
     * Get a new event message for the given category.
     *
     * @param  string $category
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function event($category)
    {
        $message = new Message($this->getEndpoint('events'), $this);
        $message->set('category', $category);

        return $message;
    }

    /**
     * Create a "user" event. Shortcut for event('user').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function user()
    {
        return $this->event('user');
    }

    /**
     * Create a "session_end" event. Shortcut for event('session_end').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function sessionEnd()
    {
        return $this->event('session_end');
    }

    /**
     * Create a "business" event. Shortcut for event('business').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function business()
    {
        return $this->event('business');
    }

    /**
     * Create a "resource" event. Shortcut for event('resource').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function resource()
    {
        return $this->event('resource');
    }

    /**
     * Create a "progression" event. Shortcut for event('progression').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function progression()
    {
        return $this->event('progression');
    }

    /**
     * Create a "design" event. Shortcut for event('design').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function design()
    {
        return $this->event('design');
    }

    /**
     * Create a "error" event. Shortcut for event('error').
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function error()
    {
        return $this->event('error');
    }
}
