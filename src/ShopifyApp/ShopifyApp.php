<?php

namespace OhMyBrew\ShopifyApp;

use Illuminate\Support\Facades\Config;

/**
 * The base "helper" class for this package.
 */
class ShopifyApp
{
    /**
     * The current shop.
     *
     * @var \OhMyBrew\ShopifyApp\Models\Shop
     */
    public static $shop = null;

    /**
     * Gets/sets the current shop.
     *
     * @param string|null $shopDomain
     *
     * @return \OhMyBrew\ShopifyApp\Models\Shop
     */
    static public function shop(string $shopDomain = null)
    {
        $shopifyDomain = self::sanitizeShopDomain($shopDomain);
        if ($shopifyDomain)
        {
            // Grab shop from database here
            $shopModel = Config::get('shopify-app.shop_model');
            $shop = $shopModel::withTrashed()->firstOrCreate(['shopify_domain' => $shopifyDomain]);

            // Update shop instance
            self::$shop = $shop;
        }

        return self::$shop;
    }

    /**
     * Gets an API instance.
     *
     * @return \OhMyBrew\ShopifyApp\ShopifyAPI
     */
    static public function api()
    {
        $api = new ShopifyAPI();
        $api->setApiKey(Config::get('shopify-app.api_key'));
        $api->setApiSecret(Config::get('shopify-app.api_secret'));

        // Add versioning?
        $version = Config::get('shopify-app.api_version');
        if ($version !== null)
        {
            $api->setVersion($version);
        }

        // Enable basic rate limiting?
        if (Config::get('shopify-app.api_rate_limiting_enabled') === true)
        {
            $api->enableRateLimiting(
                Config::get('shopify-app.api_rate_limit_cycle'),
                Config::get('shopify-app.api_rate_limit_cycle_buffer')
            );
        }

        return $api;
    }

    /**
     * Ensures shop domain meets the specs.
     *
     * @param string $domain The shopify domain
     *
     * @return string
     */
    static public function sanitizeShopDomain($domain)
    {
        if (empty($domain))
        {
            return null;
        }

        $configEndDomain = 'myshopify.com';
        $domain = strtolower(preg_replace('/https?:\/\//i', '', trim($domain)));

        if (strpos($domain, $configEndDomain) === false && strpos($domain, '.') === false)
        {
            // No myshopify.com ($configEndDomain) in shop's name
            $domain .= ".{$configEndDomain}";
        }

        // Return the host after cleaned up
        return parse_url("https://{$domain}", PHP_URL_HOST);
    }

    /**
     * HMAC creation helper.
     *
     * @param array $opts
     *
     * @return string
     */
    static public function createHmac(array $opts)
    {
        // Setup defaults
        $data = $opts['data'];
        $raw = $opts['raw'] ?? false;
        $buildQuery = $opts['buildQuery'] ?? false;
        $buildQueryWithJoin = $opts['buildQueryWithJoin'] ?? false;
        $encode = $opts['encode'] ?? false;
        $secret = $opts['secret'] ?? Config::get('shopify-app.api_secret');

        if ($buildQuery)
        {
            //Query params must be sorted and compiled
            ksort($data);
            $queryCompiled = [];
            foreach ($data as $key => $value)
            {
                $queryCompiled[] = "{$key}=" . (is_array($value) ? implode(',', $value) : $value);
            }
            $data = implode(($buildQueryWithJoin ? '&' : ''), $queryCompiled);
        }

        // Create the hmac all based on the secret
        $hmac = hash_hmac('sha256', $data, $secret, $raw);

        // Return based on options
        return $encode ? base64_encode($hmac) : $hmac;
    }
}
