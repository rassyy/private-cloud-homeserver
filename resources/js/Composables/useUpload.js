import { ref, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

export function useUpload() {
    const uploads = ref([]);

    const isUploading = computed(() => uploads.value.some(u => u.status === 'uploading'));
    const completedCount = computed(() => uploads.value.filter(u => u.status === 'done').length);
    const totalProgress = computed(() => {
        if (uploads.value.length === 0) return 0;
        const sum = uploads.value.reduce((acc, u) => acc + (u.progress || 0), 0);
        return Math.round(sum / uploads.value.length);
    });

    async function uploadFiles(fileList, folderId = null, onAllFinished = null) {
        const newUploads = Array.from(fileList).map(file => ({
            id: `${file.name}-${file.size}-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`,
            file,
            name: file.name,
            size: file.size,
            progress: 0,
            status: 'uploading',
            error: null,
            cancelSource: axios.CancelToken.source(),
        }));

        uploads.value.push(...newUploads);

        // Upload files in parallel using the reactive proxies from uploads.value
        const reactiveNewUploads = uploads.value.slice(-newUploads.length);
        await Promise.all(reactiveNewUploads.map(item => uploadSingleFile(item, folderId)));

        // Once all finished, reload current view props to show newly uploaded files and storage usage
        if (!uploads.value.some(u => u.status === 'uploading')) {
            router.reload({ only: ['files', 'folders', 'storage_usage'] });
            if (onAllFinished) onAllFinished();
        }
    }

    async function uploadSingleFile(item, folderId) {
        const file = item.file;
        const chunkSize = 2 * 1024 * 1024; // 2MB chunks for reliable large uploads (>100MB)
        const totalChunks = Math.ceil(file.size / chunkSize) || 1;
        const identifier = `${file.size}-${file.name.replace(/[^0-9a-zA-Z_-]/g, '')}-${Date.now()}`;

        for (let chunkNumber = 1; chunkNumber <= totalChunks; chunkNumber++) {
            if (item.status === 'cancelled') return;

            const start = (chunkNumber - 1) * chunkSize;
            const end = Math.min(start + chunkSize, file.size);
            const chunkBlob = file.slice(start, end);

            const formData = new FormData();
            formData.append('file', chunkBlob, file.name);
            formData.append('resumableChunkNumber', chunkNumber);
            formData.append('resumableTotalChunks', totalChunks);
            formData.append('resumableChunkSize', chunkSize);
            formData.append('resumableTotalSize', file.size);
            formData.append('resumableIdentifier', identifier);
            formData.append('resumableFilename', file.name);

            if (folderId !== null && folderId !== undefined && folderId !== 'null') {
                formData.append('folder_id', folderId);
            }

            try {
                const response = await axios.post(route('files.store'), formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    cancelToken: item.cancelSource.token,
                    onUploadProgress: (progressEvent) => {
                        if (progressEvent.total) {
                            item.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        }
                    },
                });

                if (chunkNumber === totalChunks || response.data?.status === 'success') {
                    item.progress = 100;
                    item.status = 'done';
                    return;
                }
            } catch (error) {
                if (axios.isCancel(error)) {
                    item.status = 'cancelled';
                    return;
                }
                console.error('Upload error:', error);
                item.status = 'error';
                item.error = error.response?.data?.message || 'Upload failed';
                return;
            }
        }
    }

    function cancelUpload(id) {
        const item = uploads.value.find(u => u.id === id);
        if (item && item.status === 'uploading') {
            item.cancelSource.cancel('User cancelled upload');
            item.status = 'cancelled';
        }
    }

    function clearCompleted() {
        uploads.value = uploads.value.filter(u => u.status === 'uploading');
    }

    return {
        uploads,
        isUploading,
        completedCount,
        totalProgress,
        uploadFiles,
        cancelUpload,
        clearCompleted,
    };
}
