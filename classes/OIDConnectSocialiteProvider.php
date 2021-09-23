<?php namespace Codecycler\SURFconext\Classes;

use Lcobucci\JWT\Parser;
use Illuminate\Http\Request;
use October\Rain\Support\Arr;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\InvalidStateException;
use Codecycler\SURFconext\Classes\Exception\TokenRequestException;

class OIDConnectSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = [
        'openid',
    ];

    protected $scopeSeparator = ' ';

    protected $parser;

    protected $authUrl;

    protected $tokenUrl;

    public function __construct(
        Request $request,
        Parser $parser,
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        string $authUrl,
        string $tokenUrl
    ) {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl);

        $this->parser = $parser;
        $this->authUrl = $authUrl;
        $this->tokenUrl = $tokenUrl;

        $this->parameters = [
            'claims' => json_encode([
                'id_token' => [
                    'email' => null,
                    'given_name' => null,
                    'family_name' => null,
                    'schac_home_organization' => null,
                ],
            ]),
        ];
    }

    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        if (!empty($response['error'])) {
            throw new TokenRequestException($response['error']);
        }

        $token = $response['id_token'];

        $user = $this->mapUserToObject($this->getUserByToken($token));

        if (!$user) {
            // Create new user
            return new User();
        }

        return $user->setToken($token)
            //->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'sub' => $user['sub'],
            'iss' => $user['iss'],
            'nickname' => $user['name'],
            'name' => $user['name'],
            'surname' => $user['surname'],
            'email' => $user['email'],
            'organisation' => $user['organisation'],
        ]);
    }

    protected function getUserByToken($token)
    {
        $plainToken = $this->parser->parse($token);

        $claims = $plainToken->claims();

        return [
            'sub' => $claims->get('sub'),
            'iss' => $claims->get('iss'),
            'name' => $claims->get('given_name'),
            'surname' => $claims->get('family_name'),
            'email' => $claims->get('email'),
            'organisation' => $claims->get('schac_home_organization'),
        ];
    }

    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'scope' => implode(' ', $this->scopes),
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
        ];
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->authUrl, $state);
    }

    protected function getTokenUrl()
    {
        return $this->tokenUrl;
    }

    /**
     * Get the access token response for the given code.
     *
     * @param  string  $code
     * @return array
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post(
            $this->getTokenUrl(),
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => $this->getTokenFields($code),
            ],
        );

        return json_decode($response->getBody(), true);
    }
}
