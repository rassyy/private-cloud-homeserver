import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export function useContextMenu() {
    const isVisible = ref(false);
    const position = ref({ x: 0, y: 0 });
    const targetItem = ref(null);
    const targetType = ref(null); // 'file' | 'folder'

    function show(event, item, type) {
        event.preventDefault();
        event.stopPropagation();

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const menuWidth = 192; // w-48
        const menuHeight = 280; // approximate

        let x = event.clientX;
        let y = event.clientY;

        if (x + menuWidth > viewportWidth) x = viewportWidth - menuWidth - 8;
        if (y + menuHeight > viewportHeight) y = viewportHeight - menuHeight - 8;

        position.value = { x, y };
        targetItem.value = item;
        targetType.value = type;
        isVisible.value = true;
    }

    function hide() {
        isVisible.value = false;
        targetItem.value = null;
        targetType.value = null;
    }

    function onRename(newName) {
        if (!targetItem.value) return;

        const routeName = targetType.value === 'folder' ? 'folders.update' : 'files.update';
        router.patch(route(routeName, targetItem.value.id), { name: newName }, {
            preserveScroll: true,
            onSuccess: () => hide(),
        });
    }

    function onDelete() {
        if (!targetItem.value) return;

        const routeName = targetType.value === 'folder' ? 'folders.destroy' : 'files.destroy';
        router.delete(route(routeName, targetItem.value.id), {
            preserveScroll: true,
            onSuccess: () => hide(),
        });
    }

    function onToggleStar() {
        if (!targetItem.value) return;

        const routeName = targetType.value === 'folder' ? 'folders.star' : 'files.star';
        router.post(route(routeName, targetItem.value.id), {}, {
            preserveScroll: true,
            onSuccess: () => hide(),
        });
    }

    function onDownload() {
        if (!targetItem.value || targetType.value !== 'file') return;
        window.location.href = route('files.download', targetItem.value.id);
        hide();
    }

    return {
        isVisible,
        position,
        targetItem,
        targetType,
        show,
        hide,
        onRename,
        onDelete,
        onToggleStar,
        onDownload,
    };
}
