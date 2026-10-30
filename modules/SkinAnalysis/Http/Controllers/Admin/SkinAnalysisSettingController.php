<?php

namespace Modules\SkinAnalysis\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SkinAnalysisSettingController extends Controller
{
    public function index()
    {
        return view('skinanalysis::admin.settings', [
            'enabled' => setting('skin_ai_enabled') === null ? true : (bool) setting('skin_ai_enabled'),
            'model' => setting('skin_ai_model') ?: 'claude-sonnet-5-5',
            'hasKey' => (bool) setting('skin_ai_anthropic_key'),
            'disclaimer' => setting('skin_ai_disclaimer'),
            'count' => DB::table('skin_analyses')->count(),
            'recent' => DB::table('skin_analyses')->latest('id')->limit(15)->get(),
        ]);
    }

    public function update()
    {
        $d = request()->validate(['model' => 'nullable|string|max:60', 'disclaimer' => 'nullable|string|max:400', 'api_key' => 'nullable|string|max:200']);

        $save = [
            'skin_ai_enabled' => request()->boolean('enabled') ? 1 : 0,
            'skin_ai_model' => $d['model'] ?? '',
            'skin_ai_disclaimer' => $d['disclaimer'] ?? '',
        ];
        if (!empty($d['api_key'])) {
            $save['skin_ai_anthropic_key'] = $d['api_key'];
        }
        if (request()->boolean('clear_key')) {
            $save['skin_ai_anthropic_key'] = '';
        }
        setting($save);

        return back()->with('success', 'Skin analysis settings saved.');
    }
}
