<?php

namespace Tests\Unit;

use App\Support\DeliveryReward;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeliveryRewardTest extends TestCase
{
    public static function subtotalProvider(): array
    {
        return [
            // subtotal, expected label, has_reward, free
            'below threshold' => [49.99, DeliveryReward::NO_REWARD_LABEL, false, false],
            'exactly 50' => [50.00, 'RM5 off delivery', true, false],
            'just under 80' => [79.99, 'RM5 off delivery', true, false],
            'exactly 80' => [80.00, 'RM8 off delivery', true, false],
            'just under 100' => [99.99, 'RM8 off delivery', true, false],
            'exactly 100' => [100.00, 'RM10 off delivery', true, false],
            'just under 150' => [149.99, 'RM10 off delivery', true, false],
            'exactly 150' => [150.00, 'Free delivery', true, true],
            'well above 150' => [500.00, 'Free delivery', true, true],
            'zero' => [0.0, DeliveryReward::NO_REWARD_LABEL, false, false],
        ];
    }

    #[DataProvider('subtotalProvider')]
    public function test_reward_tiers(float $subtotal, string $label, bool $hasReward, bool $free): void
    {
        $reward = DeliveryReward::forSubtotal($subtotal);

        $this->assertSame($label, $reward['label']);
        $this->assertSame($hasReward, $reward['has_reward']);
        $this->assertSame($free, $reward['free']);
    }
}
