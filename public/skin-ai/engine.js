/*
 * EmpressDreams skin-analysis engine (runs entirely in the browser).
 *
 * Models / engines
 *   1. MediaPipe Face Landmarker (Google, Apache-2.0) - 478-point face mesh used to locate the face and map skin regions.
 *   2. Colour-science engine  - CIELAB skin tone, ITA angle, undertone, redness, dullness.
 *   3. Blemish detector       - local-contrast blob detection on the a* (redness) channel  -> acne / blemishes.
 *   4. Pigmentation detector  - local-contrast blob detection on the L* (lightness) channel -> dark spots, uneven tone.
 *   5. Texture engine         - high-pass energy (pores / roughness) and directional gradients (fine lines).
 *   6. Shine engine           - specular highlight share in the T-zone vs cheeks -> oiliness / skin type.
 *   7. Under-eye engine       - lightness + hue difference under the eyes vs cheeks -> dark circles.
 * These are image-analysis algorithms, not a medical classifier: results are cosmetic guidance only.
 */
import {FaceLandmarker, FilesetResolver} from './vision_bundle.mjs';

const BASE = new URL('.', import.meta.url).href;
export const WORK_FACE_WIDTH = 320;

export async function createLandmarker() {
    const fileset = await FilesetResolver.forVisionTasks(BASE + 'wasm');
    const opts = (delegate) => ({
        baseOptions: {modelAssetPath: BASE + 'face_landmarker.task', delegate},
        runningMode: 'IMAGE', numFaces: 1, minFaceDetectionConfidence: 0.5, minFacePresenceConfidence: 0.5,
    });
    try { return await FaceLandmarker.createFromOptions(fileset, opts('GPU')); }
    catch (e) { return await FaceLandmarker.createFromOptions(fileset, opts('CPU')); }
}

/* ---------------------------------------------------------------- landmark sets */
const OVAL = [10,338,297,332,284,251,389,356,454,323,361,288,397,365,379,378,400,377,152,148,176,149,150,136,172,58,132,93,234,127,162,21,54,103,67,109];
const EYE_L = [362,382,381,380,374,373,390,249,263,466,388,387,386,385,384,398];   // image-right eye
const EYE_R = [33,7,163,144,145,153,154,155,133,173,157,158,159,160,161,246];      // image-left eye
const LIPS = [61,146,91,181,84,17,314,405,321,375,291,409,270,269,267,0,37,39,40,185];
const BROW_L = [276,283,282,295,285,300,293,334,296,336];
const BROW_R = [46,53,52,65,55,70,63,105,66,107];
const FOREHEAD = [54,103,67,109,10,338,297,332,284,300,293,334,296,336,9,107,66,105,63,70];
const NEEDED = new Set([...OVAL, ...EYE_L, ...EYE_R, ...LIPS, ...BROW_L, ...BROW_R, ...FOREHEAD, 234, 454, 205, 425, 4, 6, 2, 168, 17, 152, 200, 145, 374]);

/** Rough landmark layout inside the on-screen guide oval, used when the face model cannot run. */
function fallbackLandmarks() {
    const lm = new Array(478).fill(null).map(() => ({x: .5, y: .5, z: 0}));
    const cx = .5, cy = .5, rx = .27, ry = .36;
    OVAL.forEach((id, i) => { const a = (i / OVAL.length) * Math.PI * 2 - Math.PI / 2; lm[id] = {x: cx + rx * Math.cos(a), y: cy + ry * Math.sin(a), z: 0}; });
    const P = (id, x, y) => (lm[id] = {x, y, z: 0});
    P(234, cx - rx, cy); P(454, cx + rx, cy); P(205, cx - .15, cy + .08); P(425, cx + .15, cy + .08);
    P(4, cx, cy + .06); P(6, cx, cy - .03); P(2, cx, cy + .09); P(168, cx, cy - .08); P(17, cx, cy + .21); P(152, cx, cy + ry); P(200, cx, cy + .3);
    const eye = (arr, ex) => arr.forEach((id, i) => { const a = (i / arr.length) * Math.PI * 2; P(id, ex + .06 * Math.cos(a), cy - .09 + .018 * Math.sin(a)); });
    eye(EYE_R, cx - .15); eye(EYE_L, cx + .15); P(145, cx - .15, cy - .075); P(374, cx + .15, cy - .075);
    LIPS.forEach((id, i) => { const a = (i / LIPS.length) * Math.PI * 2; P(id, cx + .09 * Math.cos(a), cy + .2 + .03 * Math.sin(a)); });
    const brow = (arr, bx) => arr.forEach((id, i) => P(id, bx - .09 + (i / (arr.length - 1)) * .18, cy - .15 - .02 * Math.sin((i / (arr.length - 1)) * Math.PI)));
    brow(BROW_R, cx - .15); brow(BROW_L, cx + .15);
    FOREHEAD.forEach((id, i) => { if (!lm[id] || (lm[id].x === .5 && lm[id].y === .5)) P(id, cx - .2 + (i / (FOREHEAD.length - 1)) * .4, cy - .27 + .06 * Math.abs(Math.sin(i))); });
    P(10, cx, cy - ry);
    return lm;
}

export function faceBox(lm) {
    let x0 = 1, y0 = 1, x1 = 0, y1 = 0;
    OVAL.forEach(i => { const p = lm[i]; x0 = Math.min(x0, p.x); y0 = Math.min(y0, p.y); x1 = Math.max(x1, p.x); y1 = Math.max(y1, p.y); });
    return {x0, y0, x1, y1, w: x1 - x0, h: y1 - y0, cx: (x0 + x1) / 2, cy: (y0 + y1) / 2};
}

/* ---------------------------------------------------------------- colour + filters */
function rgb2lab(r, g, b) {
    r /= 255; g /= 255; b /= 255;
    r = r > .04045 ? Math.pow((r + .055) / 1.055, 2.4) : r / 12.92; g = g > .04045 ? Math.pow((g + .055) / 1.055, 2.4) : g / 12.92; b = b > .04045 ? Math.pow((b + .055) / 1.055, 2.4) : b / 12.92;
    let x = (r * .4124564 + g * .3575761 + b * .1804375) / .95047, y = r * .2126729 + g * .7151522 + b * .072175, z = (r * .0193339 + g * .119192 + b * .9503041) / 1.08883;
    const f = t => t > .008856 ? Math.cbrt(t) : 7.787 * t + 16 / 116; x = f(x); y = f(y); z = f(z);
    return [116 * y - 16, 500 * (x - y), 200 * (y - z)];
}
function lab2hex(L, a, b) {
    let y = (L + 16) / 116, x = a / 500 + y, z = y - b / 200;
    const f = t => (t ** 3 > .008856 ? t ** 3 : (t - 16 / 116) / 7.787);
    x = f(x) * .95047; y = f(y); z = f(z) * 1.08883;
    let r = x * 3.2404542 - y * 1.5371385 - z * .4985314, g = -x * .969266 + y * 1.8760108 + z * .041556, bl = x * .0556434 - y * .2040259 + z * 1.0572252;
    const c = t => { t = t <= .0031308 ? 12.92 * t : 1.055 * Math.pow(t, 1 / 2.4) - .055; return Math.max(0, Math.min(255, Math.round(t * 255))); };
    return '#' + [c(r), c(g), c(bl)].map(v => v.toString(16).padStart(2, '0')).join('');
}
/** Box blur with running sums (edges clamped). */
function boxBlur(src, w, h, r) {
    const tmp = new Float32Array(w * h), out = new Float32Array(w * h), n = 2 * r + 1;
    for (let y = 0; y < h; y++) {
        let s = 0; const o = y * w;
        for (let x = -r; x <= r; x++) s += src[o + Math.min(w - 1, Math.max(0, x))];
        for (let x = 0; x < w; x++) { tmp[o + x] = s / n; s += src[o + Math.min(w - 1, x + r + 1)] - src[o + Math.max(0, x - r)]; }
    }
    for (let x = 0; x < w; x++) {
        let s = 0;
        for (let y = -r; y <= r; y++) s += tmp[Math.min(h - 1, Math.max(0, y)) * w + x];
        for (let y = 0; y < h; y++) { out[y * w + x] = s / n; s += tmp[Math.min(h - 1, y + r + 1) * w + x] - tmp[Math.max(0, y - r) * w + x]; }
    }
    return out;
}
/** Blur that ignores pixels outside the mask (normalised convolution). */
function maskedBlur(v, m, w, h, r) {
    const vm = new Float32Array(w * h); for (let i = 0; i < vm.length; i++) vm[i] = m[i] ? v[i] : 0;
    const num = boxBlur(vm, w, h, r), den = boxBlur(Float32Array.from(m, x => (x ? 1 : 0)), w, h, r), out = new Float32Array(w * h);
    for (let i = 0; i < out.length; i++) out[i] = den[i] > .04 ? num[i] / den[i] : v[i];
    return out;
}
const clamp = (v, a = 0, b = 100) => Math.max(a, Math.min(b, v));
function median(arr) { if (!arr.length) return 0; const s = Float32Array.from(arr).sort(); return s[s.length >> 1]; }
function percentile(arr, p) { if (!arr.length) return 0; const s = Float32Array.from(arr).sort(); return s[Math.min(s.length - 1, Math.floor(s.length * p))]; }
function std(arr) { if (arr.length < 2) return 0; let m = 0; for (const v of arr) m += v; m /= arr.length; let s = 0; for (const v of arr) s += (v - m) ** 2; return Math.sqrt(s / arr.length); }

/** Connected components (8-neighbour) of a binary map. Returns blobs with centroid/area/bbox. */
function components(bin, w, h, minA, maxA, maxSide) {
    const seen = new Uint8Array(w * h), blobs = [], stack = new Int32Array(w * h);
    for (let i = 0; i < w * h; i++) {
        if (!bin[i] || seen[i]) continue;
        let sp = 0, area = 0, sx = 0, sy = 0, x0 = w, y0 = h, x1 = 0, y1 = 0; stack[sp++] = i; seen[i] = 1;
        while (sp) {
            const p = stack[--sp], x = p % w, y = (p / w) | 0; area++; sx += x; sy += y;
            if (x < x0) x0 = x; if (x > x1) x1 = x; if (y < y0) y0 = y; if (y > y1) y1 = y;
            for (let dy = -1; dy <= 1; dy++) for (let dx = -1; dx <= 1; dx++) {
                const nx = x + dx, ny = y + dy; if (nx < 0 || ny < 0 || nx >= w || ny >= h) continue;
                const q = ny * w + nx; if (bin[q] && !seen[q]) { seen[q] = 1; stack[sp++] = q; }
            }
        }
        const bw = x1 - x0 + 1, bh = y1 - y0 + 1;
        if (area >= minA && area <= maxA && Math.max(bw, bh) <= maxSide && area / (bw * bh) >= .35) blobs.push({x: sx / area, y: sy / area, area, r: Math.max(bw, bh) / 2 + 3});
    }
    return blobs;
}

/* ---------------------------------------------------------------- main analysis */
/**
 * @param {HTMLCanvasElement|HTMLImageElement|HTMLVideoElement} source full-resolution capture
 * @param {Array|null} landmarks normalised landmarks from the Face Landmarker (null -> guide-oval fallback)
 */
export function analyze(source, landmarks) {
    const fallback = !landmarks;
    const lm0 = landmarks || fallbackLandmarks();
    const sw = source.videoWidth || source.naturalWidth || source.width, sh = source.videoHeight || source.naturalHeight || source.height;
    const fw0 = Math.hypot((lm0[454].x - lm0[234].x) * sw, (lm0[454].y - lm0[234].y) * sh);
    const scale = Math.min(1.6, WORK_FACE_WIDTH / Math.max(40, fw0));
    const W = Math.round(sw * scale), H = Math.round(sh * scale);

    const canvas = document.createElement('canvas'); canvas.width = W; canvas.height = H;
    const ctx = canvas.getContext('2d', {willReadFrequently: true}); ctx.imageSmoothingQuality = 'high'; ctx.drawImage(source, 0, 0, W, H);
    const img = ctx.getImageData(0, 0, W, H).data;
    const P = id => ({x: lm0[id].x * W, y: lm0[id].y * H});
    const fw = Math.hypot(P(454).x - P(234).x, P(454).y - P(234).y);

    // masks ---------------------------------------------------------------------
    const mcv = document.createElement('canvas'); mcv.width = W; mcv.height = H; const mctx = mcv.getContext('2d', {willReadFrequently: true});
    const maskOf = draw => { mctx.clearRect(0, 0, W, H); mctx.fillStyle = '#fff'; mctx.strokeStyle = '#fff'; draw(mctx); const d = mctx.getImageData(0, 0, W, H).data, m = new Uint8Array(W * H); for (let i = 0; i < m.length; i++) m[i] = d[i * 4 + 3] > 128 ? 1 : 0; return m; };
    const poly = (c, ids) => { c.beginPath(); ids.forEach((id, i) => { const p = P(id); i ? c.lineTo(p.x, p.y) : c.moveTo(p.x, p.y); }); c.closePath(); };
    const ell = (c, cx, cy, rx, ry) => { c.beginPath(); c.ellipse(cx, cy, rx, ry, 0, 0, Math.PI * 2); c.fill(); };

    const faceM = maskOf(c => { poly(c, OVAL); c.fill(); });
    const featM = maskOf(c => {
        c.lineWidth = fw * .05; c.lineJoin = 'round';
        [EYE_L, EYE_R, BROW_L, BROW_R, LIPS].forEach(ids => { poly(c, ids); c.fill(); c.stroke(); });
    });
    const L = new Float32Array(W * H), A = new Float32Array(W * H), B = new Float32Array(W * H), skin = new Uint8Array(W * H);
    for (let i = 0; i < W * H; i++) {
        const r = img[i * 4], g = img[i * 4 + 1], b = img[i * 4 + 2]; const lab = rgb2lab(r, g, b); L[i] = lab[0]; A[i] = lab[1]; B[i] = lab[2];
        const cr = 128 + .5 * r - .418688 * g - .081312 * b, cb = 128 - .168736 * r - .331264 * g + .5 * b;
        skin[i] = faceM[i] && !featM[i] && cr >= 128 && cr <= 186 && cb >= 68 && cb <= 142 ? 1 : 0;
    }
    const inter = m => { const o = new Uint8Array(W * H); for (let i = 0; i < o.length; i++) o[i] = m[i] && skin[i] ? 1 : 0; return o; };
    const rg = {
        forehead: inter(maskOf(c => { poly(c, FOREHEAD); c.fill(); })),
        cheekL: inter(maskOf(c => { const p = P(205); ell(c, p.x, p.y, fw * .13, fw * .12); })),
        cheekR: inter(maskOf(c => { const p = P(425); ell(c, p.x, p.y, fw * .13, fw * .12); })),
        nose: inter(maskOf(c => { const a = P(168), b = P(2); ell(c, (a.x + b.x) / 2, (a.y + b.y) / 2, fw * .08, Math.hypot(b.x - a.x, b.y - a.y) / 2); })),
        chin: inter(maskOf(c => { const a = P(17), b = P(152); ell(c, (a.x + b.x) / 2, (a.y + b.y) / 2 + fw * .02, fw * .15, fw * .08); })),
    };
    const underM = (id) => maskOf(c => { const p = P(id); ell(c, p.x, p.y + fw * .055, fw * .1, fw * .035); });
    const eyeCover = maskOf(c => { c.lineWidth = fw * .018; [EYE_L, EYE_R].forEach(ids => { poly(c, ids); c.fill(); c.stroke(); }); });
    const under = new Uint8Array(W * H), uL = underM(145), uR = underM(374);
    for (let i = 0; i < under.length; i++) under[i] = (uL[i] || uR[i]) && !eyeCover[i] && faceM[i] ? 1 : 0;
    const cheeks = new Uint8Array(W * H), tzone = new Uint8Array(W * H), acneZone = new Uint8Array(W * H), lineZone = rg.forehead;
    for (let i = 0; i < cheeks.length; i++) {
        cheeks[i] = rg.cheekL[i] || rg.cheekR[i] ? 1 : 0; tzone[i] = rg.forehead[i] || rg.nose[i] || rg.chin[i] ? 1 : 0;
        acneZone[i] = skin[i] && (cheeks[i] || tzone[i]) ? 1 : 0;
    }
    const pick = (arr, m) => { const o = []; for (let i = 0; i < arr.length; i++) if (m[i]) o.push(arr[i]); return o; };
    const count = m => { let n = 0; for (let i = 0; i < m.length; i++) n += m[i]; return n; };
    const nSkin = Math.max(1, count(acneZone));

    // quality -------------------------------------------------------------------
    const faceL = pick(L, skin), meanL = faceL.reduce((a, b) => a + b, 0) / Math.max(1, faceL.length);
    const lapVals = []; for (let y = 1; y < H - 1; y += 2) for (let x = 1; x < W - 1; x += 2) { const i = y * W + x; if (skin[i]) lapVals.push(Math.abs(4 * L[i] - L[i - 1] - L[i + 1] - L[i - W] - L[i + W])); }
    const sharp = std(lapVals);
    const lL = median(pick(L, rg.cheekL)), lR = median(pick(L, rg.cheekR));
    const yaw = (P(4).x - (P(234).x + P(454).x) / 2) / fw;
    const quality = {issues: [], fallback, meanL, sharp, yaw};
    if (fallback) quality.issues.push('Face model unavailable - used the on-screen guide, so accuracy is lower.');
    if (meanL < 38) quality.issues.push('Too dark - face a window or soft daylight.');
    if (meanL > 82) quality.issues.push('Too bright / overexposed - move away from direct light.');
    if (Math.abs(lL - lR) > 12) quality.issues.push('Uneven lighting between the two cheeks.');
    if (sharp < 1.3) quality.issues.push('Image looks blurry - hold still and wipe the lens.');
    if (Math.abs(yaw) > .09) quality.issues.push('Face the camera straight on.');
    if (count(skin) < fw * fw * .25) quality.issues.push('Only a small part of the face was readable.');

    // blurs --------------------------------------------------------------------
    const Lbg = maskedBlur(L, acneZone, W, H, 11), Abg = maskedBlur(A, acneZone, W, H, 11), Lbg2 = maskedBlur(L, acneZone, W, H, 30);
    const Lfine = boxBlur(L, W, H, 1);

    // 1) blemishes (redness blobs) ---------------------------------------------
    const bin = new Uint8Array(W * H);
    for (let i = 0; i < bin.length; i++) bin[i] = acneZone[i] && A[i] - Abg[i] > 5.2 && L[i] < Lbg[i] + 4 ? 1 : 0;
    const acneBlobs = components(bin, W, H, 9, 380, 38);
    const acneArea = acneBlobs.reduce((a, b) => a + b.area, 0) / nSkin;
    const acne = clamp(100 * (1 - Math.exp(-(acneBlobs.length / 10 + acneArea * 35))));

    // 2) pigmentation (dark, non-red blobs) ------------------------------------
    const bin2 = new Uint8Array(W * H);
    for (let i = 0; i < bin2.length; i++) bin2[i] = acneZone[i] && !rg.nose[i] && Lbg[i] - L[i] > 6.5 && A[i] - Abg[i] < 3.2 ? 1 : 0;
    const spotBlobs = components(bin2, W, H, 12, 650, 46);
    const spotArea = spotBlobs.reduce((a, b) => a + b.area, 0) / nSkin;
    const spots = clamp(100 * (1 - Math.exp(-(spotBlobs.length / 12 + spotArea * 28))));

    // 3) tone unevenness -------------------------------------------------------
    const lowFreq = []; for (let i = 0; i < L.length; i++) if (cheeks[i] || rg.forehead[i]) lowFreq.push(L[i] - Lbg2[i]);
    const toneStd = std(lowFreq); const tone = clamp((toneStd - 3.6) / 4.5 * 100);

    // 4) redness ----------------------------------------------------------------
    const aCheek = median(pick(A, cheeks)), aFore = median(pick(A, rg.forehead)), bCheek = median(pick(B, cheeks)), lCheek = median(pick(L, cheeks));
    const redness = clamp(Math.max((aCheek - 16) / 6.5 * 100, (aCheek - aFore - 2) * 12));

    // 5) pores / texture --------------------------------------------------------
    const hp = []; for (let i = 0; i < L.length; i++) if ((cheeks[i] || rg.nose[i]) && skin[i]) { const d = L[i] - Lfine[i]; if (Math.abs(d) < 8) hp.push(d); }
    const hpStd = std(hp); const pores = clamp((hpStd - 1.1) / 2.4 * 100);

    // 6) fine lines (horizontal forehead creases) ------------------------------
    let gy = 0, gx = 0, gn = 0;
    for (let y = 2; y < H - 2; y++) for (let x = 2; x < W - 2; x++) { const i = y * W + x; if (!lineZone[i]) continue; gy += Math.abs(Lfine[i + W] - Lfine[i - W]); gx += Math.abs(Lfine[i + 1] - Lfine[i - 1]); gn++; }
    gy /= Math.max(1, gn); gx /= Math.max(1, gn);
    const lines = clamp(((gy / (gx + .15)) - 1.0) / .75 * 55 + (gy - 1.1) * 18);

    // 7) shine / oil ------------------------------------------------------------
    const hiT = pick(L, tzone), hiC = pick(L, cheeks), refL = lCheek;
    const shineT = hiT.filter(v => v > refL + 16).length / Math.max(1, hiT.length), shineC = hiC.filter(v => v > refL + 16).length / Math.max(1, hiC.length);
    const oil = clamp(shineT * 300 + Math.max(0, median(hiT) - refL - 5) * 1.5);
    const cheekOil = clamp(shineC * 300);

    // 8) dryness / dullness -------------------------------------------------------
    const cA = pick(A, cheeks), cB = pick(B, cheeks), chroma = median(cA.map((a, i) => Math.hypot(a, cB[i])));
    const dull = clamp((20 - chroma) / 9 * 100);
    const dry = clamp((dull * .45 + pores * .35 + Math.max(0, 30 - oil) * .7) * (1 - oil / 160));

    // 9) dark circles ------------------------------------------------------------
    const lUnder = median(pick(L, under)), bUnder = median(pick(B, under)), aUnder = median(pick(A, under));
    const dcDiff = lCheek - lUnder, dcHue = (aUnder - aCheek) * .6 + (bCheek - bUnder) * .6;
    const darkcircles = clamp((dcDiff - 4) / 9 * 100 + Math.max(0, dcHue) * 3);

    // tone / shade ----------------------------------------------------------------
    const ita = Math.atan2(lCheek - 50, bCheek) * 180 / Math.PI, hue = Math.atan2(bCheek, aCheek) * 180 / Math.PI;
    const tier = ita > 55 ? ['Very light', 'Fair 01'] : ita > 41 ? ['Light', 'Light 02'] : ita > 28 ? ['Intermediate', 'Medium 03'] : ita > 10 ? ['Tan', 'Tan 04'] : ['Deep', 'Deep 05'];
    const undertone = hue < 50 ? 'Cool' : hue > 60 ? 'Warm' : 'Neutral';

    const skinType = oil >= 55 && cheekOil >= 35 ? 'Oily' : oil >= 35 && cheekOil < 22 ? 'Combination' : dry >= 50 && oil < 32 ? 'Dry' : 'Normal';

    const level = s => (s < 25 ? 'Low' : s < 55 ? 'Moderate' : 'High');
    const R = v => Math.round(v);
    const concerns = [
        {key: 'acne', label: 'Acne & blemishes', score: R(acne), detail: `${acneBlobs.length} red blemish area(s) found`},
        {key: 'redness', label: 'Redness & irritation', score: R(redness), detail: `Cheek redness index ${aCheek.toFixed(1)}`},
        {key: 'spots', label: 'Dark spots & pigmentation', score: R(spots), detail: `${spotBlobs.length} dark spot(s) found`},
        {key: 'tone', label: 'Uneven skin tone', score: R(tone), detail: `Tone variation ${toneStd.toFixed(1)}`},
        {key: 'darkcircles', label: 'Dark circles', score: R(darkcircles), detail: `Under-eye is ${dcDiff.toFixed(1)} shades darker than cheeks`},
        {key: 'lines', label: 'Fine lines', score: R(lines), detail: 'Forehead crease analysis'},
        {key: 'pores', label: 'Enlarged pores & texture', score: R(pores), detail: `Texture energy ${hpStd.toFixed(2)}`},
        {key: 'oil', label: 'Excess oil / shine', score: R(oil), detail: `${(shineT * 100).toFixed(0)}% of the T-zone is shiny`},
        {key: 'dry', label: 'Dryness & dullness', score: R(dry), detail: `Skin radiance ${chroma.toFixed(1)}`},
    ].map(c => ({...c, level: level(c.score)})).sort((a, b) => b.score - a.score);

    const wts = {acne: .2, redness: .12, spots: .14, tone: .1, darkcircles: .1, lines: .1, pores: .1, oil: .07, dry: .07};
    const issue = concerns.reduce((a, c) => a + c.score * wts[c.key], 0) / Object.values(wts).reduce((a, b) => a + b, 0);
    const overall = R(clamp(100 - issue * 1.25));

    // region outlines for the overlay (in work-canvas pixels)
    const outline = ids => ids.map(id => [P(id).x, P(id).y]);
    return {
        canvas, W, H, overall, skinType, concerns, quality,
        tone: {tier: tier[0], shade: tier[1], undertone, ita: R(ita), hue: R(hue), hex: lab2hex(lCheek, aCheek, bCheek)},
        blobs: {acne: acneBlobs, spots: spotBlobs},
        outlines: {face: outline(OVAL), forehead: outline(FOREHEAD)},
        regions: {cheekL: P(205), cheekR: P(425), fw},
        shine: {t: shineT, c: shineC},
        metrics: `skin type ${skinType}; tone ${tier[0]}/${undertone}; ` + concerns.map(c => `${c.label} ${c.score}/100`).join(', '),
    };
}
