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

// Crystal group (so we can pulse + rotate as a whole)
let crystal: THREE.Group | null = null;
// Wireframe edge layers (one per theme colour for refractive split look)
let edgeGeometry: THREE.EdgesGeometry | null = null;
const edgeLines: THREE.LineSegments[] = [];
const edgeMaterials: THREE.LineBasicMaterial[] = [];
let coreIcoGeo: THREE.IcosahedronGeometry | null = null;

// Orbiting glint field
let glintGeometry: THREE.BufferGeometry | null = null;
let glintMaterial: THREE.PointsMaterial | null = null;
let glints: THREE.Points | null = null;
let glintTexture: THREE.Texture | null = null;

const GLINT_COUNT = 2600;

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

// Small soft radial sprite so glints read as light, not square dots.
function makeGlintTexture(): THREE.Texture {
    const size = 64;
    const cvs = document.createElement('canvas');
    cvs.width = size;
    cvs.height = size;
    const ctx = cvs.getContext('2d');

    if (ctx) {
        const grad = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
        grad.addColorStop(0, 'rgba(255,255,255,1)');
        grad.addColorStop(0.35, 'rgba(255,255,255,0.55)');
        grad.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cvs);
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

    const c1 = readThemeColor('--ak-grad-1', '#22d3ee');
    const c2 = readThemeColor('--ak-grad-2', '#6366f1');
    const c3 = readThemeColor('--ak-grad-3', '#a855f7');
    const palette = [c1, c2, c3];

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(58, width / height, 0.1, 2000);
    camera.position.set(0, 0, 100);

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    // --- Faceted crystal: three offset wireframe icosahedra, one per colour ---
    crystal = new THREE.Group();
    coreIcoGeo = new THREE.IcosahedronGeometry(30, 1);
    edgeGeometry = new THREE.EdgesGeometry(coreIcoGeo);

    // Tiny per-layer offsets create a chromatic-split, refractive shimmer.
    const offsets = [
        { s: 1.0, o: 0.0 },
        { s: 0.965, o: 0.7 },
        { s: 0.93, o: 1.4 },
    ];

    for (let i = 0; i < palette.length; i++) {
        const mat = new THREE.LineBasicMaterial({
            color: palette[i].clone(),
            transparent: true,
            opacity: i === 0 ? 0.62 : 0.34,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
        });
        const line = new THREE.LineSegments(edgeGeometry, mat);
        const cfg = offsets[i];
        line.scale.setScalar(cfg.s);
        line.rotation.set(cfg.o * 0.4, cfg.o, cfg.o * 0.2);
        edgeMaterials.push(mat);
        edgeLines.push(line);
        crystal.add(line);
    }

    scene.add(crystal);

    // --- Orbiting glint field tinted across the three colours ---
    glintTexture = makeGlintTexture();
    glintGeometry = new THREE.BufferGeometry();
    const positions = new Float32Array(GLINT_COUNT * 3);
    const colors = new Float32Array(GLINT_COUNT * 3);
    const tmp = new THREE.Color();

    for (let i = 0; i < GLINT_COUNT; i++) {
        // Spherical shell so glints appear to orbit the crystal.
        const radius = 42 + Math.random() * 78;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        const sinPhi = Math.sin(phi);
        const i3 = i * 3;
        positions[i3] = radius * sinPhi * Math.cos(theta);
        positions[i3 + 1] = radius * sinPhi * Math.sin(theta) * 0.82;
        positions[i3 + 2] = radius * Math.cos(phi);

        // Blend two adjacent palette colours for a jewel-like spread.
        const a = palette[i % 3];
        const b = palette[(i + 1) % 3];
        tmp.copy(a).lerp(b, Math.random());
        colors[i3] = tmp.r;
        colors[i3 + 1] = tmp.g;
        colors[i3 + 2] = tmp.b;
    }

    glintGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    glintGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    glintMaterial = new THREE.PointsMaterial({
        size: 1.7,
        map: glintTexture,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true,
    });
    glints = new THREE.Points(glintGeometry, glintMaterial);
    scene.add(glints);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera) {
return;
}

    const t = performance.now() * 0.001;

    if (crystal) {
        // Slow two-axis tumble.
        crystal.rotation.y = t * 0.16;
        crystal.rotation.x = Math.sin(t * 0.21) * 0.35;
        // Gentle breathing pulse of the whole crystal.
        const pulse = 1 + Math.sin(t * 0.9) * 0.045;
        crystal.scale.setScalar(pulse);

        // Faint independent counter-drift on the chromatic layers for shimmer.
        for (let i = 0; i < edgeLines.length; i++) {
            const line = edgeLines[i];
            line.rotation.z = Math.sin(t * 0.5 + i * 1.3) * 0.06 * i;
            const m = edgeMaterials[i];
            const base = i === 0 ? 0.62 : 0.34;
            m.opacity = base + Math.sin(t * 1.4 + i * 2.1) * 0.12;
        }
    }

    if (glints) {
        // Orbit the field opposite to the crystal.
        glints.rotation.y = -t * 0.09;
        glints.rotation.x = Math.cos(t * 0.17) * 0.12;
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

    if (crystal) {
        for (const line of edgeLines) {
            crystal.remove(line);
        }

        scene?.remove(crystal);
    }

    if (glints) {
        scene?.remove(glints);
    }

    for (const m of edgeMaterials) {
        m.dispose();
    }

    edgeMaterials.length = 0;
    edgeLines.length = 0;

    edgeGeometry?.dispose();
    coreIcoGeo?.dispose();
    glintGeometry?.dispose();
    glintMaterial?.dispose();
    glintTexture?.dispose();

    renderer?.dispose();

    crystal = null;
    edgeGeometry = null;
    coreIcoGeo = null;
    glintGeometry = null;
    glintMaterial = null;
    glints = null;
    glintTexture = null;
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
        <!-- deep base wash -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(1100px 640px at 64% -8%, color-mix(in srgb, var(--ak-grad-2) 30%, transparent), transparent 60%), radial-gradient(820px 560px at 12% 116%, color-mix(in srgb, var(--ak-grad-3) 22%, transparent), transparent 60%), radial-gradient(640px 520px at 92% 96%, color-mix(in srgb, var(--ak-grad-1) 16%, transparent), transparent 62%)',
            }"
        />
        <!-- THREE.js canvas (skipped on reduced-motion) -->
        <canvas v-if="!reducedMotion" ref="canvasEl" class="pointer-events-none absolute inset-0 -z-10 h-full w-full" aria-hidden="true" />
        <!-- static fallback for reduced motion -->
        <div
            v-else
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'conic-gradient(from 210deg at 58% 32%, color-mix(in srgb, var(--ak-grad-1) 26%, transparent), color-mix(in srgb, var(--ak-grad-2) 26%, transparent), color-mix(in srgb, var(--ak-grad-3) 26%, transparent), color-mix(in srgb, var(--ak-grad-1) 26%, transparent))',
                filter: 'blur(40px)',
                opacity: 0.6,
            }"
        />
        <div class="ak-grid-texture pointer-events-none absolute inset-0 -z-10 opacity-40" />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-44" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <!-- shared editable copy block -->
        <HeroCopy :hero="hero" :stats="stats" />
    </section>
</template>
