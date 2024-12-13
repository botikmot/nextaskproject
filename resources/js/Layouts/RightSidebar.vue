<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '@/Pages/Projects/NewTaskModal.vue';
import axios from 'axios';

let isTaskModalOpen = ref(false);
let projects = ref([])
let labels = ref(null)
const props = defineProps({
    projects: Object,
    labels: Object,
});

const createTask = async () => {
    const response = await axios.get('/tasks');
    if(response.data.success){
        console.log(response)
        projects.value = response.data.projects
        labels.value = response.data.labels
        isTaskModalOpen.value = true
    }
}

</script>

<template>
    <div class="mb-6 text-lg font-semibold text-gray-800 dark:text-gray-100">Quick Actions </div>
    <button @click="createTask" class="block w-full mb-4 py-2 text-linen bg-sky-blue rounded-full hover:bg-navy-blue">Create New Task</button>
    <!-- <button class="block w-full mb-4 py-2 text-white bg-blue-500 rounded">Join Meeting</button> -->
    <div class="mt-6">
        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">People You May Know</h3>
        <!-- Placeholder for connection suggestions -->
    </div>

    <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
        <NewTaskModal @close="isTaskModalOpen = false" :projects="projects" :labels="labels"/>
    </Modal>

</template>