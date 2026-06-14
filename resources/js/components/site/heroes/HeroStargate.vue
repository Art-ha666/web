<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import HeroCopy from './HeroCopy.vue';

defineProps<{ hero: Record<string, any>; stats: any[] }>();

const canvasEl = ref<HTMLCanvasElement | null>(null);
const reducedMotion = ref(false);

/* ---------- three.js stargate portal ---------- */
let raf = 0;
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;

// portal group that gently rotates/sways as one
let portal: THREE.Group | null = null;

// outer rotating ring (torus wireframe)
let ringGeo: THREE.TorusGeometry | null = null;
let ringMat: THREE.MeshBasicMaterial | null = null;
let ringObj: THREE.Mesh | null = null;

// secondary counter-rotating thin ring for depth
let ring2Geo: THREE.TorusGeometry | null = null;
let ring2Mat: THREE.MeshBasicMaterial | null = null;
let ring2Obj: THREE.Mesh | null = null;

// chevron glints studded around the ring (pulse outward in sequence)
let chevronGeo: THREE.BufferGeometry | null = null;
let chevronMat: THREE.PointsMaterial | null = null;
let chevronObj: THREE.Points | null = null;

// inner event-horizon vortex (particles spiralling within the ring plane)
let vortexGeo: THREE.BufferGeometry | null = null;
let vortexMat: THREE.PointsMaterial | null = null;
let vortexObj: THREE.Points | null = null;

// bright additive shimmer glow filling the gate
let glowMat: THREE.SpriteMaterial | null = null;
let glowTex: THREE.CanvasTexture | null = null;
let glowObj: THREE.Sprite | null = null;

// geometry constants
const RING_RADIUS = 30; // gate radius
const TUBE_RADIUS = 1.6; // torus tube thickness
const VORTEX_COUNT = 2400; // spiralling event-horizon particles (capped)
const CHEVRON_COUNT = 24; // glints around the ring

// per-vortex persistent state (no per-frame allocation in animate)
const vAngle = new Float32Array(VORTEX_COUNT); // current swirl angle
const vRadiusNorm = new Float32Array(VORTEX_COUNT); // 0 = centre, 1 = rim
const vAngSpeed = new Float32Array(VORTEX_COUNT); // angular velocity
const vInSpeed = new Float32Array(VORTEX_COUNT); // inward drift speed
const vortexPos = new Float32Array(VORTEX_COUNT * 3);
const vortexCol = new Float32Array(VORTEX_COUNT * 3);

// per-chevron persistent state
const chevPos = new Float32Array(CHEVRON_COUNT * 3);
const chevCol = new Float32Array(CHEVRON_COUNT * 3);
const chevBaseAngle = new Float32Array(CHEVRON_COUNT);
const chevPhase = new Float32Array(CHEVRON_COUNT);

// scratch colour reused every frame, never reallocated
const scratchColor = new THREE.Color();
let cGrad1 = new THREE.Color('#22d3ee'); // cyan (rim)
let cGrad2 = new THREE.Color('#6366f1'); // indigo (mid)
let cGrad3 = new THREE.Color('#a855f7'); // violet (core)

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

/** Soft radial sprite for the shimmering event-horizon glow. */
function makeGlowTexture(inner: THREE.Color, outer: THREE.Color): THREE.CanvasTexture {
    const size = 256;
    const cv = document.createElement('canvas');
    cv.width = size;
    cv.height = size;
    const ctx = cv.getContext('2d');

    if (ctx) {
        const g = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
        g.addColorStop(0.0, `rgba(255,255,255,0.85)`);
        g.addColorStop(0.22, `rgba(${(inner.r * 255) | 0},${(inner.g * 255) | 0},${(inner.b * 255) | 0},0.55)`);
        g.addColorStop(0.6, `rgba(${(outer.r * 255) | 0},${(outer.g * 255) | 0},${(outer.b * 255) | 0},0.22)`);
        g.addColorStop(1.0, `rgba(0,0,0,0)`);
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
    }

    const tex = new THREE.CanvasTexture(cv);
    tex.needsUpdate = true;

    return tex;
}

/** Position a vortex particle from its angle + normalised radius. */
function placeVortex(i: number): void {
    const i3 = i * 3;
    const r = vRadiusNorm[i] * RING_RADIUS;
    const a = vAngle[i];

    vortexPos[i3] = Math.cos(a) * r;
    vortexPos[i3 + 1] = Math.sin(a) * r;
    // subtle out-of-plane shimmer so the horizon reads as volumetric, not flat
    vortexPos[i3 + 2] = Math.sin(a * 3 + vRadiusNorm[i] * 8) * 1.4;

    colorByRadius(vRadiusNorm[i], scratchColor);
    // brighten toward the rim, dim toward the dark throat
    const dim = 0.28 + 0.72 * vRadiusNorm[i];
    vortexCol[i3] = scratchColor.r * dim;
    vortexCol[i3 + 1] = scratchColor.g * dim;
    vortexCol[i3 + 2] = scratchColor.b * dim;
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

    cGrad1 = readThemeColor('--ak-grad-1', '#22d3ee'); // cyan (rim)
    cGrad2 = readThemeColor('--ak-grad-2', '#6366f1'); // indigo (mid)
    cGrad3 = readThemeColor('--ak-grad-3', '#a855f7'); // violet (core)

    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
    camera.position.set(0, 0, 78); // looks straight at the gate
    camera.lookAt(0, 0, 0);

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    portal = new THREE.Group();
    scene.add(portal);

    // ---- outer rotating ring (torus wireframe) ----
    ringGeo = new THREE.TorusGeometry(RING_RADIUS, TUBE_RADIUS, 12, 140);
    ringMat = new THREE.MeshBasicMaterial({
        color: cGrad1.clone().lerp(cGrad2, 0.3),
        wireframe: true,
        transparent: true,
        opacity: 0.55,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    ringObj = new THREE.Mesh(ringGeo, ringMat);
    portal.add(ringObj);

    // ---- secondary thin counter-rotating ring ----
    ring2Geo = new THREE.TorusGeometry(RING_RADIUS + 2.4, 0.5, 8, 160);
    ring2Mat = new THREE.MeshBasicMaterial({
        color: cGrad3.clone().lerp(cGrad2, 0.4),
        wireframe: true,
        transparent: true,
        opacity: 0.35,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    ring2Obj = new THREE.Mesh(ring2Geo, ring2Mat);
    portal.add(ring2Obj);

    // ---- chevron glints studded around the ring ----
    for (let i = 0; i < CHEVRON_COUNT; i++) {
        const a = (i / CHEVRON_COUNT) * Math.PI * 2;
        chevBaseAngle[i] = a;
        chevPhase[i] = (i / CHEVRON_COUNT) * Math.PI * 2;
        const i3 = i * 3;
        chevPos[i3] = Math.cos(a) * (RING_RADIUS + TUBE_RADIUS);
        chevPos[i3 + 1] = Math.sin(a) * (RING_RADIUS + TUBE_RADIUS);
        chevPos[i3 + 2] = 0;

        colorByRadius(0.9, scratchColor);
        chevCol[i3] = scratchColor.r;
        chevCol[i3 + 1] = scratchColor.g;
        chevCol[i3 + 2] = scratchColor.b;
    }

    chevronGeo = new THREE.BufferGeometry();
    chevronGeo.setAttribute('position', new THREE.BufferAttribute(chevPos, 3));
    chevronGeo.setAttribute('color', new THREE.BufferAttribute(chevCol, 3));
    chevronMat = new THREE.PointsMaterial({
        size: 4.2,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 0.95,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    chevronObj = new THREE.Points(chevronGeo, chevronMat);
    portal.add(chevronObj);

    // ---- inner event-horizon vortex ----
    for (let i = 0; i < VORTEX_COUNT; i++) {
        // sqrt keeps the disc evenly filled rather than clumping at the centre
        vRadiusNorm[i] = Math.sqrt(Math.random());
        vAngle[i] = Math.random() * Math.PI * 2;
        vAngSpeed[i] = 0.4 + Math.random() * 0.7; // all swirl the same way for a vortex
        vInSpeed[i] = 0.02 + Math.random() * 0.05;
        placeVortex(i);
    }

    vortexGeo = new THREE.BufferGeometry();
    vortexGeo.setAttribute('position', new THREE.BufferAttribute(vortexPos, 3));
    vortexGeo.setAttribute('color', new THREE.BufferAttribute(vortexCol, 3));
    vortexMat = new THREE.PointsMaterial({
        size: 1.7,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    vortexObj = new THREE.Points(vortexGeo, vortexMat);
    portal.add(vortexObj);

    // ---- shimmering event-horizon glow behind the vortex ----
    glowTex = makeGlowTexture(cGrad1.clone().lerp(cGrad2, 0.4), cGrad3);
    glowMat = new THREE.SpriteMaterial({
        map: glowTex,
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        opacity: 0.8,
    });
    glowObj = new THREE.Sprite(glowMat);
    glowObj.scale.set(RING_RADIUS * 2.2, RING_RADIUS * 2.2, 1);
    glowObj.position.set(0, 0, -2);
    portal.add(glowObj);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera) {
        return;
    }

    const t = performance.now() * 0.001;

    // ---- swirl the event-horizon vortex ----
    if (vortexGeo) {
        for (let i = 0; i < VORTEX_COUNT; i++) {
            // inner particles spin faster (differential rotation = vortex feel)
            vAngle[i] += vAngSpeed[i] * (1.4 - vRadiusNorm[i]) * 0.016;
            vRadiusNorm[i] -= vInSpeed[i] * 0.016;

            if (vRadiusNorm[i] <= 0.02) {
                // sucked into the throat → respawn out at the rim
                vRadiusNorm[i] = 1;
                vAngle[i] = Math.random() * Math.PI * 2;
                vAngSpeed[i] = 0.4 + Math.random() * 0.7;
                vInSpeed[i] = 0.02 + Math.random() * 0.05;
            }

            placeVortex(i);
        }

        (vortexGeo.getAttribute('position') as THREE.BufferAttribute).needsUpdate = true;
        (vortexGeo.getAttribute('color') as THREE.BufferAttribute).needsUpdate = true;
    }

    // ---- pulse the chevron glints in a travelling wave around the ring ----
    if (chevronGeo) {
        for (let i = 0; i < CHEVRON_COUNT; i++) {
            const i3 = i * 3;
            const pulse = 0.5 + 0.5 * Math.sin(t * 2.2 - chevPhase[i] * 2);
            const a = chevBaseAngle[i];
            // glints breathe outward from the ring on the pulse
            const r = RING_RADIUS + TUBE_RADIUS + pulse * 2.4;
            chevPos[i3] = Math.cos(a) * r;
            chevPos[i3 + 1] = Math.sin(a) * r;

            colorByRadius(0.85, scratchColor);
            const b = 0.35 + 0.65 * pulse;
            chevCol[i3] = scratchColor.r * b;
            chevCol[i3 + 1] = scratchColor.g * b;
            chevCol[i3 + 2] = scratchColor.b * b;
        }

        (chevronGeo.getAttribute('position') as THREE.BufferAttribute).needsUpdate = true;
        (chevronGeo.getAttribute('color') as THREE.BufferAttribute).needsUpdate = true;
    }

    // ---- ring rotation: slow spin of the gate ----
    if (ringObj) {
        ringObj.rotation.z = t * 0.12;
    }

    if (ring2Obj) {
        ring2Obj.rotation.z = -t * 0.18;
    }

    // ---- shimmer the glow ----
    if (glowObj && glowMat) {
        const shimmer = 0.66 + Math.sin(t * 1.3) * 0.14;
        glowMat.opacity = shimmer;
        const s = RING_RADIUS * (2.1 + Math.sin(t * 0.9) * 0.12);
        glowObj.scale.set(s, s, 1);
    }

    // ---- gentle awe-inspiring sway of the whole portal for depth ----
    if (portal) {
        portal.rotation.y = Math.sin(t * 0.22) * 0.16;
        portal.rotation.x = Math.cos(t * 0.18) * 0.1;
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

    if (scene && portal) {
        if (ringObj) {
            portal.remove(ringObj);
        }

        if (ring2Obj) {
            portal.remove(ring2Obj);
        }

        if (chevronObj) {
            portal.remove(chevronObj);
        }

        if (vortexObj) {
            portal.remove(vortexObj);
        }

        if (glowObj) {
            portal.remove(glowObj);
        }

        scene.remove(portal);
    }

    ringGeo?.dispose();
    ringMat?.dispose();
    ring2Geo?.dispose();
    ring2Mat?.dispose();
    chevronGeo?.dispose();
    chevronMat?.dispose();
    vortexGeo?.dispose();
    vortexMat?.dispose();
    glowMat?.dispose();
    glowTex?.dispose();

    renderer?.dispose();

    ringGeo = null;
    ringMat = null;
    ringObj = null;
    ring2Geo = null;
    ring2Mat = null;
    ring2Obj = null;
    chevronGeo = null;
    chevronMat = null;
    chevronObj = null;
    vortexGeo = null;
    vortexMat = null;
    vortexObj = null;
    glowMat = null;
    glowTex = null;
    glowObj = null;
    portal = null;
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
        <!-- deep-space base wash centred on the gate -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(820px 820px at 50% 40%, color-mix(in srgb, var(--ak-grad-2) 28%, transparent), transparent 60%), radial-gradient(1400px 700px at 50% 120%, color-mix(in srgb, var(--ak-grad-3) 18%, transparent), transparent 64%)',
            }"
        />

        <!-- THREE.js stargate portal (skipped on reduced-motion) -->
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
                    'conic-gradient(from 210deg at 50% 40%, color-mix(in srgb, var(--ak-grad-1) 26%, transparent), color-mix(in srgb, var(--ak-grad-2) 26%, transparent), color-mix(in srgb, var(--ak-grad-3) 26%, transparent), color-mix(in srgb, var(--ak-grad-1) 26%, transparent))',
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
