<?php namespace Codecycler\SURFconext;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Validator;
use Illuminate\Http\Request;
use System\Classes\PluginBase;
use Lcobucci\Clock\SystemClock;
use Lcobucci\Jose\Parsing\Decoder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Codecycler\SURFconext\Classes\KeysFetcher;
use Codecycler\SURFconext\Classes\TokenStorage;
use Codecycler\SURFconext\Classes\TokenRefresher;
use Lcobucci\JWT\Validation\Validator as JWTValidator;
use Codecycler\SURFconext\Classes\Contract\JSONGetter;
use Codecycler\SURFconext\Classes\Contract\JSONPoster;
use Codecycler\SURFconext\Classes\Contract\Authenticator;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Codecycler\SURFconext\Classes\Adapter\JSONFetcherAdapter;
use Codecycler\SURFconext\Classes\OIDConnectSocialiteProvider;
use Codecycler\SURFconext\Classes\Adapter\NullAuthenticatorAdapter;

/**
 * SURFconext Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Codecycler.Socialite',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'SURFconext',
            'description' => 'No description provided yet...',
            'author'      => 'Codecycler',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JSONFetcherAdapter::class, function ($app) {
            return new JSONFetcherAdapter();
        });

        $this->app->singleton(JSONGetter::class, function ($app) {
            return $app[JSONFetcherAdapter::class];
        });

        $this->app->singleton(JSONPoster::class, function ($app) {
            return $app[JSONFetcherAdapter::class];
        });

        $this->app->singleton(Decoder::class, function () {
            return new JoseEncoder();
        });

        $this->app->singleton(Parser::class, function ($app) {
            return new Token\Parser($app[Decoder::class]);
        });

        $this->app->singleton(Validator::class, function () {
            return new JWTValidator();
        });

        $this->app->singleton(Clock::class, function () {
            $timezone = new \DateTimeZone("Europe/Amsterdam");
            return new SystemClock($timezone);
        });

        $this->app->singleton(Signer::class, function ($app) {
            return new Signer\Rsa\Sha256();
        });

        $this->app->bind(KeysFetcher::class, function ($app) {
            return new KeysFetcher(
                $app[\Codecycler\SURFconext\Classes\Contract\JSONGetter::class],
                $app['cache.store'],
                $app[Decoder::class],
                config('codecycler.surfconext::config.keys')
            );
        });

        $this->app->bind(TokenRefresher::class, function ($app) {
            return new TokenRefresher(
                $app[\Codecycler\SURFconext\Classes\Contract\JSONPoster::class],
                $app[TokenStorage::class],
                config('codecycler.surfconext::config.client_id'),
                config('codecycler.surfconext::config.client_secret'),
                config('codecycler.surfconext::config.redirect'),
                config('codecycler.surfconext::config.token'),
            );
        });

        $this->app->singleton(Authenticator::class, function () {
            return new NullAuthenticatorAdapter();
        });
    }

    public function boot()
    {
        $socialite = $this->app->make(SocialiteFactory::class);

        $socialite->extend('surfconext', function ($app) {
            return new OIDConnectSocialiteProvider(
                $app[Request::class],
                $app[Parser::class],
                config('codecycler.surfconext::config.client_id'),
                config('codecycler.surfconext::config.client_secret'),
                config('codecycler.surfconext::config.redirect'),
                config('codecycler.surfconext::config.auth'),
                config('codecycler.surfconext::config.token'),
            );
        });
    }
}