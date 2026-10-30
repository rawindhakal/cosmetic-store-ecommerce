@if (\Modules\GlowPoints\Services\GlowPoints::enabled())
    @auth
        <div
            x-data="{
                s: null, points: '', error: null, busy: false,
                token: document.querySelector('meta[name=csrf-token]')?.content || window.FleetCart?.csrfToken,
                async load() {
                    const r = await fetch('{{ route('glowpoints.summary') }}', { headers: { Accept: 'application/json' } });
                    this.s = await r.json();
                    this.points = this.s.applied_points || this.s.max_points || '';
                },
                async call(method, body) {
                    this.busy = true; this.error = null;
                    const r = await fetch('{{ route('glowpoints.redeem') }}', {
                        method, headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': this.token },
                        body: body ? JSON.stringify(body) : undefined,
                    });
                    const d = await r.json();
                    this.busy = false;
                    if (!r.ok) { this.error = d.message || 'Could not apply points.'; return; }
                    this.s = d.summary;
                    $store.cart.updateCart(d.cart);
                },
                apply() { this.call('POST', { points: parseInt(this.points || 0) }); },
                remove() { this.call('DELETE'); },
            }"
            x-init="load()"
            x-cloak
            class="coupon-wrap glowpoints-wrap"
        >
            <template x-if="s && s.enabled">
                <div>
                    <p style="margin:10px 0 6px">
                        <strong x-text="s.name"></strong>:
                        <span x-text="s.balance"></span> available
                        (worth <span x-text="formatCurrency(s.balance_value)"></span>)
                    </p>

                    <template x-if="s.balance >= s.min_redeem && s.max_points >= s.min_redeem">
                        <div class="d-flex">
                            <template x-if="s.applied_points === 0">
                                <div class="d-flex" style="width:100%">
                                    <input type="number" class="form-control" min="0" :max="s.max_points" x-model="points"
                                           :placeholder="`Up to ${s.max_points} points`" @keyup.enter.prevent="apply">
                                    <button type="button" class="btn btn-default btn-apply-coupon" :disabled="busy" @click.prevent="apply">Redeem</button>
                                </div>
                            </template>
                            <template x-if="s.applied_points > 0">
                                <div class="d-flex" style="width:100%;align-items:center;justify-content:space-between">
                                    <span><span x-text="s.applied_points"></span> points applied</span>
                                    <button type="button" class="btn btn-default" :disabled="busy" @click.prevent="remove">Remove</button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!(s.balance >= s.min_redeem && s.max_points >= s.min_redeem)">
                        <small>Minimum <span x-text="s.min_redeem"></span> points to redeem (up to <span x-text="s.max_percent"></span>% of the order).</small>
                    </template>

                    <template x-if="error"><span class="error-message" x-text="error"></span></template>

                    <small x-show="s.will_earn > 0" style="display:block;margin-top:6px">
                        You'll earn about <span x-text="s.will_earn"></span> <span x-text="s.name"></span> on this order.
                    </small>
                </div>
            </template>
        </div>
    @endauth
@endif
