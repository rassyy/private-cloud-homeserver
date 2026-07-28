<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/CloudVault/Sidebar.vue';
import Topbar from '@/Components/CloudVault/Topbar.vue';

const showMobileSidebar = ref(false);
</script>

<template>
    <div class="flex h-screen w-full bg-slate-50/50 text-slate-900 antialiased selection:bg-primary/20 overflow-hidden">
        <!-- Sidebar (Desktop) -->
        <Sidebar class="hidden md:flex" />

        <!-- Mobile Sidebar Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showMobileSidebar"
                class="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm md:hidden"
                @click="showMobileSidebar = false"
            >
                <Sidebar
                    class="flex w-[280px] h-full"
                    @click.stop
                />
            </div>
        </Transition>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-transparent relative">
            <Topbar @toggle-sidebar="showMobileSidebar = !showMobileSidebar" />

            <div class="flex-1 overflow-hidden">
                <slot />
            </div>
        </main>
    </div>
</template>
