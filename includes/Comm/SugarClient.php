<?php


class SugarClient extends \League\OAuth1\Client\Server\Server
{

    /**
     * Get the URL for retrieving temporary credentials.
     *
     * @return string
     */
    public function urlTemporaryCredentials()
    {
        return 'https://crm.virtuvia.localhost' . '/service/v4_1/rest.php'
//            . '?method=oauth_request_token'
            ;
    }

    /**
     * Get the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    public function urlAuthorization()
    {
        // TODO: Implement urlAuthorization() method.
    }

    /**
     * Get the URL retrieving token credentials.
     *
     * @return string
     */
    public function urlTokenCredentials()
    {
        // TODO: Implement urlTokenCredentials() method.
    }

    /**
     * Get the URL for retrieving user details.
     *
     * @return string
     */
    public function urlUserDetails()
    {
        // TODO: Implement urlUserDetails() method.
    }

    /**
     * Take the decoded data from the user details URL and convert
     * it to a User object.
     *
     * @param mixed $data
     * @param \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials
     *
     * @return \League\OAuth1\Client\Server\User
     */
    public function userDetails($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userDetails() method.
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's UID.
     *
     * @param mixed $data
     * @param \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials
     *
     * @return string|int
     */
    public function userUid($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userUid() method.
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's email.
     *
     * @param mixed $data
     * @param \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials
     *
     * @return string
     */
    public function userEmail($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userEmail() method.
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's screen name.
     *
     * @param mixed $data
     * @param \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials
     *
     * @return string
     */
    public function userScreenName($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userScreenName() method.
    }

    protected function temporaryCredentialsProtocolHeader($uri)
    {
        $parameters = array_merge(
//            ['method' => 'oauth_request_token',],
            $this->baseProtocolParameters(), array(
//            'oauth_callback' => $this->clientCredentials->getCallbackUri(),
            'method' => 'oauth_request_token',
        ));

        $parameters['oauth_signature'] = $this->signature->sign($uri, $parameters, 'GET');

        $normal = $this->normalizeProtocolParameters($parameters);

        return $parameters; //
    }

    /**
     * Get the base protocol parameters for an OAuth request.
     * Each request builds on these parameters.
     *
     * @return array
     *
     * @see    OAuth 1.0 RFC 5849 Section 3.1
     */
    protected function baseProtocolParameters()
    {
        $dateTime = new \DateTime();

        return array(
            'oauth_consumer_key' => $this->clientCredentials->getIdentifier(),
            'oauth_signature_method' => $this->signature->method(),
            'oauth_timestamp' => 1507324807, //$dateTime->format('U'),
            'oauth_nonce' => 'guhtb6', //$this->nonce(),
            'oauth_version' => '1.0',
        );
    }



    /**
     * Gets temporary credentials by performing a request to
     * the server.
     *
     * @return TemporaryCredentials
     */
    public function getTemporaryCredentials()
    {
        $uri = $this->urlTemporaryCredentials();

        $client = $this->createHttpClient();

        $query = $this->temporaryCredentialsProtocolHeader($uri);
//        $header['method'] = 'request_oauth_token';
        $authorizationHeader = []; //array('Authorization' => $header);
        $headers = $this->buildHttpClientHeaders($authorizationHeader);

        try {
            $response = $client->get($uri, [
                'query' => $query,
                'headers' => $headers,
            ]);
        } catch (BadResponseException $e) {
            return $this->handleTemporaryCredentialsBadResponse($e);
        }

        return $this->createTemporaryCredentials((string) $response->getBody());
    }
}
