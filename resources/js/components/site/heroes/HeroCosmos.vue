<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import HeroCopy from './HeroCopy.vue';

defineProps<{ hero: Record<string, any>; stats: any[] }>();

const canvasEl = ref<HTMLCanvasElement | null>(null);
const reducedMotion = ref(false);

/* ---------- three.js deep-space cosmos ---------- */
let raf = 0;
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;

// starfield (dense parallax points across varied depths)
let starGeo: THREE.BufferGeometry | null = null;
let starMat: THREE.PointsMaterial | null = null;
let starTex: THREE.CanvasTexture | null = null;
let starObj: THREE.Points | null = null;

// nebula (soft glowing additive cloud sprites tinted across the gradient)
let nebulaGeo: THREE.BufferGeometry | null = null;
let nebulaMat: THREE.PointsMaterial | null = null;
let nebulaTex: THREE.CanvasTexture | null = null;
let nebulaObj: THREE.Points | null = null;

// Counts capped well under "a few thousand".
const STAR_COUNT = 2400;
const NEBULA_COUNT = 140;
const FIELD = { x: 320, y: 220, z: 360 }; // half-extents of the star volume

// Persistent per-star state (no per-frame allocation in animate()).
const starPositions = new Float32Array(STAR_COUNT * 3);
const starBasePositions = new Float32Array(STAR_COUNT * 3); // untouched reference for drift
const starColors = new Float32Array(STAR_COUNT * 3);
const starBaseAlpha = new Float32Array(STAR_COUNT); // baseline brightness 0..1
const starDrift = new Float32Array(STAR_COUNT); // slow vertical drift speed
const twinklePhase = new Float32Array(STAR_COUNT); // phase offset for twinkle
const twinkleAmp = new Float32Array(STAR_COUNT); // 0 = steady, >0 = twinkles

// Per-star base RGB (without alpha) so twinkle can rescale brightness without alloc.
const starBaseR = new Float32Array(STAR_COUNT);
const starBaseG = new Float32Array(STAR_COUNT);
const starBaseB = new Float32Array(STAR_COUNT);

// Nebula sprite persistent state.
const nebulaPositions = new Float32Array(NEBULA_COUNT * 3);
const nebulaColors = new Float32Array(NEBULA_COUNT * 3);

// Scratch colour reused every frame, never reallocated.
const scratchColor = new THREE.Color();
let cGrad1 = new THREE.Color('#22d3ee');
let cGrad2 = new THREE.Color('#6366f1');
let cGrad3 = new THREE.Color('#a855f7');

function readThemeColor(varName: string, fallback: string): THREE.Color {
    let raw = '';

    try {
        raw = getComputedStyle(document.documentElement)
            .getPropertyValue(varName)
            .trim();
    } catch {
        raw = '';
    }

    try {
        return new THREE.Color(raw || fallback);
    } catch {
        return new THREE.Color(fallback);
    }
}

/** Blend the three gradient stops by a normalised t (0 → 1) across grad-1/2/3. */
function colorByT(t: number, out: THREE.Color): THREE.Color {
    if (t < 0.5) {
        out.copy(cGrad1).lerp(cGrad2, t * 2);
    } else {
        out.copy(cGrad2).lerp(cGrad3, (t - 0.5) * 2);
    }

    return out;
}

/** Tiny soft round sprite used for every star point. */
function makeStarTexture(): THREE.CanvasTexture {
    const size = 64;
    const cv = document.createElement('canvas');
    cv.width = size;
    cv.height = size;
    const ctx = cv.getContext('2d');

    if (ctx) {
        const g = ctx.createRadialGradient(
            size / 2,
            size / 2,
            0,
            size / 2,
            size / 2,
            size / 2,
        );
        g.addColorStop(0.0, 'rgba(255,255,255,1)');
        g.addColorStop(0.35, 'rgba(255,255,255,0.65)');
        g.addColorStop(1.0, 'rgba(255,255,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cv);
    tex.needsUpdate = true;

    return tex;
}

/** Large soft billowing cloud sprite for the nebula points. */
function makeNebulaTexture(): THREE.CanvasTexture {
    const size = 256;
    const cv = document.createElement('canvas');
    cv.width = size;
    cv.height = size;
    const ctx = cv.getContext('2d');

    if (ctx) {
        const g = ctx.createRadialGradient(
            size / 2,
            size / 2,
            0,
            size / 2,
            size / 2,
            size / 2,
        );
        g.addColorStop(0.0, 'rgba(255,255,255,0.5)');
        g.addColorStop(0.25, 'rgba(255,255,255,0.22)');
        g.addColorStop(0.6, 'rgba(255,255,255,0.06)');
        g.addColorStop(1.0, 'rgba(255,255,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cv);
    tex.needsUpdate = true;

    return tex;
}

function initThree() {
    const canvas = canvasEl.value;

    if (!canvas) {
        return;
    }

    const parent = canvas.parentElement;

    if (!parent) {
        return;
    }

    const width = parent.clientWidth || window.innerWidth;
    const height = parent.clientHeight || window.innerHeight;

    cGrad1 = readThemeColor('--ak-grad-1', '#22d3ee'); // cyan
    cGrad2 = readThemeColor('--ak-grad-2', '#6366f1'); // indigo
    cGrad3 = readThemeColor('--ak-grad-3', '#a855f7'); // violet

    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 2000);
    camera.position.set(0, 0, 260);
    camera.lookAt(0, 0, 0);

    renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: true,
        alpha: true,
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    // ---- starfield ----
    for (let i = 0; i < STAR_COUNT; i++) {
        const i3 = i * 3;
        const x = (Math.random() - 0.5) * FIELD.x * 2;
        const y = (Math.random() - 0.5) * FIELD.y * 2;
        const z = (Math.random() - 0.5) * FIELD.z * 2;

        starBasePositions[i3] = x;
        starBasePositions[i3 + 1] = y;
        starBasePositions[i3 + 2] = z;
        starPositions[i3] = x;
        starPositions[i3 + 1] = y;
        starPositions[i3 + 2] = z;

        // mostly white, a faint tint pulled toward the palette for warmth
        const tint = Math.random();
        colorByT(tint, scratchColor);
        const whiteMix = 0.55 + Math.random() * 0.4; // bias to white
        const r = scratchColor.r * (1 - whiteMix) + whiteMix;
        const g = scratchColor.g * (1 - whiteMix) + whiteMix;
        const b = scratchColor.b * (1 - whiteMix) + whiteMix;

        starBaseAlpha[i] = 0.35 + Math.random() * 0.65;
        starBaseR[i] = r;
        starBaseG[i] = g;
        starBaseB[i] = b;
        starColors[i3] = r * starBaseAlpha[i];
        starColors[i3 + 1] = g * starBaseAlpha[i];
        starColors[i3 + 2] = b * starBaseAlpha[i];

        starDrift[i] =
            (0.4 + Math.random() * 0.9) * (Math.random() < 0.5 ? -1 : 1);
        twinklePhase[i] = Math.random() * Math.PI * 2;
        // only ~22% of stars twinkle, the rest stay serene & steady
        twinkleAmp[i] = Math.random() < 0.22 ? 0.4 + Math.random() * 0.45 : 0;
    }

    starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute(
        'position',
        new THREE.BufferAttribute(starPositions, 3),
    );
    starGeo.setAttribute('color', new THREE.BufferAttribute(starColors, 3));

    starTex = makeStarTexture();
    starMat = new THREE.PointsMaterial({
        size: 2.6,
        map: starTex,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 1,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    starObj = new THREE.Points(starGeo, starMat);
    scene.add(starObj);

    // ---- nebula clouds ----
    for (let i = 0; i < NEBULA_COUNT; i++) {
        const i3 = i * 3;
        // cluster the clouds in a few soft bands toward the back for depth
        nebulaPositions[i3] = (Math.random() - 0.5) * FIELD.x * 1.6;
        nebulaPositions[i3 + 1] = (Math.random() - 0.5) * FIELD.y * 1.3;
        nebulaPositions[i3 + 2] = -40 - Math.random() * (FIELD.z * 0.9);

        colorByT(Math.random(), scratchColor);
        const intensity = 0.12 + Math.random() * 0.18; // low-opacity tint
        nebulaColors[i3] = scratchColor.r * intensity;
        nebulaColors[i3 + 1] = scratchColor.g * intensity;
        nebulaColors[i3 + 2] = scratchColor.b * intensity;
    }

    nebulaGeo = new THREE.BufferGeometry();
    nebulaGeo.setAttribute(
        'position',
        new THREE.BufferAttribute(nebulaPositions, 3),
    );
    nebulaGeo.setAttribute('color', new THREE.BufferAttribute(nebulaColors, 3));

    nebulaTex = makeNebulaTexture();
    nebulaMat = new THREE.PointsMaterial({
        size: 320,
        map: nebulaTex,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    nebulaObj = new THREE.Points(nebulaGeo, nebulaMat);
    scene.add(nebulaObj);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera || !starGeo) {
        return;
    }

    const t = performance.now() * 0.001;

    // gentle vertical drift + selective twinkle, all writing into preallocated buffers
    const colAttr = starGeo.getAttribute('color') as THREE.BufferAttribute;
    const colArr = colAttr.array as Float32Array;

    for (let i = 0; i < STAR_COUNT; i++) {
        const i3 = i * 3;

        // slow drift; wrap within the field so the volume never empties
        let y = starBasePositions[i3 + 1] + starDrift[i] * t * 2;
        const span = FIELD.y * 2;
        y = ((((y + FIELD.y) % span) + span) % span) - FIELD.y;
        starPositions[i3 + 1] = y;

        if (twinkleAmp[i] > 0) {
            const tw =
                1 -
                twinkleAmp[i] *
                    (0.5 + 0.5 * Math.sin(t * 2.2 + twinklePhase[i]));
            const a = starBaseAlpha[i] * tw;
            colArr[i3] = starBaseR[i] * a;
            colArr[i3 + 1] = starBaseG[i] * a;
            colArr[i3 + 2] = starBaseB[i] * a;
        }
    }

    (starGeo.getAttribute('position') as THREE.BufferAttribute).needsUpdate =
        true;
    colAttr.needsUpdate = true;

    // starfield breathes with a barely-there rotation
    if (starObj) {
        starObj.rotation.y = t * 0.01;
    }

    // nebula clouds slowly billow + counter-rotate for a vast, living sky
    if (nebulaObj) {
        nebulaObj.rotation.z = t * 0.015;
        nebulaObj.rotation.y = Math.sin(t * 0.05) * 0.06;
    }

    if (nebulaMat) {
        nebulaMat.opacity = 0.78 + Math.sin(t * 0.25) * 0.12;
    }

    // gentle camera parallax - the serene, vast sway through the cosmos
    camera.position.x = Math.sin(t * 0.08) * 18;
    camera.position.y = Math.cos(t * 0.06) * 12;
    camera.lookAt(0, 0, 0);

    renderer.render(scene, camera);
}

function onResize() {
    const canvas = canvasEl.value;

    if (!canvas || !renderer || !camera) {
        return;
    }

    const parent = canvas.parentElement;

    if (!parent) {
        return;
    }

    const width = parent.clientWidth || window.innerWidth;
    const height = parent.clientHeight || window.innerHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height, false);
}

function disposeThree() {
    if (raf) {
        cancelAnimationFrame(raf);
        raf = 0;
    }

    window.removeEventListener('resize', onResize);

    if (scene) {
        if (starObj) {
            scene.remove(starObj);
        }

        if (nebulaObj) {
            scene.remove(nebulaObj);
        }
    }

    starGeo?.dispose();
    starMat?.dispose();
    starTex?.dispose();
    nebulaGeo?.dispose();
    nebulaMat?.dispose();
    nebulaTex?.dispose();

    renderer?.dispose();

    starGeo = null;
    starMat = null;
    starTex = null;
    starObj = null;
    nebulaGeo = null;
    nebulaMat = null;
    nebulaTex = null;
    nebulaObj = null;
    scene = null;
    camera = null;
    renderer = null;
}

onMounted(() => {
    reducedMotion.value = !!window.matchMedia?.(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (reducedMotion.value) {
        return;
    }

    // defer one tick so the canvas parent has measured layout
    requestAnimationFrame(() => {
        if (!reducedMotion.value) {
            initThree();
        }
    });
});

onBeforeUnmount(() => {
    disposeThree();
});
</script>

<template>
    <section class="relative overflow-hidden">
        <!-- deep-space base wash -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(1100px 760px at 28% 12%, color-mix(in srgb, var(--ak-grad-2) 26%, transparent), transparent 60%), radial-gradient(1000px 620px at 82% 88%, color-mix(in srgb, var(--ak-grad-3) 22%, transparent), transparent 62%), radial-gradient(900px 600px at 60% 50%, color-mix(in srgb, var(--ak-grad-1) 12%, transparent), transparent 66%)',
            }"
        />

        <!-- THREE.js cosmic starfield + nebula (skipped on reduced-motion) -->
        <canvas
            v-if="!reducedMotion"
            ref="canvasEl"
            class="pointer-events-none absolute inset-0 -z-10 h-full w-full"
            aria-hidden="true"
        />

        <!-- static conic-gradient fallback for reduced motion -->
        <div
            v-else
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'conic-gradient(from 200deg at 40% 35%, color-mix(in srgb, var(--ak-grad-1) 26%, transparent), color-mix(in srgb, var(--ak-grad-2) 26%, transparent), color-mix(in srgb, var(--ak-grad-3) 26%, transparent), color-mix(in srgb, var(--ak-grad-1) 26%, transparent))',
                filter: 'blur(40px)',
                opacity: 0.6,
            }"
        />

        <div
            class="ak-grid-texture pointer-events-none absolute inset-0 -z-10 opacity-40"
        />
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-44"
            :style="{
                background:
                    'linear-gradient(to top, var(--ak-bg), transparent)',
            }"
        />

        <!-- shared editable copy block -->
        <HeroCopy :hero="hero" :stats="stats" />
    </section>
</template>
