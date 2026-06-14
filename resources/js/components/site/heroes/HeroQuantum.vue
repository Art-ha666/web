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
    trust_items?: string[];
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

// Trust micro-row is fully editable from the admin Homepage editor (hero.trust_items).
const trustIcons = ['zap', 'check', 'sparkles'];
const trustItems = computed(() =>
    (props.hero.trust_items?.length ? props.hero.trust_items : ['One team, no hand-offs', 'Working demos weekly', 'You own the code and IP'])
        .slice(0, 3)
        .map((label, i) => ({ label, icon: trustIcons[i] ?? 'check' })),
);

/* ---------- three.js neural particle field ---------- */
let raf = 0;
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let pointsGeo: THREE.BufferGeometry | null = null;
let pointsMat: THREE.PointsMaterial | null = null;
let pointsObj: THREE.Points | null = null;
let lineGeo: THREE.BufferGeometry | null = null;
let lineMat: THREE.LineBasicMaterial | null = null;
let lineObj: THREE.LineSegments | null = null;

const COUNT = 120;
const FIELD = { x: 90, y: 55, z: 60 };
const LINK_DIST = 18;
const MAX_LINKS = COUNT * 8; // upper bound of line segments per frame

const positions = new Float32Array(COUNT * 3);
const velocities = new Float32Array(COUNT * 3);

function readThemeColor(varName: string, fallback: string): THREE.Color {
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

    const cGrad1 = readThemeColor('--ak-grad-1', '#22d3ee'); // cyan
    const cGrad2 = readThemeColor('--ak-grad-2', '#6366f1'); // indigo
    const cGrad3 = readThemeColor('--ak-grad-3', '#a855f7'); // violet

    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(60, width / height, 1, 600);
    camera.position.z = 95;

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.setSize(width, height, false);

    // seed nodes + a soft per-node colour blend across the gradient
    const pointColors = new Float32Array(COUNT * 3);

    for (let i = 0; i < COUNT; i++) {
        const i3 = i * 3;
        positions[i3] = (Math.random() - 0.5) * FIELD.x * 2;
        positions[i3 + 1] = (Math.random() - 0.5) * FIELD.y * 2;
        positions[i3 + 2] = (Math.random() - 0.5) * FIELD.z * 2;

        velocities[i3] = (Math.random() - 0.5) * 0.08;
        velocities[i3 + 1] = (Math.random() - 0.5) * 0.08;
        velocities[i3 + 2] = (Math.random() - 0.5) * 0.08;

        const t = i / COUNT;
        const blend = new THREE.Color();

        if (t < 0.5) {
            blend.copy(cGrad1).lerp(cGrad2, t * 2);
        } else {
            blend.copy(cGrad2).lerp(cGrad3, (t - 0.5) * 2);
        }

        pointColors[i3] = blend.r;
        pointColors[i3 + 1] = blend.g;
        pointColors[i3 + 2] = blend.b;
    }

    pointsGeo = new THREE.BufferGeometry();
    pointsGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    pointsGeo.setAttribute('color', new THREE.BufferAttribute(pointColors, 3));

    pointsMat = new THREE.PointsMaterial({
        size: 2.2,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    pointsObj = new THREE.Points(pointsGeo, pointsMat);
    scene.add(pointsObj);

    // line segment buffer (positions + colors) reused each frame
    lineGeo = new THREE.BufferGeometry();
    lineGeo.setAttribute('position', new THREE.BufferAttribute(new Float32Array(MAX_LINKS * 2 * 3), 3));
    lineGeo.setAttribute('color', new THREE.BufferAttribute(new Float32Array(MAX_LINKS * 2 * 3), 3));
    lineMat = new THREE.LineBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 0.5,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    lineObj = new THREE.LineSegments(lineGeo, lineMat);
    scene.add(lineObj);

    window.addEventListener('resize', onResize, { passive: true });
    animate();
}

function buildLinks() {
    if (!lineGeo) {
return;
}

    const linePos = lineGeo.getAttribute('position') as THREE.BufferAttribute;
    const lineCol = lineGeo.getAttribute('color') as THREE.BufferAttribute;
    const lp = linePos.array as Float32Array;
    const lc = lineCol.array as Float32Array;
    const pc = (pointsGeo!.getAttribute('color') as THREE.BufferAttribute).array as Float32Array;

    let seg = 0;
    const linkSq = LINK_DIST * LINK_DIST;

    for (let i = 0; i < COUNT && seg < MAX_LINKS; i++) {
        const i3 = i * 3;
        const ax = positions[i3];
        const ay = positions[i3 + 1];
        const az = positions[i3 + 2];

        for (let j = i + 1; j < COUNT && seg < MAX_LINKS; j++) {
            const j3 = j * 3;
            const dx = ax - positions[j3];
            const dy = ay - positions[j3 + 1];
            const dz = az - positions[j3 + 2];
            const dSq = dx * dx + dy * dy + dz * dz;

            if (dSq > linkSq) {
continue;
}

            const fade = 1 - Math.sqrt(dSq) / LINK_DIST;
            const o = seg * 6;

            lp[o] = ax;
            lp[o + 1] = ay;
            lp[o + 2] = az;
            lp[o + 3] = positions[j3];
            lp[o + 4] = positions[j3 + 1];
            lp[o + 5] = positions[j3 + 2];

            // colour each endpoint by its node colour, dimmed by distance fade
            lc[o] = pc[i3] * fade;
            lc[o + 1] = pc[i3 + 1] * fade;
            lc[o + 2] = pc[i3 + 2] * fade;
            lc[o + 3] = pc[j3] * fade;
            lc[o + 4] = pc[j3 + 1] * fade;
            lc[o + 5] = pc[j3 + 2] * fade;

            seg++;
        }
    }

    lineGeo.setDrawRange(0, seg * 2);
    linePos.needsUpdate = true;
    lineCol.needsUpdate = true;
}

function animate() {
    raf = requestAnimationFrame(animate);

    if (!renderer || !scene || !camera || !pointsGeo) {
return;
}

    // drift + soft bounce inside the field
    for (let i = 0; i < COUNT; i++) {
        const i3 = i * 3;
        positions[i3] += velocities[i3];
        positions[i3 + 1] += velocities[i3 + 1];
        positions[i3 + 2] += velocities[i3 + 2];

        if (positions[i3] > FIELD.x || positions[i3] < -FIELD.x) {
velocities[i3] *= -1;
}

        if (positions[i3 + 1] > FIELD.y || positions[i3 + 1] < -FIELD.y) {
velocities[i3 + 1] *= -1;
}

        if (positions[i3 + 2] > FIELD.z || positions[i3 + 2] < -FIELD.z) {
velocities[i3 + 2] *= -1;
}
    }

    (pointsGeo.getAttribute('position') as THREE.BufferAttribute).needsUpdate = true;

    buildLinks();

    const t = performance.now() * 0.00006;

    if (pointsObj) {
        pointsObj.rotation.y = t;
        pointsObj.rotation.x = Math.sin(t * 0.6) * 0.18;
    }

    if (lineObj) {
        lineObj.rotation.y = t;
        lineObj.rotation.x = Math.sin(t * 0.6) * 0.18;
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

    pointsGeo?.dispose();
    pointsMat?.dispose();
    lineGeo?.dispose();
    lineMat?.dispose();

    if (scene) {
        if (pointsObj) {
scene.remove(pointsObj);
}

        if (lineObj) {
scene.remove(lineObj);
}
    }

    renderer?.dispose();

    pointsGeo = null;
    pointsMat = null;
    pointsObj = null;
    lineGeo = null;
    lineMat = null;
    lineObj = null;
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
        <!-- deep base wash -->
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'radial-gradient(1200px 620px at 70% -10%, color-mix(in srgb, var(--ak-grad-2) 30%, transparent), transparent 62%), radial-gradient(900px 520px at 8% 110%, color-mix(in srgb, var(--ak-grad-3) 22%, transparent), transparent 60%)',
            }"
        />

        <!-- THREE.js neural particle field (skipped on reduced-motion) -->
        <canvas
            v-if="!reducedMotion"
            ref="canvasEl"
            class="pointer-events-none absolute inset-0 -z-10 h-full w-full"
            aria-hidden="true"
        />

        <!-- static gradient wash fallback for reduced motion -->
        <div
            v-else
            class="pointer-events-none absolute inset-0 -z-10"
            :style="{
                background:
                    'conic-gradient(from 210deg at 60% 30%, color-mix(in srgb, var(--ak-grad-1) 26%, transparent), color-mix(in srgb, var(--ak-grad-2) 26%, transparent), color-mix(in srgb, var(--ak-grad-3) 26%, transparent), color-mix(in srgb, var(--ak-grad-1) 26%, transparent))',
                filter: 'blur(40px)',
                opacity: 0.6,
            }"
        />

        <div class="ak-grid-texture pointer-events-none absolute inset-0 -z-10 opacity-40" />
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-44"
            :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }"
        />

        <!-- Copy -->
        <div class="ak-container relative pb-24 pt-36 sm:pt-44 lg:pb-32">
            <div class="mx-auto max-w-3xl text-center">
                <div
                    class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-glass-border bg-[var(--ak-glass)] px-4 py-1.5 text-xs font-medium text-ak-text-2 backdrop-blur"
                    style="animation-delay: 0s"
                >
                    <span class="relative flex h-2 w-2">
                        <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                        <span class="relative inline-flex h-2 w-2 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                    </span>
                    {{ hero.badge ?? 'AI-native engineering · Built to scale' }}
                </div>

                <p
                    v-if="hero.eyebrow"
                    class="ak-fade-up ak-eyebrow mx-auto mt-7 justify-center"
                    style="animation-delay: 0.06s"
                >
                    {{ hero.eyebrow }}
                </p>

                <h1
                    class="ak-fade-up mt-6 font-display text-[2.7rem] font-semibold leading-[1.03] tracking-tight text-ak-text sm:text-6xl lg:text-[4.4rem]"
                    style="animation-delay: 0.1s"
                >
                    <span>
                        <template v-for="(part, i) in line1Parts" :key="i">
                            <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                            <span v-else>{{ part.text }}</span>
                        </template>
                    </span>
                    <span class="block text-ak-text-2">{{ hero.headline_line2 }}</span>
                </h1>

                <p class="ak-fade-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-ak-text-2" style="animation-delay: 0.2s">
                    {{ hero.subhead }}
                </p>

                <div class="ak-fade-up mt-9 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 0.3s">
                    <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                        {{ hero.primary_label ?? 'Start a project' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                        <Icon name="sparkles" :size="18" />
                        {{ hero.secondary_label ?? 'Explore our work' }}
                    </AkButton>
                </div>

                <!-- trust micro-row (editable) -->
                <div
                    v-if="trustItems.length"
                    class="ak-fade-up mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-ak-text-3"
                    style="animation-delay: 0.38s"
                >
                    <span v-for="(item, i) in trustItems" :key="i" class="inline-flex items-center gap-1.5">
                        <Icon
                            :name="item.icon"
                            :size="14"
                            :class="item.icon === 'check' ? 'text-emerald-400' : ''"
                            :style="item.icon !== 'check' ? { color: i === 0 ? 'var(--ak-grad-1)' : 'var(--ak-grad-3)' } : undefined"
                        />
                        {{ item.label }}
                    </span>
                </div>
            </div>

            <!-- hero stat strip -->
            <div
                v-if="stats.length"
                class="ak-fade-up ak-glass mx-auto mt-14 grid max-w-3xl grid-cols-2 gap-px overflow-hidden rounded-2xl sm:grid-cols-4"
                style="animation-delay: 0.46s"
            >
                <div
                    v-for="(stat, i) in stats"
                    :key="i"
                    class="bg-[var(--ak-glass)] px-5 py-6 text-center"
                >
                    <div class="font-display text-2xl font-bold text-ak-text sm:text-3xl">
                        <span v-if="stat.prefix" class="ak-gradient-text">{{ stat.prefix }}</span
                        ><span>{{ stat.value }}</span
                        ><span v-if="stat.suffix" class="ak-gradient-text">{{ stat.suffix }}</span>
                    </div>
                    <div class="mt-1 text-xs text-ak-text-3">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
