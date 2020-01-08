<?php

namespace CrewLabs\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Dribbble extends AbstractProvider
{
    use BearerAuthorizationTrait;


    /**
     * @var array List of scopes that will be used for authentication.
     * @link https://developer.dribbble.com/v2/oauth/#scopes
     */
    protected $scopes = [];

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://dribbble.com/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://dribbble.com/oauth/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.dribbble.com/v2/user?' . http_build_query(['access_token' => $token->getToken()]);
    }

    protected function getAuthorizationParameters(array $options)
    {

        // Additional scopes MAY be added by constructor or option.
        $scopes = array_merge($this->getDefaultScopes(), $this->scopes);
        if (!empty($options['scope'])) {
            $scopes = array_merge($scopes, $options['scope']);
        }
        $options['scope'] = array_unique($scopes);
        return parent::getAuthorizationParameters($options);
    }

    /**
     * Get the default scopes used by this provider.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['public'];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     * @return string Scope separator
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        // @codeCoverageIgnoreStart
        if (empty($data['error'])) {
            return;
        }
        // @codeCoverageIgnoreEnd
        $code = 0;
        $error = $data['error'];
        if (is_array($error)) {
            $code = $error['code'];
            $error = $error['message'];
        }
        throw new IdentityProviderException($error, $code, $data);
    }
    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new DribbbleResourceOwner($response);
    }
}
