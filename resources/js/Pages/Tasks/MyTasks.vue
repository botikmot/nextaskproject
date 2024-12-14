<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TaskCard from './TaskCard.vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
  tasks: Object,
  userRole: String,
  projects: Array,
});

const selectedProjects = ref([]);
const selectedStatuses = ref([]);
const sortBy = ref('title');
const sortOrder = ref('asc');

const sortingOptions = [
      { label: 'Title', value: 'title' },
      { label: 'Priority', value: 'priority' },
      { label: 'Status', value: 'status' },
      { label: 'Created At', value: 'created_at' },
    ];

const filteredTasks = computed(() => {
    let tasks = props.tasks.filter(task => {
        // Check if the task matches selected projects
        const matchesProject = 
            selectedProjects.value.length === 0 || 
            selectedProjects.value.includes(task.project_id);
        
        // Check if the task matches selected statuses
        const matchesStatus = 
            selectedStatuses.value.length === 0 || 
            selectedStatuses.value.includes(task.status_id);
        
        return matchesProject && matchesStatus;
    });

     // Apply sorting based on the selected sorting option and order
    tasks = tasks.sort((a, b) => {
        let comparison = 0;

        if (sortBy.value === 'title') {
            comparison = a.title.localeCompare(b.title);
        } else if (sortBy.value === 'priority') {
            comparison = a.priority - b.priority; // Assuming priority is a number
        } else if (sortBy.value === 'status') {
            comparison = a.status.name.localeCompare(b.status.name);
        } else if (sortBy.value === 'created_at') {
            comparison = new Date(a.created_at) - new Date(b.created_at);
        }

        // Reverse the comparison result if sortOrder is descending
        return sortOrder.value === 'asc' ? comparison : -comparison;
    });

    return tasks;

});

const toggleSortBy = (value) => {
    sortBy.value = value;
    localStorage.setItem('SortBy', value);
};

const toggleOrderBy = (order) => {
    sortOrder.value = order;
    localStorage.setItem('OrderBy', order);
};

const toggleProject = (projectId) => {
    if (selectedProjects.value.includes(projectId)) {
        selectedProjects.value = selectedProjects.value.filter(id => id !== projectId);
    } else {
        selectedProjects.value.push(projectId);
    }
    localStorage.setItem('selectedProjects', JSON.stringify(selectedProjects.value));
};

const toggleStatus = (statusId) => {
    if (selectedStatuses.value.includes(statusId)) {
        selectedStatuses.value = selectedStatuses.value.filter(id => id !== statusId);
    } else {
        selectedStatuses.value.push(statusId);
    }
    localStorage.setItem('selectedStatuses', JSON.stringify(selectedStatuses.value));
    console.log('getStatuses', getStatuses())
};

const getStatuses = () => {
    if (selectedProjects.value.length === 0) {
        // Return all statuses if no project is selected
        return props.projects.flatMap(project => project.statuses);
    } else {
        // Return statuses of selected projects
        return props.projects
            .filter(project => selectedProjects.value.includes(project.id))
            .flatMap(project => project.statuses);
    }
};

console.log('tasks', props.tasks)
onMounted(() => {
    const savedProjects = JSON.parse(localStorage.getItem('selectedProjects')) || [];
    const savedStatuses = JSON.parse(localStorage.getItem('selectedStatuses')) || [];
    selectedProjects.value = savedProjects;
    selectedStatuses.value = savedStatuses;

    const savedSortBy = localStorage.getItem('SortBy') || 'title';
    const savedOrderBy = localStorage.getItem('OrderBy') || 'asc';
    sortBy.value = savedSortBy;
    sortOrder.value = savedOrderBy;
});

</script>


<template>
    <Head title="Tasks" />

    <AuthenticatedLayout pageTitle="My Tasks">
    <div class="w-full bg-linen p-4">
      <div class="flex justify-between">
        <p class="text-navy-blue text-lg">
          Track your progress, prioritize effectively, and stay on top of your responsibilities. 
          Every task brings you one step closer to your goals.
        </p>
        <div class="flex items-center space-x-4">          
          <!-- Filter Dropdown -->
          <Dropdown align="right" width="64">
            <template #trigger>
              
              <div class="flex text-color-white cursor-pointer rounded-full px-3 bg-sky-blue py-1 items-center hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg">
                <i class="fa-solid fa-gear"></i>
                <span class="pl-2">Filter</span>
              </div>
            </template>
            <template #content>
              <div v-for="project in projects" :key="project.id" class="border-b py-2 border-dark-gray">
                <!-- Project Section -->
                <div
                  class="flex items-center pl-3 hover:bg-crystal-blue text-sm font-thin py-2 cursor-pointer"
                  @click.stop="toggleProject(project.id)"
                >
                  <input
                    type="checkbox"
                    class="form-checkbox h-4 w-4 text-navy-blue"
                    :value="project.id"
                    :checked="selectedProjects.includes(project.id)"
                    @click.stop="toggleProject(project.id)"
                  />
                  <span class="pl-2 font-semibold">{{ project.title }}</span>
                </div>

                <!-- Statuses Section -->
                <div
                  v-if="project.statuses && selectedProjects.includes(project.id)"
                  class="pl-3 mt-2"
                >
                  <div
                    v-for="status in project.statuses"
                    :key="status.id"
                    class="flex pl-3 items-center text-sm font-thin py-1 hover:bg-crystal-blue cursor-pointer"
                    @click.stop="toggleStatus(status.id)"
                  >
                    <input
                      type="checkbox"
                      class="form-checkbox h-4 w-4 text-sky-blue"
                      :value="status.id"
                      :checked="selectedStatuses.includes(status.id)"
                      @click.stop="toggleStatus(status.id)"
                    />
                    <span class="pl-2">{{ status.name }}</span>
                  </div>
                </div>
              </div>
              <!-- Sort By Section -->
                <div class="pl-3 border-b py-2 border-dark-gray">
                    <div class="font-semibold text-sm text-navy-blue mb-2">Sort by</div>
                        <div 
                            v-for="option in sortingOptions"
                            :key="option.value"
                            class="flex items-center py-1 pl-3 hover:bg-crystal-blue cursor-pointer"
                            @click.stop="toggleSortBy(option.value)"
                        >
                            <input
                                type="radio"
                                class="form-radio h-4 w-4 text-navy-blue"
                                :value="option.value"
                                v-model="sortBy"
                            />
                        <span class="pl-2 text-sm">{{ option.label }}</span>
                    </div>
                </div>
              <!-- Sort Order Section -->
                <div class="pl-3 border-b py-2 border-dark-gray">
                    <div class="font-semibold text-sm text-navy-blue mb-2">Sort Order</div>
                    <div @click.stop="toggleOrderBy('asc')" class="flex items-center py-1 pl-3 hover:bg-crystal-blue cursor-pointer">
                        <input
                            type="radio"
                            class="form-radio h-4 w-4 text-navy-blue"
                            value="asc"
                            v-model="sortOrder"
                        />
                        <span class="pl-2 text-sm">Ascending</span>
                    </div>
                    <div @click.stop="toggleOrderBy('desc')" class="flex items-center py-1 pl-3 hover:bg-crystal-blue cursor-pointer">
                        <input
                            type="radio"
                            class="form-radio h-4 w-4 text-navy-blue"
                            value="desc"
                            v-model="sortOrder"
                        />
                        <span class="pl-2 text-sm">Descending</span>
                    </div>
                </div>

            </template>
          </Dropdown>
        </div>
      </div>
      <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
        <section class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 xl:grid-cols-3 gap-4 items-start">
          <TaskCard
            v-for="(item, index) in filteredTasks"
            :key="item.id"
            :task="item"
            :tasks="item.project.tasks"
            :completedId="item.project.completed_status_id"
            :members="item.project.users"
            :project="item.project"
          />
        </section>
      </div>
    </div>
  </AuthenticatedLayout>

</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.expanded {
    transition: all 0.3s ease-in-out;
}
</style>
