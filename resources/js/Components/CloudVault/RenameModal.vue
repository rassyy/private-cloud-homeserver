<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    visible: { type: Boolean, default: false },
    item: { type: Object, default: null },
    itemType: { type: String, default: null },
});

const emit = defineEmits(['close']);

const form = useForm({ name: '' });

watch(() => props.visible, (val) => {
    if (val && props.item) {
        form.name = props.item.name;
    }
});

function submit() {
    const routeName = props.itemType === 'folder' ? 'folders.update' : 'files.update';
    form.patch(route(routeName, props.item.id), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-50 flex items-center justify-center glass-overlay"
                @click.self="emit('close')"
            >
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div v-if="visible" class="bg-white w-full max-w-[400px] rounded-2xl shadow-2xl border border-slate-200/30 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-200/50 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-slate-400">edit</span>
                                <h2 class="font-geist text-xl tracking-tight font-semibold text-slate-900">Rename Item</h2>
                            </div>
                            <button class="text-slate-400 hover:text-slate-900 transition-colors" @click="emit('close')">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="p-6">
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-medium text-slate-500" for="renameInput">Name</label>
                                    <input
                                        id="renameInput"
                                        v-model="form.name"
                                        class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900"
                                        type="text"
                                        @focus="$event.target.select()"
                                    />
                                    <p class="text-xs text-slate-400 mt-1">Changing the extension may make the file unusable.</p>
                                    <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/50 flex justify-end gap-3 rounded-b-2xl">
                                <button type="button" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-500 hover:bg-slate-200/50 transition-colors" @click="emit('close')">Cancel</button>
                                <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-lg text-xs font-medium hover:bg-slate-800 transition-all shadow-sm" :disabled="form.processing">Save changes</button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
