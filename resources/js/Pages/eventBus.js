import { ref } from 'vue';

export const eventBus = {
    isCollapsed: ref(localStorage.getItem('collapsed') === 'true'),
    toggleCollapsed() {
        this.isCollapsed.value = !this.isCollapsed.value;
        localStorage.setItem('collapsed', this.isCollapsed.value);
    }
};
