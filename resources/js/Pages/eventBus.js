import { ref } from 'vue';

export const eventBus = {
    isCollapsed: ref(localStorage.getItem('collapsed') === 'true'),
    toggleCollapsed() {
        this.isCollapsed.value = !this.isCollapsed.value;
        localStorage.setItem('collapsed', this.isCollapsed.value);
    },
    // New functionality for task events
    taskAdded: ref(false), // Reactive property to track task addition
    emitTaskAdded() {
        this.taskAdded.value = true; // Set taskAdded to true when a task is added
    },
    clearTaskAdded() {
        this.taskAdded.value = false; // Reset taskAdded after handling the event
    }
};
