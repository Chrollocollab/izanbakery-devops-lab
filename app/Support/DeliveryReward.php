<?php

namespace App\Support;

/**
 * Spend-based delivery rewards.
 *
 * The actual delivery fee is charged at live GrabExpress rates, so we cannot
 * compute a final price up front. Instead, the order subtotal earns a discount
 * (or free delivery) that is applied against whatever the GrabExpress fee is.
 *
 *   RM50+   → RM5 off delivery
 *   RM80+   → RM8 off delivery
 *   RM100+  → RM10 off delivery
 *   RM150+  → Free delivery
 *   below   → no reward yet
 */
class DeliveryReward
{
    /**
     * Tiers ordered from highest spend to lowest. `discount` of null means the
     * whole delivery fee is waived (free delivery).
     */
    public const TIERS = [
        ['min' => 150, 'discount' => null, 'label' => 'Free delivery'],
        ['min' => 100, 'discount' => 10,   'label' => 'RM10 off delivery'],
        ['min' => 80,  'discount' => 8,    'label' => 'RM8 off delivery'],
        ['min' => 50,  'discount' => 5,    'label' => 'RM5 off delivery'],
    ];

    public const NO_REWARD_LABEL = 'Delivery charges are based on GrabExpress rates.';

    /**
     * Resolve the reward earned for a given order subtotal.
     *
     * @return array{label: string, discount: int|null, free: bool, has_reward: bool, min: int|null}
     */
    public static function forSubtotal(float $subtotal): array
    {
        foreach (self::TIERS as $tier) {
            if ($subtotal >= $tier['min']) {
                return [
                    'label'      => $tier['label'],
                    'discount'   => $tier['discount'],
                    'free'       => $tier['discount'] === null,
                    'has_reward' => true,
                    'min'        => $tier['min'],
                ];
            }
        }

        return [
            'label'      => self::NO_REWARD_LABEL,
            'discount'   => 0,
            'free'       => false,
            'has_reward' => false,
            'min'        => null,
        ];
    }

    /**
     * Just the display label for a subtotal.
     */
    public static function labelFor(float $subtotal): string
    {
        return self::forSubtotal($subtotal)['label'];
    }
}
