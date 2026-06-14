<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import HeroCopy from './HeroCopy.vue';

defineProps<{ hero: Record<string, any>; stats: any[] }>();

const canvasEl = ref<HTMLCanvasElement | null>(null);
const reducedMotion = ref(false);

let raf = 0;
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;

// helix container - everything spins + drifts together
let helix: THREE.Group | null = null;

// strand particle clouds (A = grad-1, B = grad-3)
let strandAGeo: THREE.BufferGeometry | null = null;
let strandBGeo: THREE.BufferGeometry | null = null;
let strandAMat: THREE.PointsMaterial | null = null;
let strandBMat: THREE.PointsMaterial | null = null;
let strandA: THREE.Points | null = null;
let strandB: THREE.Points | null = null;

// rung connectors (grad-2)
let rungGeo: THREE.BufferGeometry | null = null;
let rungMat: THREE.LineBasicMaterial | null = null;
let rungs: THREE.LineSegments | null = null;

// soft glow sprite texture (shared, disposed once)
let glowTex: THREE.Texture | null = null;

// helix design constants
const TURNS = 5; // full revolutions of the double helix
const HEIGHT = 150; // vertical extent in world units
const RADIUS = 18; // helix radius
const PER_STRAND = 1400; // particles per strand -> 2800 total
const RUNG_COUNT = 130; // connecting bars
const TWIST = TURNS * Math.PI * 2;

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

function makeGlowTexture(): THREE.Texture {
    const size = 64;
    const cnv = document.createElement('canvas');
    cnv.width = size;
    cnv.height = size;
    const ctx = cnv.getContext('2d');

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
        g.addColorStop(0.25, 'rgba(255,255,255,0.85)');
        g.addColorStop(0.55, 'rgba(255,255,255,0.25)');
        g.addColorStop(1.0, 'rgba(255,255,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cnv);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.needsUpdate = true;

    return tex;
}

// y in [0, HEIGHT] mapped from a 0..1 progress, recentred around 0
function helixPoint(p: number, phase: number, target: THREE.Vector3): void {
    const angle = p * TWIST + phase;
    const y = p * HEIGHT - HEIGHT / 2;
    target.set(Math.cos(angle) * RADIUS, y, Math.sin(angle) * RADIUS);
}

function buildStrand(
    phase: number,
    color: THREE.Color,
): { geo: THREE.BufferGeometry; mat: THREE.PointsMaterial } {
    const positions = new Float32Array(PER_STRAND * 3);
    const colors = new Float32Array(PER_STRAND * 3);
    const tmp = new THREE.Vector3();
    const tint = new THREE.Color();

    for (let i = 0; i < PER_STRAND; i++) {
        const p = i / (PER_STRAND - 1);
        helixPoint(p, phase, tmp);
        // gentle radial jitter so the strand reads as a glowing filament, not a wire
        const jr = 1 + (Math.random() - 0.5) * 0.18;
        positions[i * 3] = tmp.x * jr;
        positions[i * 3 + 1] = tmp.y + (Math.random() - 0.5) * 0.6;
        positions[i * 3 + 2] = tmp.z * jr;

        // subtle brightness variance for a living shimmer
        const b = 0.7 + Math.random() * 0.3;
        tint.copy(color).multiplyScalar(b);
        colors[i * 3] = tint.r;
        colors[i * 3 + 1] = tint.g;
        colors[i * 3 + 2] = tint.b;
    }

    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const mat = new THREE.PointsMaterial({
        size: 2.6,
        map: glowTex,
        vertexColors: true,
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true,
        opacity: 0.95,
    });

    return { geo, mat };
}

function buildRungs(color: THREE.Color): {
    geo: THREE.BufferGeometry;
    mat: THREE.LineBasicMaterial;
} {
    const positions = new Float32Array(RUNG_COUNT * 2 * 3);
    const colors = new Float32Array(RUNG_COUNT * 2 * 3);
    const a = new THREE.Vector3();
    const b = new THREE.Vector3();
    const tint = new THREE.Color();

    for (let i = 0; i < RUNG_COUNT; i++) {
        const p = i / (RUNG_COUNT - 1);
        helixPoint(p, 0, a); // strand A phase
        helixPoint(p, Math.PI, b); // strand B phase (opposite side)

        const o = i * 6;
        positions[o] = a.x;
        positions[o + 1] = a.y;
        positions[o + 2] = a.z;
        positions[o + 3] = b.x;
        positions[o + 4] = b.y;
        positions[o + 5] = b.z;

        // fade rungs toward the ends for depth, brighten in the middle band
        const fade = 0.45 + 0.55 * Math.sin(p * Math.PI);
        tint.copy(color).multiplyScalar(fade);

        for (let v = 0; v < 2; v++) {
            colors[o + v * 3] = tint.r;
            colors[o + v * 3 + 1] = tint.g;
            colors[o + v * 3 + 2] = tint.b;
        }
    }

    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const mat = new THREE.LineBasicMaterial({
        vertexColors: true,
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        opacity: 0.5,
    });

    return { geo, mat };
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

    const c1 = readThemeColor('--ak-grad-1', '#22d3ee');
    const c2 = readThemeColor('--ak-grad-2', '#6366f1');
    const c3 = readThemeColor('--ak-grad-3', '#a855f7');

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(55, width / height, 0.1, 2000);
    camera.position.set(0, 0, 96);
    camera.lookAt(0, 0, 0);

    renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: true,
        alpha: true,
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    glowTex = makeGlowTexture();

    helix = new THREE.Group();
    // slight lean so the helix has dynamic perspective
    helix.rotation.z = 0.12;

    const a = buildStrand(0, c1);
    strandAGeo = a.geo;
    strandAMat = a.mat;
    strandA = new THREE.Points(strandAGeo, strandAMat);

    const b = buildStrand(Math.PI, c3);
    strandBGeo = b.geo;
    strandBMat = b.mat;
    strandB = new THREE.Points(strandBGeo, strandBMat);

    const r = buildRungs(c2);
    rungGeo = r.geo;
    rungMat = r.mat;
    rungs = new THREE.LineSegments(rungGeo, rungMat);

    helix.add(strandA);
    helix.add(strandB);
    helix.add(rungs);
    scene.add(helix);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera || !helix) {
        return;
    }

    const t = performance.now() * 0.001;

    // slow, elegant rotation around the vertical axis
    helix.rotation.y = t * 0.18;
    // vertical drift that wraps within one helix pitch so it feels infinite
    const pitch = HEIGHT / TURNS;
    helix.position.y = (t * 3.2) % pitch;
    // very gentle breathing tilt for life, never disorienting
    helix.rotation.x = Math.sin(t * 0.25) * 0.06;

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

    if (helix) {
        if (strandA) {
            helix.remove(strandA);
        }

        if (strandB) {
            helix.remove(strandB);
        }

        if (rungs) {
            helix.remove(rungs);
        }

        scene?.remove(helix);
    }

    strandAGeo?.dispose();
    strandBGeo?.dispose();
    rungGeo?.dispose();
    strandAMat?.dispose();
    strandBMat?.dispose();
    rungMat?.dispose();
    glowTex?.dispose();
    renderer?.dispose();

    strandA = null;
    strandB = null;
    rungs = null;
    strandAGeo = null;
    strandBGeo = null;
    rungGeo = null;
    strandAMat = null;
    strandBMat = null;
    rungMat = null;
    glowTex = null;
    helix = null;
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
        <!-- deep base wash -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(1100px 680px at 50% -8%, color-mix(in srgb, var(--ak-grad-2) 28%, transparent), transparent 60%), radial-gradient(820px 620px at 12% 112%, color-mix(in srgb, var(--ak-grad-3) 20%, transparent), transparent 62%), radial-gradient(820px 620px at 88% 108%, color-mix(in srgb, var(--ak-grad-1) 18%, transparent), transparent 62%)',
            }"
        />
        <!-- THREE.js canvas (skipped on reduced-motion) -->
        <canvas
            v-if="!reducedMotion"
            ref="canvasEl"
            class="pointer-events-none absolute inset-0 -z-10 h-full w-full"
            aria-hidden="true"
        />
        <!-- static fallback for reduced motion -->
        <div
            v-else
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'conic-gradient(from 200deg at 52% 34%, color-mix(in srgb, var(--ak-grad-1) 26%, transparent), color-mix(in srgb, var(--ak-grad-2) 26%, transparent), color-mix(in srgb, var(--ak-grad-3) 26%, transparent), color-mix(in srgb, var(--ak-grad-1) 26%, transparent))',
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
