<?php namespace Codecycler\SURFconext\Classes;

use Codecycler\SURFconext\Classes\Contract\JSONPoster;
use Codecycler\SURFconext\Classes\Exception\TokenStorageException;

class TokenRefresher
{
    protected $scopes = [
        'openid',
        'email',
        'profile',
    ];

    private $storage;
    private $clientId;
    private $clientSecret;

    private $redirectUrl;

    private $poster;

    private $tokenUrl;

    public function __construct(
        JSONPoster $poster,
        TokenStorage $storage,
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        string $tokenUrl
    ) {
        $this->storage = $storage;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->poster = $poster;
        $this->tokenUrl = $tokenUrl;
    }

    public function refreshIDToken(string $sub, string $iss): string
    {
        $refreshToken = $this->storage->fetchRefresh($sub, $iss);

        if (!$refreshToken) {
            throw new TokenStorageException("Failed to fetch refresh token");
        }

        $data = $this->poster->post($this->tokenUrl, [], http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'redirect_uri' => $this->redirectUrl,
            'scope' => implode(' ', $this->scopes),
        ]));

        if (!$this->storage->saveRefresh($sub, $iss, $data['refresh_token'])) {
            throw new TokenStorageException("Failed to store refresh token");
        }

        return $data['id_token'];
    }
}
