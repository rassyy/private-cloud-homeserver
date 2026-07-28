<script setup>
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineEmits(['toggle-sidebar']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const searchQuery = ref(page.props.filters?.search || '');
const showUserMenu = ref(false);
let searchTimeout = null;

watch(searchQuery, (newVal) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('folders.index'), { search: newVal ? newVal : undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

watch(() => page.props.filters?.search, (newVal) => {
    if ((newVal || '') !== searchQuery.value) {
        searchQuery.value = newVal || '';
    }
});

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <header class="flex justify-between items-center w-full px-8 h-16 sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md border-b border-slate-200/50">
        <!-- Mobile Menu Button -->
        <button
            class="md:hidden p-2 rounded-xl text-slate-500 hover:bg-white hover:text-slate-900 transition-all duration-300"
            @click="$emit('toggle-sidebar')"
        >
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Search Bar -->
        <div class="flex-1 max-w-xl relative group">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors duration-300 pointer-events-none text-[20px]">search</span>
            <input
                v-model="searchQuery"
                class="w-full bg-white border border-slate-200/80 rounded-xl py-2 pl-10 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all duration-300 ease-in-out shadow-sm placeholder:text-slate-400"
                placeholder="Search Self Cloud..."
                type="text"
            />
            <button
                v-if="searchQuery"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 rounded-lg flex items-center justify-center transition-colors"
                @click="searchQuery = ''"
            >
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>

        <!-- Trailing Actions -->
        <div class="flex items-center gap-2 ml-4">
            <button class="p-2 rounded-xl text-slate-500 hover:bg-white hover:text-slate-900 hover:shadow-sm border border-transparent hover:border-slate-200/50 transition-all duration-300 ease-in-out flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
            </button>
            <button class="p-2 rounded-xl text-slate-500 hover:bg-white hover:text-slate-900 hover:shadow-sm border border-transparent hover:border-slate-200/50 transition-all duration-300 ease-in-out flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">settings</span>
            </button>
            <div class="h-5 w-px bg-slate-200 mx-2"></div>

            <!-- User Avatar / Menu -->
            <div class="relative">
                <button
                    class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 shadow-sm hover:shadow transition-all duration-300 ease-in-out hover:ring-2 hover:ring-primary/20 ring-offset-2 ring-offset-slate-50 bg-slate-100 flex items-center justify-center"
                    @click="showUserMenu = !showUserMenu"
                >
                    <span class="text-xs font-bold text-slate-600">{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                </button>

                <!-- Dropdown -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="showUserMenu"
                        class="absolute right-0 top-12 w-56 bg-white/90 backdrop-blur-xl border border-slate-200/50 shadow-lg rounded-xl py-2 z-50"
                        @click.away="showUserMenu = false"
                    >
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-medium text-slate-900">{{ user?.name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ user?.email }}</p>
                        </div>
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors text-sm"
                            @click="showUserMenu = false"
                        >
                            <span class="material-symbols-outlined text-[18px]">person</span>
                            Profile
                        </Link>
                        <div class="h-px bg-slate-100 my-1"></div>
                        <button
                            class="flex items-center gap-3 px-4 py-2 hover:bg-red-50 text-red-500 hover:text-red-600 transition-colors w-full text-left text-sm"
                            @click="logout"
                        >
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            Sign out
                        </button>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
