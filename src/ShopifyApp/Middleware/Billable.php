<?php

namespace OhMyBrew\ShopifyApp\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use OhMyBrew\ShopifyApp\ShopifyApp;

/**
 * Responsible for ensuring the shop is being billed.
 */
class Billable
{
    /**
     * Checks if a shop has paid for access.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Config::get('shopify-app.billing_enabled') === true && !shop()->hasValidPlan())
        {
            return Redirect::route('billing');
        }

        // Move on, everything's fine
        return $next($request);
    }
}
