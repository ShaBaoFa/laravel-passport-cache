<?php

declare(strict_types=1);
/**
 * Wlfpanda1012
 *
 * @link    https://blog.wlfpanda1012.com/
 */

namespace Wlfpanda1012\LaravelPassportCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;

/**
 * Class CacheClientRepository
 * 继承 ClientRepository 并且覆盖了原有的方法.
 */
class CacheTokenRepository extends TokenRepository
{
    protected string $cacheKeyPrefix;

    protected float|int $expiresInSeconds;

    protected array $cacheTags;

    protected mixed $cacheStore;

    protected ?Repository $store;

    public function __construct(?string $cacheKeyPrefix = null, ?int $expiresInSeconds = null, array $tags = [], ?string $store = null)
    {
        $this->store = null;
        $this->cacheKeyPrefix = sprintf('%s_token_', $cacheKeyPrefix ?? 'passport_client');
        $this->expiresInSeconds = $expiresInSeconds ?? 5 * 60;
        $this->cacheTags = $tags;
        $this->cacheStore = $store ?? Config::get('cache.default');
    }

    public function itemKey(string $key): string
    {
        return $this->cacheKeyPrefix . $key;
    }

    public function cacheStore(): Repository
    {
        if ($this->store instanceof Repository) {
            return $this->store;
        }
        $store = Cache::store($this->cacheStore);

        $this->store = $store->getStore() instanceof TaggableStore ? $store->tags($this->cacheTags) : $store;

        return $this->store;
    }

    /**
     * Get a token by the given ID.
     *
     * @param string $id
     */
    public function find($id): ?Token
    {
        return $this->cacheStore()->remember(
            $this->itemKey($id),
            Carbon::now()->addSeconds($this->expiresInSeconds),
            function () use ($id) {
                return Passport::token()->newQuery()->where('id', $id)->first();
            }
        );
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param string $id
     * @param int $userId
     */
    public function findForUser($id, $userId): ?Token
    {
        return $this->cacheStore()->remember(
            $this->itemKey($id),
            now()->addSeconds($this->expiresInSeconds),
            function () use ($id, $userId) {
                return Passport::token()->newQuery()->where('id', $id)->where('user_id', $userId)->first();
            }
        );
    }

    /**
     * Get the token instances for the given user ID.
     *
     * @param mixed $userId
     */
    public function forUser($userId): Collection
    {
        return $this->cacheStore()->remember(
            $this->itemKey($userId),
            Carbon::now()->addSeconds($this->expiresInSeconds),
            function () use ($userId) {
                return Passport::token()->newQuery()->where('user_id', $userId)->get();
            }
        );
    }

    /**
     * Get a valid token instance for the given user and client.
     *
     * @param Authenticatable $user
     * @param Client $client
     */
    public function getValidToken($user, $client): ?Token
    {
        return $this->cacheStore()->remember(
            $this->itemKey($user->getAuthIdentifier()),
            Carbon::now()->addSeconds($this->expiresInSeconds),
            function () use ($client, $user) {
                return $client->tokens()
                    ->whereUserId($user->getAuthIdentifier())
                    ->where('revoked', 0)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
            }
        );
    }
}
