import { ref } from 'vue';

export function useDragDrop() {
    const isDragging = ref(false);
    let dragCounter = 0;

    function dragEnter(event) {
        event.preventDefault();
        dragCounter++;
        isDragging.value = true;
    }

    function dragLeave(event) {
        event.preventDefault();
        dragCounter--;
        if (dragCounter === 0) {
            isDragging.value = false;
        }
    }

    function dragOver(event) {
        event.preventDefault();
    }

    function drop(event, callback) {
        event.preventDefault();
        dragCounter = 0;
        isDragging.value = false;

        const dt = event.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            callback(Array.from(dt.files));
        }
    }

    function reset() {
        dragCounter = 0;
        isDragging.value = false;
    }

    return {
        isDragging,
        dragEnter,
        dragLeave,
        dragOver,
        drop,
        reset,
    };
}
