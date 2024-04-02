# Laravel passport cache

思路源于 [overtrue/laravel-passport-cache-token](https://github.com/overtrue/laravel-passport-cache-token) 项目
但是我发现其实并没有完全解决所有的对于 oauth 的数据库sql问题。
```shell
[2024-04-01 11:48:41] local.INFO: TokenRepository find id: cf0e54e73d2bd0c7acb15e89db5ef2d5ca9c86a439d13e46263ad1450448c848fc36d11c57c11414  
[2024-04-01 11:48:41] local.INFO: select * from `oauth_access_tokens` where `id` = ? limit 1 ["cf0e54e73d2bd0c7acb15e89db5ef2d5ca9c86a439d13e46263ad1450448c848fc36d11c57c11414"] 
[2024-04-01 11:48:41] local.INFO: ClientRepository find id: 2  
[2024-04-01 11:48:41] local.INFO: select * from `oauth_clients` where `id` = ? limit 1 ["2"] 
[2024-04-01 11:48:41] local.INFO: TokenRepository find id: cf0e54e73d2bd0c7acb15e89db5ef2d5ca9c86a439d13e46263ad1450448c848fc36d11c57c11414  
[2024-04-01 11:48:41] local.INFO: select * from `oauth_access_tokens` where `id` = ? limit 1 ["cf0e54e73d2bd0c7acb15e89db5ef2d5ca9c86a439d13e46263ad1450448c848fc36d11c57c11414"]
```
TokenRepository 中的缓存解决了，但是 ClientRepository 中的缓存并没有解决，所以我决定自己实现一个。

## Installing

```shell
$ composer require wlfpanda1012/laravel-passport-cache -vvv
```

## Usage

**config/passport.php**
```php
            return [
                //...
                'cache' => [
                    'token' => [
                        // Cache key prefix
                        'prefix' => 'passport_token',

                        // The lifetime of token cache,
                        // Unit: second
                        'expires_in' => 300,

                        // Cache tags
                        'tags' => [],
                    ],
                    'client' => [
                        // Cache key prefix
                        'prefix' => 'passport_client',

                        // The lifetime of token cache,
                        // Unit: second
                        'expires_in' => 300,

                        // Cache tags
                        'tags' => [],
                    ],
                ],
            ];
```

## License

MIT