<?php

namespace OhMyBrew\ShopifyApp\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use OhMyBrew\ShopifyApp\ShopifyApp;
use OhMyBrew\ShopifyApp\Models\Charge;
use OhMyBrew\ShopifyApp\Models\Plan;

/**
 * Responsible for reprecenting a shop record.
 */
trait ShopModelTrait
{
    use SoftDeletes;

    /**
     * The API instance.
     *
     * @var \OhMyBrew\ShopifyApp\ShopifyAPI
     */
    protected $api;


    /**
     * Constructor for the model.
     *
     * @param array $attributes The model attribues to pass in.
     *
     * @return self
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Creates or returns an instance of API for the shop.
     *
     * @return \OhMyBrew\ShopifyApp\ShopifyAPI
     */
    public function api()
    {
        if (!$this->api)
        {
            // Create new API instance
            $this->api = (ShopifyApp::api())->setShopDomain($this->shopify_domain)
                ->setAccessToken($this->shopify_token);
        }

        // Return existing instance
        return $this->api;
    }

    /**
     * Checks is shop is grandfathered in.
     *
     * @return bool
     */
    public function isGrandfathered()
    {
        return ((bool) $this->grandfathered) === true;
    }

    /**
     * Get charges.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    /**
     * Checks if charges have been applied to the shop.
     *
     * @return bool
     */
    public function hasCharges()
    {
        return $this->charges->isNotEmpty();
    }

    /**
     * Gets the plan.
     *
     * @return \OhMyBrew\ShopifyApp\Models\Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Checks if the shop is freemium.
     *
     * @return bool
     */
    public function isFreemium()
    {
        return ((bool) $this->freemium) === true;
    }

    /**
     * Gets the last single or recurring charge for the shop.
     *
     * @param int|null $planID The plan ID to check with.
     *
     * @return null|\OhMyBrew\ShopifyApp\Models\Charge
     */
    public function planCharge(int $planID = null)
    {
        return $this
            ->charges()
            ->withTrashed()
            ->whereIn('type', [Charge::CHARGE_RECURRING, Charge::CHARGE_ONETIME])
            ->where('plan_id', $planID ?? $this->plan_id)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
