<?php

declare(strict_types=1);
/**
 * Wlfpanda1012
 *
 * @link    https://blog.wlfpanda1012.com/
 */

namespace Wlfpanda1012\LaravelPassportCache;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TokenRepository::class, function () {
            return new CacheTokenRepository(
                Config::get('passport.cache.token.prefix'),
                Config::get('passport.cache.token.expires_in'),
                Config::get('passport.cache.token.tags', []),
                Config::get('passport.cache.token.store', Config::get('cache.default'))
            );
        });
        $this->app->singleton(ClientRepository::class, function () {
            return new CacheClientRepository(
                Config::get('passport.cache.client.prefix'),
                Config::get('passport.cache.client.expires_in'),
                Config::get('passport.cache.client.tags', []),
                Config::get('passport.cache.client.store', Config::get('cache.default'))
            );
        });
    }
}
