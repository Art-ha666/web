<script setup lang="ts">
import {
    ArrowRight,
    ArrowUpRight,
    Calendar,
    Check,
    ChevronDown,
    Clock,
    Code2,
    Compass,
    Github,
    Hammer,
    Linkedin,
    Mail,
    MapPin,
    Menu,
    MessageCircle,
    PenTool,
    Phone,
    Quote,
    Rocket,
    Send,
    Server,
    Smartphone,
    Sparkles,
    Star,
    Twitter,
    Users,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed  } from 'vue';
import type {Component} from 'vue';

const props = defineProps<{ name: string; size?: number; strokeWidth?: number }>();

const map: Record<string, Component> = {
    'code-2': Code2,
    smartphone: Smartphone,
    sparkles: Sparkles,
    server: Server,
    users: Users,
    compass: Compass,
    'pen-tool': PenTool,
    hammer: Hammer,
    rocket: Rocket,
    menu: Menu,
    x: X,
    'arrow-right': ArrowRight,
    'arrow-up-right': ArrowUpRight,
    mail: Mail,
    phone: Phone,
    'map-pin': MapPin,
    linkedin: Linkedin,
    github: Github,
    twitter: Twitter,
    send: Send,
    star: Star,
    check: Check,
    'chevron-down': ChevronDown,
    quote: Quote,
    calendar: Calendar,
    clock: Clock,
    zap: Zap,
    'message-circle': MessageCircle,
};

const icon = computed<Component>(() => map[props.name] ?? Sparkles);
</script>

<template>
    <component :is="icon" :size="size ?? 20" :stroke-width="strokeWidth ?? 2" />
</template>
