<?php

declare(strict_types=1);
/**
 * Wlfpanda1012
 *
 * @link    https://blog.wlfpanda1012.com/
 */

namespace Wlfpanda1012\LaravelPassportCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

/**
 * Class CacheClientRepository
 * 继承 ClientRepository 并且覆盖了原有的方法.
 */
class CacheClientRepository extends ClientRepository
{
    protected string $cacheKeyPrefix;

    protected float|int $expiresInSeconds;

    protected array $cacheTags;

    protected mixed $cacheStore;

    protected ?Repository $store;

    public function __construct(?string $cacheKeyPrefix = null, ?int $expiresInSeconds = null, array $tags = [], ?string $store = null)
    {
        parent::__construct();
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
     * Get a client by the given ID.
     *
     * @param int|string $id
     */
    public function find($id): ?Client
    {
        return $this->cacheStore()->remember(
            $this->itemKey($id),
            Carbon::now()->addSeconds($this->expiresInSeconds),
            function () use ($id) {
                $client = Passport::client();

                return $client->newQuery()->where($client->getKeyName(), $id)->first();
            }
        );
    }

    /**
     * Get a client instance for the given ID and user ID.
     *
     * @param int|string $clientId
     * @param mixed $userId
     */
    public function findForUser($clientId, $userId): ?Client
    {
        return $this->cacheStore()->remember(
            $this->itemKey($clientId),
            Carbon::now()->addSeconds($this->expiresInSeconds),
            function () use ($clientId, $userId) {
                $client = Passport::client();

                return $client->newQuery()
                    ->where($client->getKeyName(), $clientId)
                    ->where('user_id', $userId)
                    ->first();
            }
        );
    }
}
