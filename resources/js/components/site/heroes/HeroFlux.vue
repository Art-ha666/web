<script setup lang="ts">
import * as THREE from 'three';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AkButton from '../AkButton.vue';
import Icon from '../Icon.vue';

interface HeroData {
    badge?: string;
    eyebrow?: string;
    headline_line1?: string;
    headline_line2?: string;
    accent_word?: string;
    subhead?: string;
    primary_label?: string;
    primary_url?: string;
    secondary_label?: string;
    secondary_url?: string;
}

interface StatItem {
    value: string;
    prefix?: string | null;
    suffix?: string | null;
    label: string;
}

const props = defineProps<{ hero: HeroData; stats: StatItem[] }>();

const canvasEl = ref<HTMLCanvasElement | null>(null);
const reducedMotion = ref(false);

const line1Parts = computed(() => {
    const line = props.hero.headline_line1 ?? '';
    const accent = props.hero.accent_word ?? '';

    if (!accent || !line.includes(accent)) {
return [{ text: line, accent: false }];
}

    const [before, after] = line.split(accent);

    return [
        { text: before, accent: false },
        { text: accent, accent: true },
        { text: after, accent: false },
    ];
});

// ---- Three.js liquid gradient mesh ----
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.OrthographicCamera | null = null;
let mesh: THREE.Mesh | null = null;
let geometry: THREE.PlaneGeometry | null = null;
let material: THREE.ShaderMaterial | null = null;
let rafId = 0;
let startTime = 0;
let resizeObserver: ResizeObserver | null = null;

/** Read a CSS custom property and resolve it to a THREE.Color, falling back gracefully. */
function readColor(varName: string, fallback: string): THREE.Color {
    let raw = '';

    try {
        raw = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    } catch {
        raw = '';
    }

    const value = raw || fallback;

    try {
        return new THREE.Color(value);
    } catch {
        return new THREE.Color(fallback);
    }
}

const vertexShader = /* glsl */ `
    varying vec2 vUv;
    void main() {
        vUv = uv;
        gl_Position = vec4(position.xy, 0.0, 1.0);
    }
`;

const fragmentShader = /* glsl */ `
    precision highp float;

    varying vec2 vUv;

    uniform float uTime;
    uniform vec2 uResolution;
    uniform vec3 uColorA;
    uniform vec3 uColorB;
    uniform vec3 uColorC;
    uniform vec3 uBg;

    // Classic simplex-ish value noise built from smooth interpolated hashes.
    vec2 hash22(vec2 p) {
        p = vec2(dot(p, vec2(127.1, 311.7)), dot(p, vec2(269.5, 183.3)));
        return -1.0 + 2.0 * fract(sin(p) * 43758.5453123);
    }

    float gnoise(vec2 p) {
        vec2 i = floor(p);
        vec2 f = fract(p);
        vec2 u = f * f * (3.0 - 2.0 * f);
        float a = dot(hash22(i + vec2(0.0, 0.0)), f - vec2(0.0, 0.0));
        float b = dot(hash22(i + vec2(1.0, 0.0)), f - vec2(1.0, 0.0));
        float c = dot(hash22(i + vec2(0.0, 1.0)), f - vec2(0.0, 1.0));
        float d = dot(hash22(i + vec2(1.0, 1.0)), f - vec2(1.0, 1.0));
        return mix(mix(a, b, u.x), mix(c, d, u.x), u.y);
    }

    // Domain-warped fractal noise - gives that slow "liquid" flow.
    float fbm(vec2 p) {
        float total = 0.0;
        float amp = 0.55;
        float freq = 1.0;
        for (int i = 0; i < 5; i++) {
            total += gnoise(p * freq) * amp;
            freq *= 2.02;
            amp *= 0.5;
        }
        return total;
    }

    void main() {
        // Aspect-correct coordinates so the flow doesn't stretch.
        vec2 uv = vUv;
        vec2 p = uv;
        p.x *= uResolution.x / max(uResolution.y, 1.0);

        float t = uTime * 0.045;

        // Two layers of domain warping for an organic, marbled flow.
        vec2 q = vec2(
            fbm(p + vec2(0.0, t)),
            fbm(p + vec2(5.2, -t * 0.8) + 1.3)
        );

        vec2 r = vec2(
            fbm(p + 1.8 * q + vec2(1.7, 9.2) + 0.15 * t),
            fbm(p + 1.8 * q + vec2(8.3, 2.8) - 0.12 * t)
        );

        float f = fbm(p + 2.2 * r);

        // Build a smooth multi-stop gradient through the theme colors.
        float m = clamp(f * 0.5 + 0.5, 0.0, 1.0);
        float flow = clamp(length(q) * 0.65 + r.x * 0.35, 0.0, 1.0);

        vec3 col = mix(uColorA, uColorB, smoothstep(0.15, 0.65, m));
        col = mix(col, uColorC, smoothstep(0.5, 0.95, flow));

        // Soft bloom-like highlights riding the warp ridges.
        float ridge = smoothstep(0.55, 0.95, abs(r.y));
        col += uColorA * ridge * 0.18;

        // Sink the lower edge into the page background for a clean blend.
        float fade = smoothstep(0.0, 0.42, uv.y);
        col = mix(uBg, col, fade);

        // Vignette so copy stays legible and edges feel deep.
        vec2 vc = uv - 0.5;
        float vig = 1.0 - dot(vc, vc) * 0.85;
        col *= clamp(vig, 0.55, 1.0);

        // Gentle dither to kill banding on smooth gradients.
        float dither = (fract(sin(dot(gl_FragCoord.xy, vec2(12.9898, 78.233))) * 43758.5453) - 0.5) / 255.0;
        col += dither;

        gl_FragColor = vec4(col, 1.0);
    }
`;

function syncSize() {
    if (!renderer || !canvasEl.value || !material) {
return;
}

    const parent = canvasEl.value.parentElement;

    if (!parent) {
return;
}

    const w = parent.clientWidth || window.innerWidth;
    const h = parent.clientHeight || window.innerHeight;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    renderer.setPixelRatio(dpr);
    renderer.setSize(w, h, false);
    material.uniforms.uResolution.value.set(w * dpr, h * dpr);
}

function animate() {
    if (!renderer || !scene || !camera || !material) {
return;
}

    material.uniforms.uTime.value = (performance.now() - startTime) / 1000;
    renderer.render(scene, camera);
    rafId = requestAnimationFrame(animate);
}

function initThree() {
    if (!canvasEl.value) {
return;
}

    renderer = new THREE.WebGLRenderer({
        canvas: canvasEl.value,
        antialias: false,
        alpha: false,
        powerPreference: 'low-power',
    });
    renderer.setClearColor(0x000000, 0);

    scene = new THREE.Scene();
    camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);

    geometry = new THREE.PlaneGeometry(2, 2);
    material = new THREE.ShaderMaterial({
        vertexShader,
        fragmentShader,
        depthTest: false,
        depthWrite: false,
        uniforms: {
            uTime: { value: 0 },
            uResolution: { value: new THREE.Vector2(1, 1) },
            uColorA: { value: readColor('--ak-grad-1', '#28baf3') },
            uColorB: { value: readColor('--ak-grad-2', '#5778f8') },
            uColorC: { value: readColor('--ak-grad-3', '#7e2cfd') },
            uBg: { value: readColor('--ak-bg', '#07080d') },
        },
    });

    mesh = new THREE.Mesh(geometry, material);
    scene.add(mesh);

    syncSize();

    const parent = canvasEl.value.parentElement;

    if (parent && 'ResizeObserver' in window) {
        resizeObserver = new ResizeObserver(() => syncSize());
        resizeObserver.observe(parent);
    }

    window.addEventListener('resize', syncSize, { passive: true });

    startTime = performance.now();
    rafId = requestAnimationFrame(animate);
}

function disposeThree() {
    cancelAnimationFrame(rafId);
    rafId = 0;
    window.removeEventListener('resize', syncSize);

    if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
    }

    if (mesh && scene) {
scene.remove(mesh);
}

    geometry?.dispose();
    material?.dispose();
    renderer?.dispose();
    renderer?.forceContextLoss?.();
    geometry = null;
    material = null;
    mesh = null;
    scene = null;
    camera = null;
    renderer = null;
}

onMounted(() => {
    reducedMotion.value = !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    if (!reducedMotion.value) {
        initThree();
    }
});

onBeforeUnmount(() => {
    disposeThree();
});
</script>

<template>
    <section class="relative overflow-hidden">
        <!-- Liquid gradient mesh backdrop -->
        <div class="pointer-events-none absolute inset-0">
            <!-- Animated three.js canvas -->
            <canvas v-if="!reducedMotion" ref="canvasEl" class="absolute inset-0 h-full w-full opacity-90" aria-hidden="true" />

            <!-- Static fallback for reduced motion -->
            <div
                v-else
                class="absolute inset-0 opacity-90"
                :style="{
                    background:
                        'radial-gradient(1200px 700px at 70% -10%, color-mix(in srgb, var(--ak-grad-3) 45%, transparent), transparent 60%), radial-gradient(1000px 620px at 18% 6%, color-mix(in srgb, var(--ak-grad-1) 34%, transparent), transparent 58%), linear-gradient(135deg, color-mix(in srgb, var(--ak-grad-2) 26%, transparent), transparent 70%)',
                }"
            />

            <!-- Texture + readability overlays -->
            <div class="ak-grid-texture absolute inset-0 opacity-[0.18]" />
            <div class="absolute inset-0" :style="{ background: 'radial-gradient(900px 520px at 50% 38%, transparent, color-mix(in srgb, var(--ak-bg) 72%, transparent) 78%)' }" />
            <div class="absolute inset-x-0 top-0 h-32" :style="{ background: 'linear-gradient(to bottom, var(--ak-bg), transparent)' }" />
            <div class="absolute inset-x-0 bottom-0 h-48" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />
        </div>

        <!-- Copy -->
        <div class="ak-container relative flex flex-col items-center pb-24 pt-36 text-center sm:pt-44 lg:pb-32">
            <div
                class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-glass-border bg-[var(--ak-glass)] px-4 py-1.5 text-xs font-medium text-ak-text-2 backdrop-blur"
                style="animation-delay: 0s"
            >
                <span class="relative flex h-2 w-2">
                    <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                    <span class="relative inline-flex h-2 w-2 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                </span>
                {{ hero.badge ?? 'AI-native product engineering' }}
            </div>

            <p v-if="hero.eyebrow" class="ak-eyebrow ak-fade-up mt-7" :dot="false" style="animation-delay: 0.06s">
                <Icon name="sparkles" :size="14" />
                {{ hero.eyebrow }}
            </p>

            <h1
                class="ak-fade-up mt-6 max-w-4xl font-display text-[2.85rem] font-semibold leading-[1.02] tracking-tight text-ak-text sm:text-6xl lg:text-[4.6rem]"
                style="animation-delay: 0.12s"
            >
                <span class="block">
                    <template v-for="(part, i) in line1Parts" :key="i">
                        <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                        <span v-else>{{ part.text }}</span>
                    </template>
                </span>
                <span class="mt-1 block text-ak-text-2">{{ hero.headline_line2 }}</span>
            </h1>

            <p class="ak-fade-up mt-7 max-w-2xl text-lg leading-relaxed text-ak-text-2" style="animation-delay: 0.22s">
                {{ hero.subhead }}
            </p>

            <div class="ak-fade-up mt-9 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 0.32s">
                <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                    {{ hero.primary_label ?? 'Start a project' }}
                    <Icon name="arrow-right" :size="18" />
                </AkButton>
                <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                    <Icon name="zap" :size="17" />
                    {{ hero.secondary_label ?? 'See the work' }}
                </AkButton>
            </div>

            <!-- Hero stat strip -->
            <div
                v-if="stats.length"
                class="ak-fade-up ak-glass mt-14 flex w-full max-w-3xl flex-wrap items-center justify-center gap-x-12 gap-y-6 rounded-2xl px-8 py-6"
                style="animation-delay: 0.44s"
            >
                <div v-for="(stat, i) in stats" :key="i" class="flex flex-col items-center text-center">
                    <div class="font-display text-3xl font-bold text-ak-text sm:text-[2rem]">
                        <span v-if="stat.prefix" class="text-ak-text-2">{{ stat.prefix }}</span
                        ><span class="ak-gradient-text">{{ stat.value }}</span
                        ><span v-if="stat.suffix" class="ak-gradient-text">{{ stat.suffix }}</span>
                    </div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-ak-text-3">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
