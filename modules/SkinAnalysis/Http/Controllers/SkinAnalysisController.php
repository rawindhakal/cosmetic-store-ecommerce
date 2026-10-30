<?php

namespace Modules\SkinAnalysis\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Support\Money;

class SkinAnalysisController extends Controller
{
    private const LABELS = [
        'acne' => 'Acne & blemishes', 'redness' => 'Redness & irritation', 'spots' => 'Dark spots & pigmentation', 'tone' => 'Uneven skin tone',
        'darkcircles' => 'Dark circles', 'lines' => 'Fine lines', 'pores' => 'Enlarged pores & texture', 'oil' => 'Excess oil / shine', 'dry' => 'Dryness & dullness',
    ];

    public static function enabled(): bool
    {
        return setting('skin_ai_enabled') === null || (bool) setting('skin_ai_enabled');
    }

    public function index()
    {
        abort_unless(self::enabled(), 404);

        return view('skinanalysis::public.index', [
            'aiAvailable' => (bool) setting('skin_ai_anthropic_key'),
            'disclaimer' => setting('skin_ai_disclaimer') ?: 'This is a cosmetic skin-care guide, not a medical diagnosis. If you have painful, spreading or persistent skin problems, please see a dermatologist.',
        ]);
    }

    /** Products from the real catalogue that match the measured concerns. */
    public function recommend()
    {
        $concerns = collect(request('concerns', []))->filter(fn ($c) => isset($c['key'], self::LABELS[$c['key']]) && ($c['score'] ?? 0) >= 25)->sortByDesc('score')->take(5)->values();
        $keywords = config('fleetcart.modules.skinanalysis.config.keywords');

        $products = $this->catalogue();
        $groups = [];
        $used = [];

        foreach ($concerns as $c) {
            $kw = $keywords[$c['key']] ?? [];
            $scored = $products->map(function ($p) use ($kw) {
                $hay = strtolower($p->name);
                $desc = strtolower($p->description ?? '');
                $s = 0;
                foreach ($kw as $i => $k) {
                    if (str_contains($hay, $k)) {
                        $s += 5 - min(3, $i * 0.3);
                    } elseif (str_contains($desc, $k)) {
                        $s += 1;
                    }
                }
                return [$p, $s];
            })->filter(fn ($x) => $x[1] > 0)->sortByDesc(fn ($x) => $x[1])->values();

            $picked = [];
            foreach ($scored as [$p, $s]) {
                if (count($picked) >= 4) {
                    break;
                }
                if (isset($used[$p->id]) && count($scored) > 6) {
                    continue; // prefer variety across concerns
                }
                $picked[] = $this->card($p);
                $used[$p->id] = true;
            }

            if ($picked) {
                $groups[] = ['key' => $c['key'], 'label' => self::LABELS[$c['key']], 'score' => $c['score'], 'products' => $picked];
            }
        }

        $always = config('fleetcart.modules.skinanalysis.config.always');
        $spf = $products->filter(fn ($p) => collect($always)->contains(fn ($k) => str_contains(strtolower($p->name), $k)))->take(3)->map(fn ($p) => $this->card($p))->values();

        return response()->json([
            'groups' => $groups,
            'protect' => $spf,
            'foundation' => $this->foundation((string) request('shade'), (string) request('undertone')),
        ]);
    }

    private function catalogue()
    {
        $skinCats = DB::table('categories')->whereIn('slug', ['skincare', 'cosmetics', 'makeup'])->pluck('id');

        return DB::table('products')
            ->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))
            ->join('product_categories as pc', 'pc.product_id', '=', 'products.id')
            ->whereIn('pc.category_id', $skinCats)
            ->whereNull('products.deleted_at')->where('products.is_active', 1)->where('products.in_stock', 1)
            ->distinct()
            ->get(['products.id', 'products.slug', 'products.price', 'products.selling_price', 't.name', 't.description']);
    }

    private function card($p): array
    {
        $img = DB::table('entity_files')->join('files', 'files.id', '=', 'entity_files.file_id')
            ->where('entity_type', 'Modules\\Product\\Entities\\Product')->where('entity_id', $p->id)->where('zone', 'base_image')->value('files.path');
        $hasVariants = DB::table('product_variants')->where('product_id', $p->id)->whereNull('deleted_at')->exists();

        return [
            'id' => $p->id, 'name' => $p->name, 'url' => route('products.show', ['slug' => $p->slug]),
            'image' => $img ? asset('storage/' . $img) : null,
            'price' => Money::inDefaultCurrency($p->selling_price)->convertToCurrentCurrency()->format(),
            'mrp' => $p->price > $p->selling_price ? Money::inDefaultCurrency($p->price)->convertToCurrentCurrency()->format() : null,
            'simple' => !$hasVariants,
        ];
    }

    /** Match a foundation variant ("Fair 01" ... "Deep 05") to the measured skin tone. */
    private function foundation(string $shade, string $undertone): ?array
    {
        if ($shade === '') {
            return null;
        }

        $rows = DB::table('product_variants as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'p.id')->where('t.locale', 'en'))
            ->whereNull('v.deleted_at')->whereNull('p.deleted_at')->where('v.is_active', 1)->where('v.in_stock', 1)->where('p.is_active', 1)
            ->where(fn ($q) => $q->where('t.name', 'like', '%Foundation%')->orWhere('t.name', 'like', '%Cushion%'))
            ->where('v.name', $shade)
            ->get(['p.id', 'p.slug', 'p.price', 'p.selling_price', 't.name', 'v.name as variant', 'v.uid']);

        return $rows->take(4)->map(function ($r) use ($shade) {
            $card = $this->card($r);
            $card['url'] = route('products.show', ['slug' => $r->slug, 'variant' => $r->uid]);
            $card['variant'] = $r->variant;
            $card['price'] = Money::inDefaultCurrency($r->selling_price)->convertToCurrentCurrency()->format();

            return $card;
        })->values()->all();
    }

    public function save()
    {
        $d = request()->validate([
            'overall' => 'required|integer|min:0|max:100', 'skin_type' => 'nullable|string|max:30', 'shade' => 'nullable|string|max:40',
            'undertone' => 'nullable|string|max:20', 'concerns' => 'required|array',
        ]);

        DB::table('skin_analyses')->insert([
            'user_id' => auth()->id(), 'overall_score' => $d['overall'], 'skin_type' => $d['skin_type'] ?? null, 'tone_shade' => $d['shade'] ?? null,
            'undertone' => $d['undertone'] ?? null,
            'concerns' => json_encode(collect($d['concerns'])->map(fn ($c) => ['key' => $c['key'] ?? '', 'score' => (int) ($c['score'] ?? 0)])->values()),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['saved' => true]);
    }

    public function history()
    {
        return view('skinanalysis::public.history', [
            'rows' => DB::table('skin_analyses')->where('user_id', auth()->id())->latest('id')->paginate(10),
            'labels' => self::LABELS,
        ]);
    }

    /**
     * Optional second opinion from a vision LLM (only if an admin added an Anthropic API key
     * and the visitor ticked the consent box). The photo is sent only for this one request.
     */
    public function aiReview()
    {
        $key = setting('skin_ai_anthropic_key');
        abort_unless($key && self::enabled(), 404);

        $d = request()->validate(['image' => 'required|string', 'metrics' => 'nullable|string|max:2000']);
        if (!preg_match('#^data:image/(jpeg|png);base64,(.+)$#', $d['image'], $m) || strlen($m[2]) > 3_500_000) {
            return response()->json(['message' => 'Invalid image.'], 422);
        }

        $prompt = "You are a skincare assistant. Look at this face photo and the measurements from an automated analysis (" . ($d['metrics'] ?? 'none') . "). "
            . "Reply in 5 short bullet points: (1) which skin concerns you can visually confirm, (2) any measurement that looks wrong because of lighting or makeup, (3) two gentle skincare habits. "
            . "Do NOT diagnose diseases; if something looks medical, advise seeing a dermatologist.";

        $r = Http::withHeaders(['x-api-key' => $key, 'anthropic-version' => '2023-06-01'])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => setting('skin_ai_model') ?: 'claude-sonnet-5-5', 'max_tokens' => 500,
            'messages' => [['role' => 'user', 'content' => [
                ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => 'image/' . $m[1], 'data' => $m[2]]],
                ['type' => 'text', 'text' => $prompt],
            ]]],
        ]);

        if (!$r->ok()) {
            return response()->json(['message' => 'AI review is unavailable right now.'], 502);
        }

        return response()->json(['text' => $r->json('content.0.text')]);
    }
}
