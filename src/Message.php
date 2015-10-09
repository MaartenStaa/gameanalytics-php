<?php namespace MaartenStaa\GameAnalytics;

use Http\Discovery\MessageFactoryDiscovery;

/**
 * A single message to be sent to GA.
 *
 * @author Maarten Staa
 */
class Message
{
    protected $endpoint;
    protected $client;
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
     * Set a value to be sent as part of this message. Use either as
     * set($myKey, $myValue) or as set(array('key1' => 'value1', 'key2' => 'value2')).
     *
     * @param string|array $key
     * @param mixed|null   $value
     */
    public function set($key, $value = null)
    {
        if ($value === null && is_array($key)) {
            $this->payload = array_merge($this->payload, $key);
        } else {
            $this->payload[$key] = $value;
        }
    }

    /**
     * Send this message to the configured endpoint using the HTTP adapter.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send()
    {
        $request = $this->buildRequest();

        return $this->client->getHttp()->sendRequest($request);
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
            ->createRequest('POST', $this->endpoint)
            ->withBody($body)
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
        $body = json_encode($this->payload);

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
