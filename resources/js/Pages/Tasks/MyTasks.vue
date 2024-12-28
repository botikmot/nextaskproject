<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import TaskCard from './TaskCard.vue';
import Dropdown from '@/Components/Dropdown.vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '../Projects/NewTaskModal.vue';
import axios from 'axios';


const props = defineProps({
  tasks: Object,
  userRole: String,
  projects: Array,
  selectedProjects: Array,
  selectedStatuses: Array,
  sortBy: String,
  sortOrder: String,
});

let isTaskModalOpen = ref(false);
let tasksProjects = ref([])
let labels = ref(null)

const totalPages = ref(props.tasks.data ? props.tasks.last_page : 1);
const currentPage = ref(props.tasks.data ? props.tasks.current_page :1);
const selectedProjects = ref(props.selectedProjects ? props.selectedProjects : []);
const selectedStatuses = ref(props.selectedStatuses ? props.selectedStatuses : []);
const sortBy = ref(props.sortBy ? props.sortBy : 'title');
const sortOrder = ref(props.sortOrder ? props.sortOrder : 'asc');
let isLoading = ref(false);

const form = useForm({
    page: currentPage.value,
    selectedProjects: selectedProjects.value,
    selectedStatuses: selectedStatuses.value,
    sortBy: sortBy.value,
    sortOrder: sortOrder.value
});

const sortingOptions = [
      { label: 'Due date', value: 'due_date' },
      { label: 'Title', value: 'title' },
      { label: 'Priority', value: 'priority' },
      { label: 'Status', value: 'status' },
      { label: 'Created At', value: 'created_at' },
    ];

const hasStatuses = computed(() => {
    return props.projects.some(project => project.statuses && project.statuses.length > 0);
});

const createTask = async () => {
    const response = await axios.get('/tasks-project');
    if(response.data.success){
        console.log(response)
        tasksProjects.value = response.data.projects
        labels.value = response.data.labels
        isTaskModalOpen.value = true
    }
}

// Fetch tasks function
const fetchTasks = async () => {
  
    try {
      console.log('form', form)
      isLoading.value = true
      form.get('/my-tasks', {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Successfully fetched.')
                    isLoading.value = false
                },
                onError: (error) => {
                    console.error('Error fetching project', error)
                }
            })
    } catch (error) {
        console.error('Error fetching tasks:', error);
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        form.page = page
        fetchTasks()
    }
};

const toggleSortBy = (value) => {
    sortBy.value = value;
    form.sortBy = value
    localStorage.setItem('SortBy', value);
    fetchTasks()
};

const toggleOrderBy = (order) => {
    sortOrder.value = order;
    form.sortOrder = order
    localStorage.setItem('OrderBy', order);
    fetchTasks()
};

const toggleProject = (projectId) => {
    if (selectedProjects.value.includes(projectId)) {
        selectedProjects.value = selectedProjects.value.filter(id => id !== projectId);
    } else {
        selectedProjects.value.push(projectId);
    }
    form.selectedProjects = selectedProjects.value
    localStorage.setItem('selectedProjects', JSON.stringify(selectedProjects.value));
    fetchTasks()
};

const toggleStatus = (statusId) => {
    if (selectedStatuses.value.includes(statusId)) {
        selectedStatuses.value = selectedStatuses.value.filter(id => id !== statusId);
    } else {
        selectedStatuses.value.push(statusId);
    }
    form.selectedStatuses = selectedStatuses.value
    localStorage.setItem('selectedStatuses', JSON.stringify(selectedStatuses.value));
    console.log('getStatuses', getStatuses())
    fetchTasks()
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
    form.selectedProjects = savedProjects;
    form.selectedStatuses = savedStatuses;
    
    const savedSortBy = localStorage.getItem('SortBy') || 'title';
    const savedOrderBy = localStorage.getItem('OrderBy') || 'asc';

    form.sortBy = savedSortBy;
    form.sortOrder = savedOrderBy;
});

</script>


<template>
    <Head title="Tasks" />

    <AuthenticatedLayout pageTitle="My Tasks">
    <div class="w-full bg-linen p-4 relative">

      <div v-if="isLoading" class="absolute inset-0 bg-dark-gray bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-10">
        <!-- Loading spinner -->
        <svg aria-hidden="true" class="w-24 h-24 text-gray animate-spin dark:text-gray-600 fill-sky-blue" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
          <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
        </svg>
        <span class="sr-only">Loading...</span>
      </div>

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

        <!-- Conditional Placeholder Messages -->
      <section v-if="projects.length === 0 && !isLoading" class="text-center text-gray">
        <p class="text-navy-blue text-lg">You currently have no tasks available.</p>
        <p class="pb-6">Create a project and add a status to start managing your tasks.</p>
        <a
            class="mt-12 px-6 py-3 cursor-pointer bg-sky-blue text-color-white rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg hover:font-bold"
            :href="route('projects')"
          >
            Set Up Your First Project
        </a>
      </section>

      <section v-else-if="projects.length > 0 && !hasStatuses && !isLoading" class="text-center text-gray-500">
        <p class="text-navy-blue text-lg">You currently have no tasks available.</p>
        <p class="pb-6">You have projects but no statuses. Add statuses to your project to categorize tasks.</p>
        <a
            class="mt-12 px-6 py-3 cursor-pointer bg-sky-blue text-color-white rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg hover:font-bold"
            :href="route('projects')"
          >
            Set Up Your First Project
        </a>
      </section>

      <section v-else-if="projects.length > 0 && hasStatuses && tasks.length === 0 && !isLoading" class="text-center text-gray-500">
        <p>You have projects and statuses, but no tasks. Add tasks to get started.</p>
        <button
            class="mt-3 px-6 py-2 bg-sky-blue text-color-white rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg hover:font-bold"
            @click="createTask"
          >
            Create New Task
        </button>
      </section>
        <section v-else class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 xl:grid-cols-3 gap-4 items-start">
          <TaskCard
            v-for="(item, index) in tasks.data"
            :key="item.id"
            :task="item"
            :tasks="item.project.tasks"
            :completedId="item.project.completed_status_id"
            :members="item.project.users"
            :project="item.project"
            :labels="item.project.labels"
          />
        </section>
        <!-- Pagination Controls -->
        <div v-if="!isLoading && tasks.data.length" class="pagination flex justify-start mt-3 items-center">
            <button class="mx-3 bg-sky-blue px-3 py-1 rounded text-color-white" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">
                Previous
            </button>
            <span class="text-sm font-bold text-navy-blue">Page {{ currentPage }} of {{ totalPages }}</span>
            <button class="mx-3 bg-sky-blue px-3 py-1 rounded text-color-white" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">
                Next
            </button>
        </div>
      </div>

      <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
        <NewTaskModal @close="isTaskModalOpen = false" :projects="tasksProjects" :labels="labels"/>
      </Modal>
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
