<?php

use Codecycler\SURFconext\Models\Settings;

return [
    'client_id' => Settings::get('client_id', env('SURFCONEXT_CLIENT_ID', '')),
    'client_secret' => Settings::get('client_secret', env('SURFCONEXT_CLIENT_SECRET', '')),
    'redirect' => Settings::get('redirect', url('/surfconext/auth/callback')),
    'auth' => Settings::get('auth', 'https://connect.test.surfconext.nl/oidc/authorize'),
    'token' => Settings::get('token', 'https://connect.test.surfconext.nl/oidc/token'),
    'keys' => Settings::get('keys', 'https://connect.test.surfconext.nl/oidc/certs'),
    'user_info' => Settings::get('user_info', 'https://connect.test.surfconext.nl/oidc/userinfo'),
    'introspect' => Settings::get('introspect', 'https://connect.test.surfconext.nl/oidc/introspect'),
    'guzzle' => [],
];
