<?php

namespace OhMyBrew\ShopifyApp\Traits;

use OhMyBrew\ShopifyApp\Models\Shop;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

/**
 * Responsible for handling incoming webhook requests.
 */
trait WebhookControllerTrait
{
    /**
     * Handles an incoming webhook.
     *
     * @param string $type The type of webhook
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($type)
    {
        $shopDomain = Request::header('x-shopify-shop-domain');

        $shopModel = Config::get('shopify-app.shop_model');
        /** @var Shop */
        $shop = $shopModel::withTrashed()->where('shopify_domain', $shopDomain)->firstOrFail();

        if ($shop->trashed())
        {
            logger(get_class() . ' - incoming webhook for TRASHED shop ' . $shopDomain);
            abort(500);
        }
        // Get the job class and dispatch
        $jobClass = Config::get('shopify-app.job_namespace') . str_replace('-', '', ucwords($type, '-')) . 'Job';
        $jobData = json_decode(Request::getContent());

        $jobClass::dispatch(
            $shopDomain,
            $jobData
        );

        return Response::make('', 201);
    }
}
