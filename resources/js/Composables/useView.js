import { ref, watch } from 'vue';

export function useView() {
    const STORAGE_KEY = 'cloudvault-view-mode';
    const SORT_KEY = 'cloudvault-sort';

    const currentView = ref(localStorage.getItem(STORAGE_KEY) || 'grid');
    const sortBy = ref(localStorage.getItem(`${SORT_KEY}-by`) || 'name');
    const sortDir = ref(localStorage.getItem(`${SORT_KEY}-dir`) || 'asc');

    watch(currentView, (val) => localStorage.setItem(STORAGE_KEY, val));
    watch(sortBy, (val) => localStorage.setItem(`${SORT_KEY}-by`, val));
    watch(sortDir, (val) => localStorage.setItem(`${SORT_KEY}-dir`, val));

    function toggleView() {
        currentView.value = currentView.value === 'grid' ? 'list' : 'grid';
    }

    function setSort(column) {
        if (sortBy.value === column) {
            sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy.value = column;
            sortDir.value = 'asc';
        }
    }

    return {
        currentView,
        sortBy,
        sortDir,
        toggleView,
        setSort,
    };
}
