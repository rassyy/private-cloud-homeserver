import { ref, computed } from 'vue';

export function useSelection() {
    const selectedItems = ref(new Set());
    const lastSelectedIndex = ref(null);

    function select(item, event = null, allItems = []) {
        if (event?.shiftKey && lastSelectedIndex.value !== null) {
            // Shift+click: range select
            const currentIndex = allItems.findIndex(i => i.id === item.id && i.type === item.type);
            const start = Math.min(lastSelectedIndex.value, currentIndex);
            const end = Math.max(lastSelectedIndex.value, currentIndex);

            const newSelection = new Set();
            for (let i = start; i <= end; i++) {
                newSelection.add(`${allItems[i].type}-${allItems[i].id}`);
            }
            selectedItems.value = newSelection;
        } else if (event?.ctrlKey || event?.metaKey) {
            // Ctrl/Cmd+click: toggle individual
            const key = `${item.type}-${item.id}`;
            const newSet = new Set(selectedItems.value);
            if (newSet.has(key)) {
                newSet.delete(key);
            } else {
                newSet.add(key);
            }
            selectedItems.value = newSet;
        } else {
            // Normal click: single select
            selectedItems.value = new Set([`${item.type}-${item.id}`]);
        }

        lastSelectedIndex.value = allItems.findIndex(i => i.id === item.id && i.type === item.type);
    }

    function isSelected(item) {
        return selectedItems.value.has(`${item.type}-${item.id}`);
    }

    function selectAll(allItems) {
        selectedItems.value = new Set(allItems.map(i => `${i.type}-${i.id}`));
    }

    function clearSelection() {
        selectedItems.value = new Set();
        lastSelectedIndex.value = null;
    }

    const selectedCount = computed(() => selectedItems.value.size);
    const hasSelection = computed(() => selectedItems.value.size > 0);

    return {
        selectedItems,
        select,
        isSelected,
        selectAll,
        clearSelection,
        selectedCount,
        hasSelection,
    };
}
