<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import { useSite } from '@/composables/useSite';

const props = defineProps<{ content: Record<string, any>; services: string[]; budgetRanges: string[] }>();

const { site } = useSite();
const c = computed(() => props.content ?? {});
const promises = computed<string[]>(() => c.value.promises?.items ?? []);
const showPromises = computed(() => c.value.promises?.enabled !== false && promises.value.length > 0);
const reassurance = computed<string[]>(() => c.value.form?.reassurance ?? []);

const form = useForm({
    name: '',
    business_email: '',
    company: '',
    phone: '',
    budget_range: '',
    service_interest: '',
    message: '',
    consent_data_processing: false as boolean,
    consent_marketing: false as boolean,
    website: '',
    source_page: '/contact',
});

function submit() {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const inputClass =
    'ak-focusable h-12 w-full rounded-xl border border-ak-glass-border bg-[var(--ak-surface)] px-4 text-sm text-ak-text placeholder:text-ak-text-3 transition focus:border-ak-primary';
</script>

<template>
    <Head>
        <title>{{ c.seo?.title || 'Contact - AKH Solutions' }}</title>
        <meta name="description" :content="c.seo?.description" />
    </Head>

    <section class="ak-container grid gap-14 pb-24 pt-36 lg:grid-cols-[1.1fr_0.9fr] lg:pt-44">
        <!-- Left: intro + contact info -->
        <div>
            <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
            <h1 class="mt-5 font-display text-4xl font-semibold leading-tight tracking-tight text-ak-text sm:text-5xl">
                {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>
            </h1>
            <p class="mt-5 max-w-md text-lg text-ak-text-2">
                {{ c.hero?.subtitle }}
            </p>

            <div class="mt-10 space-y-4">
                <a v-if="site.email" :href="`mailto:${site.email}`" class="ak-card ak-card-hover flex items-center gap-4 p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl ak-gradient-bg text-white"><Icon name="mail" :size="20" /></span>
                    <span><span class="block text-xs text-ak-text-3">{{ c.info?.email_label }}</span><span class="font-medium text-ak-text">{{ site.email }}</span></span>
                </a>
                <a v-if="site.phone" :href="`tel:${site.phone}`" class="ak-card ak-card-hover flex items-center gap-4 p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl ak-gradient-bg text-white"><Icon name="phone" :size="20" /></span>
                    <span><span class="block text-xs text-ak-text-3">{{ c.info?.phone_label }}</span><span class="font-medium text-ak-text">{{ site.phone }}</span></span>
                </a>
                <div v-if="site.locations?.length" class="ak-card flex items-center gap-4 p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl ak-gradient-bg text-white"><Icon name="map-pin" :size="20" /></span>
                    <span><span class="block text-xs text-ak-text-3">{{ c.info?.locations_label }}</span><span class="font-medium text-ak-text">{{ site.locations.join(' · ') }}</span></span>
                </div>
            </div>

            <ul v-if="showPromises" class="mt-10 space-y-3 text-sm text-ak-text-2">
                <li v-for="(promise, i) in promises" :key="i" class="flex items-center gap-2">
                    <Icon name="check" :size="16" :style="{ color: 'var(--ak-primary)' }" /> {{ promise }}
                </li>
            </ul>
        </div>

        <!-- Right: form -->
        <div class="ak-glass p-7 sm:p-8">
            <div v-if="form.wasSuccessful" class="flex h-full min-h-[24rem] flex-col items-center justify-center text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-full ak-gradient-bg text-white"><Icon name="check" :size="32" /></span>
                <h2 class="mt-6 font-display text-2xl font-semibold text-ak-text">{{ c.form?.success_title }}</h2>
                <p class="mt-3 max-w-xs text-ak-text-2">{{ c.form?.success_body }}</p>
            </div>

            <form v-else novalidate @submit.prevent="submit">
                <!-- honeypot -->
                <input v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-ak-text-2">{{ c.form?.name_label }}</label>
                        <input v-model="form.name" type="text" :class="inputClass" :placeholder="c.form?.name_placeholder" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ak-text-2">{{ c.form?.email_label }}</label>
                        <input v-model="form.business_email" type="email" :class="inputClass" :placeholder="c.form?.email_placeholder" />
                        <p v-if="form.errors.business_email" class="mt-1 text-xs text-rose-400">{{ form.errors.business_email }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ak-text-2">{{ c.form?.company_label }}</label>
                        <input v-model="form.company" type="text" :class="inputClass" :placeholder="c.form?.company_placeholder" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ak-text-2">{{ c.form?.budget_label }}</label>
                        <select v-model="form.budget_range" :class="inputClass">
                            <option value="">{{ c.form?.budget_placeholder }}</option>
                            <option v-for="range in budgetRanges" :key="range" :value="range">{{ range }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ak-text-2">{{ c.form?.service_label }}</label>
                        <select v-model="form.service_interest" :class="inputClass">
                            <option value="">{{ c.form?.service_placeholder }}</option>
                            <option v-for="svc in services" :key="svc" :value="svc">{{ svc }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-ak-text-2">{{ c.form?.message_label }}</label>
                        <textarea v-model="form.message" rows="4" class="ak-focusable w-full rounded-xl border border-ak-glass-border bg-[var(--ak-surface)] px-4 py-3 text-sm text-ak-text placeholder:text-ak-text-3 transition focus:border-ak-primary" :placeholder="c.form?.message_placeholder" />
                        <p v-if="form.errors.message" class="mt-1 text-xs text-rose-400">{{ form.errors.message }}</p>
                    </div>
                </div>

                <label class="mt-5 flex cursor-pointer items-start gap-3 text-sm text-ak-text-2">
                    <input v-model="form.consent_data_processing" type="checkbox" class="mt-0.5 h-4 w-4 shrink-0 accent-[var(--ak-primary)]" />
                    <!-- Admin-managed content; links inside must render. -->
                    <span v-html="c.form?.consent_label" />
                </label>
                <p v-if="form.errors.consent_data_processing" class="ml-7 mt-1 text-xs text-rose-400">{{ form.errors.consent_data_processing }}</p>

                <label class="mt-3 flex cursor-pointer items-start gap-3 text-sm text-ak-text-2">
                    <input v-model="form.consent_marketing" type="checkbox" class="mt-0.5 h-4 w-4 shrink-0 accent-[var(--ak-primary)]" />
                    <span>{{ c.form?.marketing_label }}</span>
                </label>

                <button type="submit" :disabled="form.processing" class="ak-btn ak-btn-gradient ak-focusable mt-7 w-full" :class="form.processing ? 'opacity-60' : ''">
                    <Icon name="send" :size="18" />
                    {{ form.processing ? c.form?.sending_label : c.form?.submit_label }}
                </button>

                <ul v-if="reassurance.length" class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 text-xs text-ak-text-3">
                    <li v-for="(item, i) in reassurance" :key="i" class="flex items-center gap-1.5">
                        <Icon name="check" :size="14" :style="{ color: 'var(--ak-primary)' }" /> {{ item }}
                    </li>
                </ul>
            </form>
        </div>
    </section>
</template>
