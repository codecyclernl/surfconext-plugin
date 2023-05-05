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

    protected $introspectUrl;

    public function __construct(
        Request $request,
        Parser $parser,
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        string $authUrl,
        string $tokenUrl,
        string $introspectUrl
    ) {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl);

        $this->parser = $parser;
        $this->authUrl = $authUrl;
        $this->tokenUrl = $tokenUrl;
        $this->introspectUrl = $introspectUrl;

        $this->parameters = [
            'claims' => json_encode([
                'id_token' => [
                    'email' => null,
                    'given_name' => null,
                    'family_name' => null,
                    'schac_home_organization' => null,
                    'ou' => null,
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

        $user->access_token = $response['access_token'];

        if (!$user) {
            // Create new user
            return new User();
        }

        return $user->setToken($token)
            //->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'));
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
            'departments' => $this->getDepartments($claims->get('ou')) ?? [],
        ];
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
            'departments' => $user['departments'],
        ]);
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
            'response_type' => 'code',
            'response_mode' => 'query',
        ];
    }

    public function getDepartments($departments): array
    {
        if (is_array($departments)) {
            return $departments;
        }

        try {
            return json_decode($departments, true);
        } catch (\Exception $exception) {}

        return [$departments];
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->authUrl, $state);
    }

    protected function getTokenUrl()
    {
        return $this->tokenUrl;
    }

    protected function getIntrospectUrl()
    {
        return $this->introspectUrl;
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

    public function introspect($token, $accessToken)
    {
        $response = $this->getHttpClient()->post($this->getIntrospectUrl(),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'form_params' => [
                    'token' => $accessToken,
                ],
            ],
        );

        return json_decode($response->getBody(), true);
    }

    public function userInfo($accessToken)
    {
        $response = $this->getHttpClient()->get(config('codecycler.surfconext::config.user_info'),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ],
        );

        return json_decode($response->getBody(), true);
    }
}
