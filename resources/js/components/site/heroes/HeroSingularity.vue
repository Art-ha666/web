<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import HeroCopy from './HeroCopy.vue';

defineProps<{ hero: Record<string, any>; stats: any[] }>();

const canvasEl = ref<HTMLCanvasElement | null>(null);
const reducedMotion = ref(false);

/* ---------- three.js wormhole / singularity tunnel ---------- */
let raf = 0;
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;

let tunnelGeo: THREE.BufferGeometry | null = null;
let tunnelMat: THREE.PointsMaterial | null = null;
let tunnelObj: THREE.Points | null = null;

let coreGeo: THREE.SphereGeometry | null = null;
let coreMat: THREE.MeshBasicMaterial | null = null;
let coreObj: THREE.Mesh | null = null;

let glowGeo: THREE.PlaneGeometry | null = null;
let glowMat: THREE.SpriteMaterial | null = null;
let glowTex: THREE.CanvasTexture | null = null;
let glowObj: THREE.Sprite | null = null;

const ringMats: THREE.LineBasicMaterial[] = [];
const ringGeos: THREE.BufferGeometry[] = [];
const ringObjs: THREE.LineLoop[] = [];

// Tunnel geometry: particles distributed across concentric depth bands.
const PARTICLE_COUNT = 2600; // capped, well under a few thousand
const TUNNEL_DEPTH = 220; // total length down the -z axis
const NEAR_Z = 30; // closest band to the camera (in front of z=0 core)
const RING_RADIUS = 46; // outer radius of the swirl
const CORE_RADIUS = 4.2; // dark vanishing point radius (particles avoid)
const RING_COUNT = 9; // torus wireframe outlines woven into the tunnel

// Per-particle persistent state (no per-frame allocation in animate()).
const positions = new Float32Array(PARTICLE_COUNT * 3);
const colors = new Float32Array(PARTICLE_COUNT * 3);
const radii = new Float32Array(PARTICLE_COUNT); // distance from tunnel axis
const angles = new Float32Array(PARTICLE_COUNT); // current swirl angle
const angularSpeed = new Float32Array(PARTICLE_COUNT); // per-particle spin
const depthSpeed = new Float32Array(PARTICLE_COUNT); // inward fall speed

// Scratch colour reused every frame, never reallocated.
const scratchColor = new THREE.Color();
let cGrad1 = new THREE.Color('#22d3ee');
let cGrad2 = new THREE.Color('#6366f1');
let cGrad3 = new THREE.Color('#a855f7');

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

/** Blend the three gradient stops by a normalised radius t (0 = core, 1 = rim). */
function colorByRadius(t: number, out: THREE.Color): THREE.Color {
    if (t < 0.5) {
        out.copy(cGrad3).lerp(cGrad2, t * 2); // violet core → indigo mid
    } else {
        out.copy(cGrad2).lerp(cGrad1, (t - 0.5) * 2); // indigo mid → cyan rim
    }

    return out;
}

/** Soft radial sprite for the bright additive accretion glow. */
function makeGlowTexture(inner: THREE.Color, outer: THREE.Color): THREE.CanvasTexture {
    const size = 256;
    const cv = document.createElement('canvas');
    cv.width = size;
    cv.height = size;
    const ctx = cv.getContext('2d');

    if (ctx) {
        const g = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
        g.addColorStop(0.0, `rgba(255,255,255,0.95)`);
        g.addColorStop(0.18, `rgba(${(inner.r * 255) | 0},${(inner.g * 255) | 0},${(inner.b * 255) | 0},0.85)`);
        g.addColorStop(0.5, `rgba(${(outer.r * 255) | 0},${(outer.g * 255) | 0},${(outer.b * 255) | 0},0.35)`);
        g.addColorStop(1.0, `rgba(0,0,0,0)`);
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cv);
    tex.needsUpdate = true;

    return tex;
}

/** Place a particle at a given depth + angle, writing position/colour. */
function placeParticle(i: number, depthT: number): void {
    const i3 = i * 3;
    // radius shrinks toward the core as the particle approaches z=0 (the singularity)
    const pull = Math.pow(depthT, 1.6); // closer to core => smaller radius
    const baseR = radii[i];
    const r = CORE_RADIUS + (baseR - CORE_RADIUS) * pull;
    const a = angles[i];
    const z = NEAR_Z - depthT * (TUNNEL_DEPTH + NEAR_Z); // depthT 0 = near, 1 = at/just past core

    positions[i3] = Math.cos(a) * r;
    positions[i3 + 1] = Math.sin(a) * r;
    positions[i3 + 2] = z;

    // colour by radius band; fade brightness as it nears the dark core
    const radiusT = (baseR - CORE_RADIUS) / (RING_RADIUS - CORE_RADIUS);
    colorByRadius(radiusT, scratchColor);
    const dim = 0.25 + 0.75 * pull; // dark near the singularity, bright at rim
    colors[i3] = scratchColor.r * dim;
    colors[i3 + 1] = scratchColor.g * dim;
    colors[i3 + 2] = scratchColor.b * dim;
}

// Per-particle normalised depth (0 near camera, 1 at core) advanced each frame.
const depthT = new Float32Array(PARTICLE_COUNT);

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

    cGrad1 = readThemeColor('--ak-grad-1', '#22d3ee'); // cyan (rim)
    cGrad2 = readThemeColor('--ak-grad-2', '#6366f1'); // indigo (mid)
    cGrad3 = readThemeColor('--ak-grad-3', '#a855f7'); // violet (core)

    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(64, width / height, 0.1, 2000);
    camera.position.set(0, 0, 60); // looks down -z INTO the tunnel
    camera.lookAt(0, 0, -200);

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    // seed every particle into the swirling tunnel
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        // bias toward the rim a little so the throat stays sparse/dark
        const rNorm = Math.sqrt(Math.random());
        radii[i] = CORE_RADIUS + rNorm * (RING_RADIUS - CORE_RADIUS);
        angles[i] = Math.random() * Math.PI * 2;
        angularSpeed[i] = (0.12 + Math.random() * 0.22) * (Math.random() < 0.5 ? -1 : 1);
        depthSpeed[i] = 0.018 + Math.random() * 0.03;
        depthT[i] = Math.random(); // spread across the full tunnel length
        placeParticle(i, depthT[i]);
    }

    tunnelGeo = new THREE.BufferGeometry();
    tunnelGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    tunnelGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    tunnelMat = new THREE.PointsMaterial({
        size: 1.5,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 0.95,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    tunnelObj = new THREE.Points(tunnelGeo, tunnelMat);
    scene.add(tunnelObj);

    // concentric torus-like ring wireframes woven through the depth for structure
    const segs = 96;

    for (let r = 0; r < RING_COUNT; r++) {
        const dt = (r + 0.5) / RING_COUNT;
        const pull = Math.pow(dt, 1.6);
        const ringR = CORE_RADIUS + (RING_RADIUS - CORE_RADIUS) * pull;
        const ringZ = NEAR_Z - dt * (TUNNEL_DEPTH + NEAR_Z);
        const pts = new Float32Array(segs * 3);

        for (let s = 0; s < segs; s++) {
            const a = (s / segs) * Math.PI * 2;
            pts[s * 3] = Math.cos(a) * ringR;
            pts[s * 3 + 1] = Math.sin(a) * ringR;
            pts[s * 3 + 2] = 0;
        }

        const rg = new THREE.BufferGeometry();
        rg.setAttribute('position', new THREE.BufferAttribute(pts, 3));

        const radiusT = (ringR - CORE_RADIUS) / (RING_RADIUS - CORE_RADIUS);
        colorByRadius(Math.min(1, radiusT + 0.15), scratchColor);
        const rm = new THREE.LineBasicMaterial({
            color: scratchColor.clone(),
            transparent: true,
            opacity: 0.12 + 0.22 * pull,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
        });
        const loop = new THREE.LineLoop(rg, rm);
        loop.position.z = ringZ;
        scene.add(loop);
        ringGeos.push(rg);
        ringMats.push(rm);
        ringObjs.push(loop);
    }

    // dark event-horizon core sphere at the vanishing point (z=0, far down tunnel feel)
    coreGeo = new THREE.SphereGeometry(CORE_RADIUS * 1.15, 32, 32);
    coreMat = new THREE.MeshBasicMaterial({
        color: 0x000000,
        transparent: true,
        opacity: 0.92,
        depthWrite: false,
    });
    coreObj = new THREE.Mesh(coreGeo, coreMat);
    coreObj.position.set(0, 0, NEAR_Z - (TUNNEL_DEPTH + NEAR_Z)); // at the throat
    scene.add(coreObj);

    // bright additive accretion glow ringing the dark core
    glowTex = makeGlowTexture(cGrad1.clone().lerp(cGrad2, 0.4), cGrad3);
    glowMat = new THREE.SpriteMaterial({
        map: glowTex,
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        opacity: 0.9,
    });
    glowObj = new THREE.Sprite(glowMat);
    glowObj.scale.set(CORE_RADIUS * 9, CORE_RADIUS * 9, 1);
    glowObj.position.copy(coreObj.position);
    scene.add(glowObj);
    // placeholder geometry handle kept for symmetry / disposal cleanliness
    glowGeo = new THREE.PlaneGeometry(1, 1);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera || !tunnelGeo) {
return;
}

    const t = performance.now() * 0.001;

    // endless fall: advance each particle toward the core, wrap back to the rim band
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        angles[i] += angularSpeed[i] * 0.016;
        depthT[i] += depthSpeed[i] * 0.016;

        if (depthT[i] >= 1) {
            // wrapped past the singularity → respawn at the outer/near edge
            depthT[i] -= 1;
            radii[i] = CORE_RADIUS + Math.sqrt(Math.random()) * (RING_RADIUS - CORE_RADIUS);
            angularSpeed[i] = (0.12 + Math.random() * 0.22) * (Math.random() < 0.5 ? -1 : 1);
        }

        placeParticle(i, depthT[i]);
    }

    (tunnelGeo.getAttribute('position') as THREE.BufferAttribute).needsUpdate = true;
    (tunnelGeo.getAttribute('color') as THREE.BufferAttribute).needsUpdate = true;

    // slow differential rotation of the whole swirl + gentle camera sway for depth
    if (tunnelObj) {
        tunnelObj.rotation.z = t * 0.05;
    }

    for (let r = 0; r < ringObjs.length; r++) {
        ringObjs[r].rotation.z = t * (0.04 + r * 0.012) * (r % 2 === 0 ? 1 : -1);
    }

    // pulsing accretion glow + slight breathing of the core
    if (glowObj && glowMat) {
        const pulse = 0.78 + Math.sin(t * 0.9) * 0.12;
        glowMat.opacity = pulse;
        const s = CORE_RADIUS * (8.6 + Math.sin(t * 0.7) * 0.6);
        glowObj.scale.set(s, s, 1);
    }

    camera.position.x = Math.sin(t * 0.18) * 3.2;
    camera.position.y = Math.cos(t * 0.15) * 2.4;
    camera.lookAt(0, 0, -200);

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
        if (tunnelObj) {
scene.remove(tunnelObj);
}

        if (coreObj) {
scene.remove(coreObj);
}

        if (glowObj) {
scene.remove(glowObj);
}

        for (const loop of ringObjs) {
scene.remove(loop);
}
    }

    tunnelGeo?.dispose();
    tunnelMat?.dispose();
    coreGeo?.dispose();
    coreMat?.dispose();
    glowGeo?.dispose();
    glowMat?.dispose();
    glowTex?.dispose();

    for (const g of ringGeos) {
g.dispose();
}

    for (const m of ringMats) {
m.dispose();
}

    ringGeos.length = 0;
    ringMats.length = 0;
    ringObjs.length = 0;

    renderer?.dispose();

    tunnelGeo = null;
    tunnelMat = null;
    tunnelObj = null;
    coreGeo = null;
    coreMat = null;
    coreObj = null;
    glowGeo = null;
    glowMat = null;
    glowTex = null;
    glowObj = null;
    scene = null;
    camera = null;
    renderer = null;
}

onMounted(() => {
    reducedMotion.value = !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

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
        <!-- deep-space base wash centred on the vanishing point -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(900px 900px at 50% 42%, color-mix(in srgb, var(--ak-grad-3) 30%, transparent), transparent 60%), radial-gradient(1400px 700px at 50% 120%, color-mix(in srgb, var(--ak-grad-2) 18%, transparent), transparent 64%)',
            }"
        />

        <!-- THREE.js singularity tunnel (skipped on reduced-motion) -->
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
                    'conic-gradient(from 210deg at 50% 42%, color-mix(in srgb, var(--ak-grad-1) 26%, transparent), color-mix(in srgb, var(--ak-grad-2) 26%, transparent), color-mix(in srgb, var(--ak-grad-3) 26%, transparent), color-mix(in srgb, var(--ak-grad-1) 26%, transparent))',
                filter: 'blur(40px)',
                opacity: 0.6,
            }"
        />

        <div class="ak-grid-texture pointer-events-none absolute inset-0 -z-10 opacity-40" />
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-44"
            :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }"
        />

        <!-- shared editable copy block -->
        <HeroCopy :hero="hero" :stats="stats" />
    </section>
</template>
