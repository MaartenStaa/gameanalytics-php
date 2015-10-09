<?php namespace MaartenStaa\GameAnalytics;

use Http\Adapter\HttpAdapter;
use Http\Discovery\HttpAdapterDiscovery;

/**
 * Main GA client.
 *
 * @author Maarten Staa
 */
class Client
{
    const API_ENDPOINT = 'https://api.gameanalytics.com/';
    const API_ENDPOINT_SANDBOX = 'https://sandbox-api.gameanalytics.com/';
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
     * @var \Http\Adapter\HttpAdapter
     */
    protected $http;

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
     * @param \Http\Adapter\HttpAdapter|null $http
     */
    public function __construct($key, $secret, HttpAdapter $http = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->http = $http ?: HttpAdapterDiscovery::find();
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
     * @return \Http\Adapter\HttpAdapter
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * Get the URL that events should be posted to.
     *
     * @return string
     */
    protected function getEndpoint($api)
    {
        return ($this->sandbox ? self::API_ENDPOINT_SANDBOX : API_ENDPOINT) .
            self::API_VERSION . '/' . $this->key . '/'. $api;
    }

    /**
     * Get a new init message.
     *
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function init()
    {
        return (new Message($this->getEndpoint('init')))
            ->setHttp($this->http);
    }

    /**
     * Get a new event message for the given category.
     *
     * @param  string $category
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function event($category)
    {
        return (new Message($this->getEndpoint('events')))
            ->setHttp($this->http)
            ->set('category', $category);
    }
}