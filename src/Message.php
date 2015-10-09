<?php namespace MaartenStaa\GameAnalytics;

use Http\Discovery\MessageFactoryDiscovery;

/**
 * A single message to be sent to GA.
 *
 * @author Maarten Staa
 */
class Message
{
    /**
     * The URI of the endpoint that this message will be sent to.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The GameAnalytics client that this message belongs to.
     *
     * @var \MaartenStaa\GameAnalytics\Client
     */
    protected $client;

    /**
     * The parameters for this message. These will be JSON encoded into the request
     * body when sending the message.
     *
     * @var array
     */
    protected $payload = array();

    /**
     * Constructor.
     *
     * @param string $endpoint
     * @param \MaartenStaa\GameAnalytics\Client $client
     */
    public function __construct($endpoint, Client $client)
    {
        $this->endpoint = $endpoint;
        $this->client = $client;
    }

    /**
     * Get the configured endpoint for this message.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get the configured GameAnalytics client.
     *
     * @return \MaartenStaa\GameAnalytics\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the payload of all the parameters that are set on this message.
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set a value to be sent as part of this message. Use either as
     * set($myKey, $myValue) or as set(array('key1' => 'value1', 'key2' => 'value2')).
     *
     * @param  string|array $key
     * @param  mixed|null   $value
     * @return \MaartenStaa\GameAnalytics\Message
     */
    public function set($key, $value = null)
    {
        if ($value === null && is_array($key)) {
            $this->payload = array_merge($this->payload, $key);
        } else {
            $this->payload[$key] = $value;
        }

        return $this;
    }

    /**
     * Send this message to the configured endpoint using the HTTP adapter.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send()
    {
        $request = $this->buildRequest();

        return $this->getClient()->getHttp()->sendRequest($request);
    }

    /**
     * Build the request to send.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function buildRequest()
    {
        // Prepare body of request.
        $body = $this->getGzippedBody();

        // Build the request and return it.
        return MessageFactoryDiscovery::find()
            ->createRequest('POST', $this->getEndpoint(), '1.1', array(), $body)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Encoding', 'gzip')
            ->withHeader('Authorization', $this->getAuthorization($body));
    }

    /**
     * Get the GZipped JSON-encoded request body.
     *
     * @return string
     */
    protected function getGzippedBody()
    {
        $body = json_encode($this->getPayload());

        return gzencode($body);
    }

    /**
     * Get the contents of the Authorization header based on the given request
     * body. Returns the base-64 encoded HMAC SHA-256 digest.
     *
     * @return string
     */
    protected function getAuthorization($body)
    {
        return base64_encode(hash_hmac('sha256', $body, $this->client->getSecret(), true));
    }
}
