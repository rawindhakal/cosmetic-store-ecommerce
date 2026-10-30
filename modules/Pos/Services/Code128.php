<?php

namespace Modules\Pos\Services;

/**
 * Minimal Code 128 (sets B and C) SVG generator so barcodes print without any JS/CDN library.
 */
class Code128
{
    private const PATTERNS = [
        "212222","222122","222221","121223","121322","131222","122213","122312","132212","221213","221312","231212","112232","122132","122231","113222","123122","123221","223211","221132",
        "221231","213212","223112","312131","311222","321122","321221","312212","322112","322211","212123","212321","232121","111323","131123","131321","112313","132113","132311","211313",
        "231113","231311","112133","112331","132131","113123","113321","133121","313121","211331","231131","213113","213311","213131","311123","311321","331121","312113","312311","332111",
        "314111","221411","431111","111224","111422","121124","121421","141122","141221","112214","112412","122114","122411","142112","142211","241211","221114","413111","241112","134111",
        "111242","121142","121241","114212","124112","124211","411212","421112","421211","212141","214121","412121","111143","111341","131141","114113","114311","411113","411311","113141",
        "114131","311141","411131","211412","211214","211232","2331112",
    ];

    public static function codes(string $text): array
    {
        $len = strlen($text);

        if ($len >= 2 && $len % 2 === 0 && ctype_digit($text)) {
            $codes = [105];
            for ($i = 0; $i < $len; $i += 2) {
                $codes[] = (int) substr($text, $i, 2);
            }
        } else {
            $codes = [104];
            foreach (str_split($text) as $ch) {
                $o = ord($ch);
                $codes[] = ($o >= 32 && $o <= 126) ? $o - 32 : 0;
            }
        }

        $sum = $codes[0];
        foreach (array_slice($codes, 1) as $i => $c) {
            $sum += $c * ($i + 1);
        }
        $codes[] = $sum % 103;
        $codes[] = 106;

        return $codes;
    }

    /** @return string inline <svg> markup */
    public static function svg(string $text, int $height = 40, float $module = 1.6, bool $showText = false): string
    {
        $bars = '';
        $x = 10 * $module;

        foreach (self::codes($text) as $code) {
            $pattern = self::PATTERNS[$code];
            foreach (str_split($pattern) as $i => $w) {
                $width = $w * $module;
                if ($i % 2 === 0) {
                    $bars .= '<rect x="' . round($x, 2) . '" y="0" width="' . round($width, 2) . '" height="' . $height . '"/>';
                }
                $x += $width;
            }
        }

        $total = round($x + 10 * $module, 2);
        $textH = $showText ? 14 : 0;
        $label = $showText
            ? '<text x="' . ($total / 2) . '" y="' . ($height + 12) . '" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="11">' . e($text) . '</text>'
            : '';

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . ($height + $textH) . '" preserveAspectRatio="none" style="width:100%;height:auto;max-height:' . ($height + $textH) . 'px" fill="#000">' . $bars . $label . '</svg>';
    }

    /** EAN-13 compatible in-store barcode generator (prefix 200 = in-store use). */
    public static function generateEan13(int $seed): string
    {
        $digits = '200' . str_pad((string) $seed, 9, '0', STR_PAD_LEFT);
        $digits = substr($digits, 0, 12);
        $sum = 0;
        foreach (str_split($digits) as $i => $d) {
            $sum += $d * ($i % 2 ? 3 : 1);
        }

        return $digits . ((10 - $sum % 10) % 10);
    }
}
