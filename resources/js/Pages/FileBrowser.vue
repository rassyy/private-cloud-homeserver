<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

import Breadcrumbs from '@/Components/CloudVault/Breadcrumbs.vue';
import FileGrid from '@/Components/CloudVault/FileGrid.vue';
import FileList from '@/Components/CloudVault/FileList.vue';
import ContextMenu from '@/Components/CloudVault/ContextMenu.vue';
import DropZoneOverlay from '@/Components/CloudVault/DropZoneOverlay.vue';
import EmptyState from '@/Components/CloudVault/EmptyState.vue';
import FileDetailsPanel from '@/Components/CloudVault/FileDetailsPanel.vue';
import CreateFolderModal from '@/Components/CloudVault/CreateFolderModal.vue';
import RenameModal from '@/Components/CloudVault/RenameModal.vue';
import MoveModal from '@/Components/CloudVault/MoveModal.vue';
import ShareModal from '@/Components/CloudVault/ShareModal.vue';
import DeleteConfirmModal from '@/Components/CloudVault/DeleteConfirmModal.vue';
import FilePreviewModal from '@/Components/CloudVault/FilePreviewModal.vue';
import UploadProgressModal from '@/Components/CloudVault/UploadProgressModal.vue';

import { useView } from '@/Composables/useView.js';
import { useDragDrop } from '@/Composables/useDragDrop.js';
import { useUpload } from '@/Composables/useUpload.js';

defineOptions({ layout: AppLayout });

const props = defineProps({
    folder: { type: Object, default: null },
    folders: { type: Array, default: () => [] },
    files: { type: Array, default: () => [] },
    ancestors: { type: Array, default: () => [] },
    all_folders: { type: Array, default: () => [] },
    view_mode: { type: String, default: 'normal' },
});

// ── View State ──
const { currentView } = useView();

// ── Drag & Drop ──
const { isDragging, dragEnter, dragLeave, dragOver, drop } = useDragDrop();

// ── Uploads ──
const { uploads, isUploading, uploadFiles: doUpload, cancelUpload } = useUpload();
const showUploadProgress = ref(false);

// ── Context Menu ──
const contextMenu = ref({ visible: false, position: { x: 0, y: 0 }, item: null, type: null });

function showContextMenu(event, item, type) {
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    let x = event.clientX;
    let y = event.clientY;
    if (x + 192 > viewportWidth) x = viewportWidth - 200;
    if (y + 280 > viewportHeight) y = viewportHeight - 288;

    contextMenu.value = { visible: true, position: { x, y }, item, type };
}

function hideContextMenu() {
    contextMenu.value = { ...contextMenu.value, visible: false };
}

// ── Modals ──
const showCreateFolder = ref(false);
const showRename = ref(false);
const showMove = ref(false);
const showShare = ref(false);
const showDelete = ref(false);
const showPreview = ref(false);

const modalItem = ref(null);
const modalItemType = ref(null);

// ── Details Panel ──
const detailsItem = ref(null);
const showDetails = ref(false);

// ── File Input ──
const fileInput = ref(null);

// ── Computed ──
const isEmpty = computed(() => props.folders.length === 0 && props.files.length === 0);
const currentFolderName = computed(() => props.folder?.name || 'My Files');

// ── Actions ──
function openFolder(folder) {
    router.visit(route('folders.show', folder.id));
}

function previewFile(file) {
    modalItem.value = file;
    showPreview.value = true;
}

function handleContextRename() {
    modalItem.value = contextMenu.value.item;
    modalItemType.value = contextMenu.value.type;
    hideContextMenu();
    showRename.value = true;
}

function handleContextDownload() {
    const item = contextMenu.value.item;
    hideContextMenu();
    if (item) window.location.href = route('files.download', item.id);
}

function handleContextMove() {
    modalItem.value = contextMenu.value.item;
    modalItemType.value = contextMenu.value.type;
    hideContextMenu();
    showMove.value = true;
}

function handleContextShare() {
    modalItem.value = contextMenu.value.item;
    modalItemType.value = contextMenu.value.type;
    hideContextMenu();
    showShare.value = true;
}

function handleContextStar() {
    const item = contextMenu.value.item;
    const type = contextMenu.value.type;
    hideContextMenu();
    if (!item) return;

    const routeName = type === 'folder' ? 'folders.star' : 'files.star';
    router.post(route(routeName, item.id), {}, { preserveScroll: true });
}

function handleContextDetails() {
    detailsItem.value = contextMenu.value.item;
    showDetails.value = true;
    hideContextMenu();
}

function handleContextDelete() {
    modalItem.value = contextMenu.value.item;
    modalItemType.value = contextMenu.value.type;
    hideContextMenu();
    showDelete.value = true;
}

function handleContextRestore() {
    const item = contextMenu.value.item;
    const type = contextMenu.value.type;
    hideContextMenu();
    if (!item) return;

    const routeName = type === 'folder' ? 'folders.restore' : 'files.restore';
    router.post(route(routeName, item.id), {}, { preserveScroll: true });
}

function handleContextForceDelete() {
    const item = contextMenu.value.item;
    const type = contextMenu.value.type;
    hideContextMenu();
    if (!item) return;

    modalItem.value = item;
    modalItemType.value = type === 'folder' ? 'force-delete-folder' : 'force-delete-file';
    showDelete.value = true;
}

function emptyTrash() {
    modalItem.value = { name: 'All Trash Items' };
    modalItemType.value = 'empty-trash';
    showDelete.value = true;
}

function handleDetailsDownload(file) {
    window.location.href = route('files.download', file.id);
}

// ── File Upload ──
function triggerUpload() {
    fileInput.value?.click();
}

function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    if (files.length > 0) {
        showUploadProgress.value = true;
        doUpload(files, props.folder?.id, () => {
            if (!uploads.value.some(u => u.status === 'error')) {
                setTimeout(() => {
                    if (!isUploading.value) showUploadProgress.value = false;
                }, 3000);
            }
        });
    }
    event.target.value = '';
}

function handleDrop(files) {
    if (files.length > 0) {
        showUploadProgress.value = true;
        doUpload(files, props.folder?.id, () => {
            if (!uploads.value.some(u => u.status === 'error')) {
                setTimeout(() => {
                    if (!isUploading.value) showUploadProgress.value = false;
                }, 3000);
            }
        });
    }
}

function handleExternalUpload() {
    triggerUpload();
}

onMounted(() => {
    window.addEventListener('trigger-upload', handleExternalUpload);
});

onUnmounted(() => {
    window.removeEventListener('trigger-upload', handleExternalUpload);
});
</script>

<template>
    <div
        class="flex-1 flex overflow-hidden relative"
        @dragenter.prevent="dragEnter"
    >
        <!-- Drop Zone Overlay -->
        <DropZoneOverlay
            :visible="isDragging"
            :folder-name="currentFolderName"
            @dragleave.prevent="dragLeave"
            @dragover.prevent="dragOver"
            @drop.prevent="(e) => drop(e, handleDrop)"
        />

        <!-- Main File Explorer -->
        <div class="flex-1 overflow-y-auto p-8 scrollbar-thin">
            <div class="max-w-[1440px] mx-auto">
                <!-- Toolbar & Breadcrumbs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                    <Breadcrumbs
                        :ancestors="ancestors"
                        :current-folder="folder"
                    />

                    <!-- View Toggles & Actions -->
                    <div class="flex items-center gap-3">
                        <!-- Upload Button -->
                        <button
                            v-if="view_mode === 'normal'"
                            class="flex items-center gap-2 px-4 py-1.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-all duration-300 ease-in-out text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5"
                            @click="triggerUpload"
                        >
                            <span class="material-symbols-outlined text-[18px]">upload</span>
                            Upload
                        </button>

                        <!-- New Folder -->
                        <button
                            v-if="view_mode === 'normal'"
                            class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200/80 rounded-xl hover:bg-slate-50 hover:shadow-sm transition-all duration-300 ease-in-out text-sm font-medium text-slate-700 shadow-sm"
                            @click="showCreateFolder = true"
                        >
                            <span class="material-symbols-outlined text-[18px]">create_new_folder</span>
                            New Folder
                        </button>

                        <!-- View Toggle -->
                        <div class="flex bg-slate-200/50 rounded-xl p-1 border border-slate-200/50">
                            <button
                                :class="{'bg-white shadow-sm text-slate-900 rounded-lg': currentView === 'grid', 'text-slate-500 hover:text-slate-700': currentView !== 'grid'}"
                                class="p-1.5 transition-all duration-300 ease-in-out flex items-center justify-center"
                                @click="currentView = 'grid'"
                            >
                                <span class="material-symbols-outlined text-[18px]">grid_view</span>
                            </button>
                            <button
                                :class="{'bg-white shadow-sm text-slate-900 rounded-lg': currentView === 'list', 'text-slate-500 hover:text-slate-700': currentView !== 'list'}"
                                class="p-1.5 transition-all duration-300 ease-in-out flex items-center justify-center"
                                @click="currentView = 'list'"
                            >
                                <span class="material-symbols-outlined text-[18px]">view_list</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Trash Notice Banner -->
                <div v-if="view_mode === 'trash'" class="mb-6 p-4 bg-red-50/80 border border-red-200/60 rounded-2xl flex items-center justify-between text-red-800 text-sm shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px] text-red-600">delete</span>
                        <span class="font-medium">Items in the Trash are deleted forever after 30 days.</span>
                    </div>
                    <button
                        v-if="!isEmpty"
                        class="px-4 py-1.5 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-all shadow-sm text-xs"
                        @click="emptyTrash"
                    >
                        Empty Trash
                    </button>
                </div>

                <!-- Empty State -->
                <EmptyState v-if="isEmpty" />

                <!-- Grid View -->
                <FileGrid
                    v-else-if="currentView === 'grid'"
                    :folders="folders"
                    :files="files"
                    @context-menu="showContextMenu"
                    @open-folder="openFolder"
                    @preview-file="previewFile"
                />

                <!-- List View -->
                <FileList
                    v-else
                    :folders="folders"
                    :files="files"
                    @context-menu="showContextMenu"
                    @open-folder="openFolder"
                    @preview-file="previewFile"
                />
            </div>
        </div>

        <!-- File Details Panel -->
        <FileDetailsPanel
            :file="detailsItem"
            :visible="showDetails"
            @close="showDetails = false"
            @download="handleDetailsDownload"
        />

        <!-- Context Menu -->
        <ContextMenu
            :visible="contextMenu.visible"
            :position="contextMenu.position"
            :item="contextMenu.item"
            :item-type="contextMenu.type"
            :view-mode="view_mode"
            @close="hideContextMenu"
            @rename="handleContextRename"
            @download="handleContextDownload"
            @move="handleContextMove"
            @share="handleContextShare"
            @star="handleContextStar"
            @details="handleContextDetails"
            @delete="handleContextDelete"
            @restore="handleContextRestore"
            @force-delete="handleContextForceDelete"
        />

        <!-- Modals -->
        <CreateFolderModal
            :visible="showCreateFolder"
            :parent-id="folder?.id"
            @close="showCreateFolder = false"
        />

        <RenameModal
            :visible="showRename"
            :item="modalItem"
            :item-type="modalItemType"
            @close="showRename = false"
        />

        <MoveModal
            :visible="showMove"
            :item="modalItem"
            :item-type="modalItemType"
            :folders="folders"
            :all-folders="all_folders"
            @close="showMove = false"
        />

        <ShareModal
            :visible="showShare"
            :item="modalItem"
            :item-type="modalItemType"
            @close="showShare = false"
        />

        <DeleteConfirmModal
            :visible="showDelete"
            :item="modalItem"
            :item-type="modalItemType"
            @close="showDelete = false"
        />

        <FilePreviewModal
            :visible="showPreview"
            :file="modalItem"
            @close="showPreview = false"
        />

        <!-- Upload Progress -->
        <UploadProgressModal
            :visible="showUploadProgress"
            :uploads="uploads"
            @close="showUploadProgress = false"
            @cancel="cancelUpload"
        />

        <!-- Hidden File Input -->
        <input
            ref="fileInput"
            type="file"
            multiple
            class="hidden"
            @change="handleFileSelect"
        />
    </div>
</template>
