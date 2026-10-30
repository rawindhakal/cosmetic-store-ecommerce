<?php

namespace Modules\GlowPoints\Services;

use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\Cart\Facades\Cart;
use Modules\GlowPoints\Entities\GlowPointTransaction as Txn;

class GlowPoints
{
    const SESSION_KEY = 'glowpoints.redeem_points';

    /* ---------------------------------------------------------------- settings */

    public static function setting(string $key, $default = null)
    {
        $value = setting("glowpoints_{$key}");

        if ($value === null || $value === '') {
            $value = config("fleetcart.modules.glowpoints.config.{$key}", $default);
        }

        return $value;
    }

    public static function enabled(): bool
    {
        return (bool) self::setting('enabled');
    }

    public static function name(): string
    {
        return (string) (self::setting('name') ?: 'GlowPoints');
    }

    public static function pointValue(): float
    {
        return max(0.0001, (float) self::setting('redeem_value'));
    }

    /* ---------------------------------------------------------------- balances */

    public static function balance($customerId): int
    {
        if (!$customerId) {
            return 0;
        }

        self::expireFor($customerId);

        return (int) Txn::where('customer_id', $customerId)->sum('points');
    }

    private static function record($customerId, string $type, int $points, ?string $description, $orderId = null, $expiresAt = null): ?Txn
    {
        if ($points === 0 || !$customerId) {
            return null;
        }

        return DB::transaction(function () use ($customerId, $type, $points, $description, $orderId, $expiresAt) {
            $balance = (int) Txn::where('customer_id', $customerId)->lockForUpdate()->sum('points');

            return Txn::create([
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'type' => $type,
                'points' => $points,
                'balance_after' => $balance + $points,
                'description' => $description,
                'expires_at' => $expiresAt,
            ]);
        });
    }

    /** Admin manual adjustment (positive or negative). */
    public static function adjust($customerId, int $points, ?string $note = null): ?Txn
    {
        return self::record($customerId, 'adjust', $points, $note ?: 'Manual adjustment');
    }

    public static function signupBonus($customerId): void
    {
        $points = (int) self::setting('signup_bonus');

        if (self::enabled() && $points > 0 && !Txn::where('customer_id', $customerId)->where('type', 'bonus')->where('description', 'like', 'Welcome%')->exists()) {
            self::record($customerId, 'bonus', $points, 'Welcome bonus', null, self::expiry());
        }
    }

    private static function expiry()
    {
        $days = (int) self::setting('expiry_days');

        return $days > 0 ? now()->addDays($days) : null;
    }

    /** Lazily expire points whose earn entries are past their expiry (oldest points are spent first). */
    public static function expireFor($customerId): void
    {
        $expiredEarned = (int) Txn::where('customer_id', $customerId)->where('points', '>', 0)
            ->whereNotNull('expires_at')->where('expires_at', '<=', now())->sum('points');

        if ($expiredEarned <= 0) {
            return;
        }

        $spent = abs((int) Txn::where('customer_id', $customerId)->whereIn('type', ['redeem', 'reverse', 'adjust'])->where('points', '<', 0)->sum('points'));
        $refunded = (int) Txn::where('customer_id', $customerId)->where('type', 'refund')->sum('points');
        $alreadyExpired = abs((int) Txn::where('customer_id', $customerId)->where('type', 'expire')->sum('points'));

        $toExpire = $expiredEarned - max(0, $spent - $refunded) - $alreadyExpired;
        $balance = (int) Txn::where('customer_id', $customerId)->sum('points');
        $toExpire = min($toExpire, $balance);

        if ($toExpire > 0) {
            self::record($customerId, 'expire', -$toExpire, 'Points expired');
        }
    }

    /* ----------------------------------------------------------------- earning */

    public static function pointsFor(float $amount): int
    {
        $per = max(0.01, (float) self::setting('earn_per_amount'));

        return (int) floor(max(0, $amount) / $per * (float) self::setting('earn_points'));
    }

    public static function earnForOrder(Order $order): void
    {
        if (!self::enabled() || !$order->customer_id || $order->glow_points_earned > 0) {
            return;
        }

        $statuses = (array) self::setting('earn_statuses', ['completed']);

        if (!in_array($order->status, $statuses, true)) {
            return;
        }

        $base = self::setting('earn_base') === 'total'
            ? $order->total->amount()
            : $order->sub_total->amount() - $order->discount->amount();

        $points = self::pointsFor($base);

        if (!Txn::where('customer_id', $order->customer_id)->where('type', 'earn')->exists()) {
            $points += (int) self::setting('first_order_bonus');
        }

        if ($points > 0) {
            self::record($order->customer_id, 'earn', $points, "Earned on order #{$order->id}", $order->id, self::expiry());
            $order->forceFill(['glow_points_earned' => $points])->save();
        }
    }

    /** If a canceled order is reinstated, take the redeemed points back out again. */
    public static function reinstateForOrder(Order $order): void
    {
        if (!$order->customer_id || $order->glow_points_redeemed <= 0) {
            return;
        }

        $redeems = Txn::where('order_id', $order->id)->where('type', 'redeem')->count();
        $refunds = Txn::where('order_id', $order->id)->where('type', 'refund')->count();

        if ($refunds >= $redeems && $refunds > 0) {
            self::record($order->customer_id, 'redeem', -(int) $order->glow_points_redeemed, "Redeemed on order #{$order->id} (reinstated)", $order->id);
        }
    }

    /** Called when an order is canceled/refunded: give back redeemed points, take back earned ones. */
    public static function reverseForOrder(Order $order): void
    {
        if (!$order->customer_id) {
            return;
        }

        if ($order->glow_points_redeemed > 0 && Txn::where('order_id', $order->id)->where('type', 'refund')->count() < Txn::where('order_id', $order->id)->where('type', 'redeem')->count()) {
            self::record($order->customer_id, 'refund', (int) $order->glow_points_redeemed, "Refunded for order #{$order->id}", $order->id);
        }

        if ($order->glow_points_earned > 0 && Txn::where('order_id', $order->id)->where('type', 'reverse')->count() < Txn::where('order_id', $order->id)->where('type', 'earn')->count()) {
            self::record($order->customer_id, 'reverse', -(int) $order->glow_points_earned, "Reversed for order #{$order->id}", $order->id);
            $order->forceFill(['glow_points_earned' => 0])->save();
        }
    }

    /* -------------------------------------------------------------- redemption */

    /**
     * Maximum redeemable points for a given payable base amount and customer balance.
     */
    public static function maxPoints(float $base, int $balance): int
    {
        $capAmount = $base * ((float) self::setting('max_redeem_percent') / 100);
        $capPoints = (int) floor($capAmount / self::pointValue());

        return max(0, min($balance, $capPoints));
    }

    /** Points currently requested in session, clamped to what is valid right now. */
    public static function requestedPoints(): int
    {
        if (!self::enabled() || !auth()->check()) {
            return 0;
        }

        $requested = (int) session(self::SESSION_KEY, 0);

        if ($requested <= 0) {
            return 0;
        }

        $base = Cart::subTotal()->amount() - Cart::coupon()->value()->amount();
        $points = min($requested, self::maxPoints($base, self::balance(auth()->id())));

        return $points >= (int) self::setting('min_redeem') ? $points : 0;
    }

    public static function discountFor(int $points): float
    {
        return round($points * self::pointValue(), 2);
    }

    public static function cartDiscount(): float
    {
        return self::discountFor(self::requestedPoints());
    }

    public static function setRedemption(int $points): void
    {
        session([self::SESSION_KEY => max(0, $points)]);
    }

    public static function clearRedemption(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** Summary used by the checkout panel / account page. */
    public static function summary(): array
    {
        $enabled = self::enabled() && auth()->check();
        $balance = $enabled ? self::balance(auth()->id()) : 0;
        $base = Cart::subTotal()->amount() - Cart::coupon()->value()->amount();
        $applied = $enabled ? self::requestedPoints() : 0;

        return [
            'enabled' => $enabled,
            'name' => self::name(),
            'balance' => $balance,
            'balance_value' => self::discountFor($balance),
            'point_value' => self::pointValue(),
            'min_redeem' => (int) self::setting('min_redeem'),
            'max_points' => $enabled ? self::maxPoints($base, $balance) : 0,
            'max_percent' => (float) self::setting('max_redeem_percent'),
            'applied_points' => $applied,
            'applied_discount' => self::discountFor($applied),
            'will_earn' => self::pointsFor($base - self::discountFor($applied)),
        ];
    }

    /** Called right after an order is created: deduct redeemed points and persist on the order. */
    public static function redeemForOrder(Order $order, int $points, float $discount): void
    {
        if ($points > 0) {
            self::record($order->customer_id, 'redeem', -$points, "Redeemed on order #{$order->id}", $order->id);
            $order->forceFill(['glow_points_redeemed' => $points, 'glow_points_discount' => $discount])->save();
        }

        self::clearRedemption();
    }
}
