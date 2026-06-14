<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import HeroCopy from './HeroCopy.vue';

defineProps<{ hero: Record<string, any>; stats: any[] }>();

const canvasEl = ref<HTMLCanvasElement | null>(null);
const reducedMotion = ref(false);

// --- grid geometry constants (kept small for perf) ---
const LINES_Z = 60; // horizontal lines receding to the horizon
const LINES_X = 41; // longitudinal lines across the plane (odd => centre line)
const SPACING = 12; // world units between grid lines
const SUN_POINTS = 1400; // additive sun/horizon cluster

let raf = 0;
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;

let grid: THREE.LineSegments | null = null;
let gridGeo: THREE.BufferGeometry | null = null;
let gridMat: THREE.LineBasicMaterial | null = null;

let sun: THREE.Points | null = null;
let sunGeo: THREE.BufferGeometry | null = null;
let sunMat: THREE.PointsMaterial | null = null;

let glow: THREE.Mesh | null = null;
let glowGeo: THREE.PlaneGeometry | null = null;
let glowMat: THREE.MeshBasicMaterial | null = null;

let sunTexture: THREE.Texture | null = null;

// reusable scratch colour (no per-frame allocation)
const scratch = new THREE.Color();

function readThemeColor(varName: string, fallback: string): THREE.Color {
    let raw = '';

    try {
        raw = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    } catch {
        raw = '';
    }

    try {
        return new THREE.Color(raw || fallback);
    } catch {
        return new THREE.Color(fallback);
    }
}

function makeSunTexture(): THREE.Texture {
    const size = 64;
    const cnv = document.createElement('canvas');
    cnv.width = size;
    cnv.height = size;
    const ctx = cnv.getContext('2d');

    if (ctx) {
        const g = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
        g.addColorStop(0, 'rgba(255,255,255,1)');
        g.addColorStop(0.35, 'rgba(255,255,255,0.55)');
        g.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cnv);
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

    const c1 = readThemeColor('--ak-grad-1', '#28baf3'); // near
    const c2 = readThemeColor('--ak-grad-2', '#5778f8'); // mid
    const c3 = readThemeColor('--ak-grad-3', '#7e2cfd'); // far / horizon

    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(62, width / height, 0.1, 2000);
    // low, slightly tilted down toward the receding plane
    camera.position.set(0, 6, 34);
    camera.lookAt(0, 1.5, -120);

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    // ---------------------------------------------------------------
    // GROUND GRID - one LineSegments built from a single buffer.
    // Lines fade from c1 (near viewer) to c3 (far horizon) and dim out
    // toward the horizon line via per-vertex colours.
    // ---------------------------------------------------------------
    const halfX = ((LINES_X - 1) / 2) * SPACING;
    const depth = (LINES_Z - 1) * SPACING; // total z extent of the plane
    const farZ = -depth; // horizon edge (most negative z)

    // segment count: LINES_Z transverse lines + LINES_X longitudinal lines
    const segCount = LINES_Z + LINES_X;
    const positions = new Float32Array(segCount * 2 * 3);
    const colors = new Float32Array(segCount * 2 * 3);

    let p = 0;
    let cIdx = 0;

    const writeVertex = (x: number, z: number) => {
        positions[p++] = x;
        positions[p++] = 0;
        positions[p++] = z;
        // fade factor: 0 near viewer -> 1 at horizon
        const f = THREE.MathUtils.clamp(z / farZ, 0, 1);

        // colour gradient near->mid->far
        if (f < 0.5) {
            scratch.copy(c1).lerp(c2, f / 0.5);
        } else {
            scratch.copy(c2).lerp(c3, (f - 0.5) / 0.5);
        }

        // dim toward the horizon so lines dissolve into the glow band
        const dim = 1 - Math.pow(f, 1.7) * 0.92;
        colors[cIdx++] = scratch.r * dim;
        colors[cIdx++] = scratch.g * dim;
        colors[cIdx++] = scratch.b * dim;
    };

    // transverse lines (run along X) marching in z toward the viewer
    for (let i = 0; i < LINES_Z; i++) {
        const z = -i * SPACING;
        writeVertex(-halfX, z);
        writeVertex(halfX, z);
    }

    // longitudinal lines (run along Z) - perspective rails
    for (let j = 0; j < LINES_X; j++) {
        const x = -halfX + j * SPACING;
        writeVertex(x, 0);
        writeVertex(x, farZ);
    }

    gridGeo = new THREE.BufferGeometry();
    gridGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    gridGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    gridMat = new THREE.LineBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 0.85,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });

    grid = new THREE.LineSegments(gridGeo, gridMat);
    grid.position.y = -2;
    scene.add(grid);

    // ---------------------------------------------------------------
    // SUN / HORIZON BAND - additive Points cluster sitting on the
    // horizon line, brightest at its core, theme-coloured.
    // ---------------------------------------------------------------
    sunTexture = makeSunTexture();
    const sunPos = new Float32Array(SUN_POINTS * 3);
    const sunCol = new Float32Array(SUN_POINTS * 3);
    const sunRadius = 46;

    for (let i = 0; i < SUN_POINTS; i++) {
        // bias points toward the centre for a dense glowing core
        const ang = Math.random() * Math.PI * 2;
        const r = Math.pow(Math.random(), 1.6) * sunRadius;
        const x = Math.cos(ang) * r;
        // squash vertically into a wide horizon band
        const y = Math.sin(ang) * r * 0.62;
        sunPos[i * 3] = x;
        sunPos[i * 3 + 1] = y;
        sunPos[i * 3 + 2] = 0;

        // core = c1 (hot), outer = c3 (cool) for a glowing falloff
        const t = THREE.MathUtils.clamp(r / sunRadius, 0, 1);
        scratch.copy(c1).lerp(c3, t);
        sunCol[i * 3] = scratch.r;
        sunCol[i * 3 + 1] = scratch.g;
        sunCol[i * 3 + 2] = scratch.b;
    }

    sunGeo = new THREE.BufferGeometry();
    sunGeo.setAttribute('position', new THREE.BufferAttribute(sunPos, 3));
    sunGeo.setAttribute('color', new THREE.BufferAttribute(sunCol, 3));

    sunMat = new THREE.PointsMaterial({
        size: 3.4,
        map: sunTexture,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        depthWrite: false,
        sizeAttenuation: true,
        blending: THREE.AdditiveBlending,
    });

    sun = new THREE.Points(sunGeo, sunMat);
    // place on the horizon, just above the grounded grid
    sun.position.set(0, 17, farZ + SPACING * 2);
    scene.add(sun);

    // soft wide glow halo behind the sun to bloom the horizon line
    glowGeo = new THREE.PlaneGeometry(360, 150);
    glowMat = new THREE.MeshBasicMaterial({
        map: sunTexture,
        color: c2,
        transparent: true,
        opacity: 0.5,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    glow = new THREE.Mesh(glowGeo, glowMat);
    glow.position.set(0, 14, farZ + SPACING);
    scene.add(glow);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera) {
return;
}

    const t = performance.now() * 0.001;

    if (grid) {
        // scroll the plane toward the viewer; wrap by one cell so it loops
        // seamlessly (the grid is built from evenly spaced lines).
        grid.position.z = (t * 7) % SPACING;
    }

    if (sun) {
        // slow hypnotic breathing of the sun core
        const s = 1 + Math.sin(t * 0.7) * 0.05;
        sun.scale.set(s, s, 1);
    }

    if (glowMat) {
        glowMat.opacity = 0.42 + Math.sin(t * 0.7) * 0.08;
    }

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

    if (grid && scene) {
scene.remove(grid);
}

    if (sun && scene) {
scene.remove(sun);
}

    if (glow && scene) {
scene.remove(glow);
}

    gridGeo?.dispose();
    gridMat?.dispose();
    sunGeo?.dispose();
    sunMat?.dispose();
    glowGeo?.dispose();
    glowMat?.dispose();
    sunTexture?.dispose();

    renderer?.dispose();

    grid = null;
    gridGeo = null;
    gridMat = null;
    sun = null;
    sunGeo = null;
    sunMat = null;
    glow = null;
    glowGeo = null;
    glowMat = null;
    sunTexture = null;
    scene = null;
    camera = null;
    renderer = null;
}

onMounted(() => {
    reducedMotion.value = !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    if (reducedMotion.value) {
return;
}

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
        <!-- deep base wash: night sky above, theme bloom toward the horizon -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(1100px 480px at 50% 64%, color-mix(in srgb, var(--ak-grad-3) 30%, transparent), transparent 60%), radial-gradient(1400px 700px at 50% 120%, color-mix(in srgb, var(--ak-grad-2) 22%, transparent), transparent 64%), linear-gradient(to bottom, var(--ak-bg), color-mix(in srgb, var(--ak-grad-3) 8%, var(--ak-bg)))',
            }"
        />

        <!-- THREE.js canvas (skipped on reduced-motion) -->
        <canvas
            v-if="!reducedMotion"
            ref="canvasEl"
            class="pointer-events-none absolute inset-0 -z-10 h-full w-full"
            aria-hidden="true"
        />

        <!-- static fallback for reduced motion: a synthwave horizon glow -->
        <div
            v-else
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'conic-gradient(from 270deg at 50% 60%, color-mix(in srgb, var(--ak-grad-1) 28%, transparent), color-mix(in srgb, var(--ak-grad-2) 28%, transparent), color-mix(in srgb, var(--ak-grad-3) 28%, transparent), color-mix(in srgb, var(--ak-grad-1) 28%, transparent))',
                filter: 'blur(46px)',
                opacity: 0.55,
            }"
        />

        <!-- faint grid texture + horizon scanline -->
        <div class="ak-grid-texture pointer-events-none absolute inset-0 -z-10 opacity-30" />
        <div
            class="pointer-events-none absolute inset-x-0 top-[58%] -z-10 h-px"
            :style="{ background: 'linear-gradient(to right, transparent, color-mix(in srgb, var(--ak-grad-1) 70%, transparent), transparent)', opacity: 0.5 }"
        />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-44" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <!-- shared editable copy block: badge, headline, subhead, CTAs, trust row, stat strip -->
        <HeroCopy :hero="hero" :stats="stats" />
    </section>
</template>
