<?php

use Illuminate\Support\Facades\Storage;
use OhMyBrew\ShopifyApp\ShopifyApp;

class Helpers
{
    static public function log($shop, $messages)
    {
        array_unshift($messages, "[" . date('Y-m-d G:m:i') . "]");
        Storage::disk('logs')->append($shop->shopify_domain . '.log', implode("\t", $messages));
    }
}

function alert($msg)
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    logger()->alert($caller['file'] . ':' . $caller['line'] . ' - ' . $msg);
}

/**
 * @param bool $forceReload
 * @return \OhMyBrew\ShopifyApp\Models\Shop
 */
function shop()
{
    return ShopifyApp::shop();
}

/**
 * @param bool $forceReload
 * @return \OhMyBrew\ShopifyApp\Models\Charge
 */
function planCharge($forceReload = false)
{
    static $planCharge = null;

    if (!$forceReload && !is_null($planCharge))
    {
        return $planCharge;
    }

    $planCharge = shop()->planCharge();

    return $planCharge;
}

function formatMoney($amount, $currency = 'EUR')
{
    if ($currency == 'EUR')
    {
        setlocale(LC_MONETARY, 'de_DE.utf8');

        return money_format('€%!n', floatval($amount));
    }

    return money_format('%+n', floatval($amount));
}

function formatWeight($grams, $unit = 'kg')
{
    return number_format($grams / 1000, 1, ",", "") . ' ' . $unit;
}

function isSandboxInstance()
{
    return env('APP_INSTANCE') == 'sandbox';
}

function isProductionInstance()
{
    return env('APP_INSTANCE') == 'production';
}

function isDebugMode()
{
    return config('app.debug');
}
