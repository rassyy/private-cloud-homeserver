<script setup>
const props = defineProps({
    folders: { type: Array, default: () => [] },
    files: { type: Array, default: () => [] },
});

const emit = defineEmits(['context-menu', 'select', 'open-folder', 'preview-file']);
</script>

<template>
    <div class="bg-white border border-slate-200/50 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200/50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <th class="py-3 px-6 font-medium w-1/2">Name</th>
                    <th class="py-3 px-6 font-medium hidden sm:table-cell">Owner</th>
                    <th class="py-3 px-6 font-medium hidden md:table-cell">Modified</th>
                    <th class="py-3 px-6 font-medium">Size</th>
                    <th class="py-3 px-6 font-medium w-16"></th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                <!-- Folders -->
                <tr
                    v-for="folder in folders"
                    :key="'list-folder-' + folder.id"
                    class="hover:bg-slate-50/80 transition-colors duration-300 ease-in-out group cursor-pointer"
                    @click="emit('open-folder', folder)"
                    @contextmenu.prevent="emit('context-menu', $event, folder, 'folder')"
                >
                    <td class="py-3 px-6">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-blue-500 text-[20px]" style="font-variation-settings: 'FILL' 1;">folder</span>
                            <span class="font-medium text-slate-900">{{ folder.name }}</span>
                            <span
                                v-if="folder.is_starred"
                                class="material-symbols-outlined text-[16px] text-amber-400 shrink-0"
                                style="font-variation-settings: 'FILL' 1;"
                                title="Starred"
                            >star</span>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-slate-500 hidden sm:table-cell">me</td>
                    <td class="py-3 px-6 text-slate-500 text-xs hidden md:table-cell">{{ folder.updated_at }}</td>
                    <td class="py-3 px-6 text-slate-500 font-mono text-xs">—</td>
                    <td class="py-3 px-6 text-right">
                        <button
                            class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg hover:bg-slate-200/50 text-slate-400 hover:text-slate-700 transition-all duration-300 ease-in-out"
                            @click.stop="emit('context-menu', $event, folder, 'folder')"
                        >
                            <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                        </button>
                    </td>
                </tr>

                <!-- Files -->
                <tr
                    v-for="file in files"
                    :key="'list-file-' + file.id"
                    class="hover:bg-slate-50/80 transition-colors duration-300 ease-in-out group cursor-pointer"
                    @click="emit('preview-file', file)"
                    @contextmenu.prevent="emit('context-menu', $event, file, 'file')"
                >
                    <td class="py-3 px-6">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-slate-100/80 border border-slate-200/60 flex items-center justify-center overflow-hidden shrink-0">
                                <img
                                    v-if="file.mime_type?.startsWith('image/')"
                                    :src="route('files.preview', file.id)"
                                    :alt="file.name"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                                <video
                                    v-else-if="file.mime_type?.startsWith('video/')"
                                    :src="route('files.preview', file.id) + '#t=0.1'"
                                    class="w-full h-full object-cover"
                                    preload="metadata"
                                    muted
                                    playsinline
                                ></video>
                                <span
                                    v-else
                                    class="material-symbols-outlined text-[20px]"
                                    :class="file.icon_color"
                                    style="font-variation-settings: 'FILL' 1;"
                                >{{ file.icon }}</span>
                            </div>
                            <span class="font-medium text-slate-900">{{ file.name }}</span>
                            <span
                                v-if="file.is_starred"
                                class="material-symbols-outlined text-[16px] text-amber-400 shrink-0"
                                style="font-variation-settings: 'FILL' 1;"
                                title="Starred"
                            >star</span>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-slate-500 hidden sm:table-cell">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[9px] font-bold ring-1 ring-slate-200">ME</span>
                            <span class="text-xs">me</span>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-slate-500 text-xs hidden md:table-cell">{{ file.updated_at }}</td>
                    <td class="py-3 px-6 text-slate-500 font-mono text-xs">{{ file.size }}</td>
                    <td class="py-3 px-6 text-right">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 ease-in-out">
                            <button
                                class="p-1.5 rounded-lg hover:bg-slate-200/50 text-slate-400 hover:text-slate-700 transition-all duration-300 ease-in-out"
                                @click.stop="emit('context-menu', $event, file, 'file')"
                            >
                                <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
