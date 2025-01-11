<script setup>
import { ref, computed, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import CreateProjectModal from '../Projects/CreateProjectModal.vue';

const props = defineProps({
    projects: Array,
});

let isModalOpen = ref(false);

// Define active projects based on custom logic
const activeProjects = computed(() => {
  return props.projects
    .filter((project) => {
      const progressIncomplete = project.progress < 100; // Incomplete project
      const dueDateFuture = new Date(project.deadline) > new Date(); // Due date is in the future
      return progressIncomplete || dueDateFuture; // Both conditions
    })
    .slice(0, 2); // Limit to maximum 2 projects
});

</script>

<template>
    <div class="">
        <h2 class="text-lg font-bold text-navy-blue border-b border-dark-gray pb-2">Projects</h2>
        <div v-if="activeProjects.length === 0" class="text-gray-500 mt-4">
            <p>No projects available yet.</p>
            <p class="mb-6">Start by creating your first project!</p>
        </div>
        <ul v-else class="mt-2 mb-6">
            <li
                v-for="project in activeProjects"
                :key="project.id"
                class="flex justify-between border-b border-dark-gray items-center py-1"
            >
                <span class="font-semibold text-sky-blue">{{ project.title }}</span>
                <span class="text-navy-blue font-bold">{{ project.progress }}% complete</span>
            </li>
        </ul>
        <a
            class="absolute bottom-5 right-5 cursor-pointer hover:scale-105 transition px-6 py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
            :href="route('projects')"
            v-if="activeProjects.length > 0"
        >
           Manage Projects
        </a>
        <a
            v-else
            class="absolute bottom-5 right-5 cursor-pointer hover:scale-105 transition px-6 py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
            @click="isModalOpen = true"
        >
            Create Your First Project
        </a>
    </div>
    <Modal :show="isModalOpen" @close="isModalOpen = false">
        <CreateProjectModal @close="isModalOpen = false"/>
    </Modal>
</template>