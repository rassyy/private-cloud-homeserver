<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import StorageUsageBar from './StorageUsageBar.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const emit = defineEmits(['new-file', 'trigger-upload']);

const navItems = [
    { name: 'My Files', icon: 'folder_shared', route: 'folders.index', url: '/files', filled: true },
    { name: 'Shared with me', icon: 'group', route: 'folders.shared', url: '/shared', filled: false },
    { name: 'Recent', icon: 'history', route: 'folders.recent', url: '/recent', filled: false },
    { name: 'Starred', icon: 'star', route: 'folders.starred', url: '/starred', filled: false },
    { name: 'Trash', icon: 'delete', route: 'folders.trash', url: '/trash', filled: false },
];

function isActive(item) {
    if (!item.route) return false;
    const currentPath = page.url.split('?')[0];
    if (item.name === 'My Files') {
        return currentPath.startsWith('/files') || currentPath.startsWith('/folders') || currentPath === '/dashboard';
    }
    return currentPath.startsWith(item.url);
}

function triggerNewFile() {
    window.dispatchEvent(new CustomEvent('trigger-upload'));
    emit('new-file');
    emit('trigger-upload');
}
</script>

<template>
    <aside class="flex-col bg-white w-[280px] h-screen py-6 px-4 z-40 border-r border-slate-200/50 flex-shrink-0 shadow-[1px_0_10px_rgba(0,0,0,0.02)]">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-8 px-2">
            <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-100 shadow-sm flex items-center justify-center">
                <span class="material-symbols-outlined text-slate-900 text-[18px]" style="font-variation-settings: 'FILL' 1;">cloud</span>
            </div>
            <div>
                <h1 class="font-geist text-lg tracking-tight font-semibold text-slate-900">Self Cloud</h1>
                <p class="text-xs font-medium text-slate-500">Self Home Storage</p>
            </div>
        </div>

        <!-- New File CTA -->
        <button
            class="w-full flex items-center justify-center gap-2 bg-slate-900 text-white rounded-xl py-2.5 px-4 mb-6 hover:bg-slate-800 transition-all duration-300 ease-in-out text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5"
            @click="triggerNewFile"
        >
            <span class="material-symbols-outlined text-[20px]">add</span>
            New File
        </button>

        <!-- Navigation -->
        <nav class="flex-1 flex flex-col gap-1">
            <template v-for="item in navItems" :key="item.name">
                <Link
                    v-if="item.route"
                    :href="route(item.route)"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-300 ease-in-out text-sm',
                        isActive(item)
                            ? 'bg-slate-100 text-slate-900 font-medium shadow-sm'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                    ]"
                >
                    <span
                        class="material-symbols-outlined text-[20px]"
                        :style="item.filled && isActive(item) ? `font-variation-settings: 'FILL' 1;` : ''"
                    >{{ item.icon }}</span>
                    {{ item.name }}
                </Link>
                <a
                    v-else
                    href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all duration-300 ease-in-out text-sm cursor-not-allowed opacity-60"
                >
                    <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                    {{ item.name }}
                </a>
            </template>
        </nav>

        <!-- Footer / Storage -->
        <div class="mt-auto flex flex-col gap-3 pt-4 border-t border-slate-200/50">
            <StorageUsageBar />
            <div class="text-xs text-slate-400/80 px-2 pt-1 font-medium select-none">
                private cloud storage by rasy.
            </div>
        </div>
    </aside>
</template>
