<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import TaskCard from './TaskCard.vue';
import Dropdown from '@/Components/Dropdown.vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '../Projects/NewTaskModal.vue';
import axios from 'axios';
import TaskCardNew from './TaskCardNew.vue';


const props = defineProps({
  tasks: Object,
  userRole: String,
  projects: Array,
  selectedProjects: Array,
  selectedStatuses: Array,
  sortBy: String,
  sortOrder: String,
  statistics: Object,
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

console.log('statistics', props.statistics)
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
  <AuthenticatedLayout pageTitle="Tasks">
    <div class="flex flex-col lg:flex-row h-full w-full bg-light-gray">
      <!-- Sidebar Filter -->
      <aside class="w-full lg:w-1/4 bg-color-white shadow-md p-6">
        <h2 class="text-navy-blue text-lg font-bold mb-4">Filters</h2>
        <div v-if="projects.length === 0" class="text-center text-gray-600 mt-4">
          <p>No projects available. Create your first project to start managing tasks.</p>
        </div>
        <div v-else>
          <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Projects</h3>
            <div v-for="project in projects" :key="project.id" class="border-b py-2 border-dark-gray">
                  
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
                  <div
                    v-if="project.statuses && selectedProjects.includes(project.id)"
                    class="pl-3 mt-2"
                  > 
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Statuses</h3>
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
          </div>

          <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Sort By</h3>
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
          <div class="mb-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">Sort Order</h3>
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
        </div>

      </aside>

      <!-- Main Content -->
      <main class="w-full lg:w-3/4 p-6 relative">

        <div v-if="isLoading" class="absolute inset-0 bg-dark-gray bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-10">
          <svg aria-hidden="true" class="w-24 h-24 text-gray animate-spin dark:text-gray-600 fill-sky-blue" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
          </svg>
          <span class="sr-only">Loading...</span>
        </div>

        <!-- New User Placeholder -->
        <div v-if="projects.length === 0 && !isLoading" class="text-center py-10">
          <div class="bg-light-gray p-6 rounded-lg shadow-md">
            <h2 class="text-navy-blue text-2xl font-bold">Welcome to Your Task Dashboard!</h2>
            <p class="mt-4 text-gray-600">
              Get started by creating your first project and adding tasks.
            </p>
            <div class="mt-6">
              <a
                class="inline-block px-6 py-3 hover:scale-105 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue transition"
                :href="route('projects')"
              >
                Create Your First Project
              </a>
            </div>
          </div>
        </div>

        <div v-else-if="projects.length > 0 && !hasStatuses && !isLoading" class="text-center py-10">
          <div class="bg-light-gray p-6 rounded-lg shadow-md">
            <h2 class="text-navy-blue text-2xl font-bold">Welcome to Your Task Dashboard!</h2>
            <p class="mt-4 text-gray-600">You currently have no tasks available.</p>
            <p class="">You have projects but no columns. Add columns to your project to categorize tasks.</p>
            <div class="mt-6">
              <a
                v-for="project in projects"
                :key="project.id"
                class="inline-block px-6 py-3 mr-2 capitalize hover:scale-105 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue transition"
                :href="route('project.show', project.id)"
              >
               {{ project.title }}
              </a>
            </div>
          </div>
        </div>

        <div v-else-if="projects.length > 0 && hasStatuses && tasks.data.length === 0 && !isLoading" class="text-center py-10">
          <div class="bg-light-gray p-6 rounded-lg shadow-md">
            <h2 class="text-navy-blue text-2xl font-bold">Welcome to Your Task Dashboard!</h2>
            <p class="mt-4 text-gray-600">You have projects and statuses, but no tasks. Add tasks to get started.</p>
            <div class="mt-6">
              <button
                class="inline-block px-6 py-3 hover:scale-105 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue transition"
                @click="createTask"
              >
                Create New Task
              </button>
            </div>
          </div>
        </div>

        <!-- Summary Widgets -->
        <div v-else>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-color-white shadow rounded">
              <h3 class="text-sm font-semibold text-gray-600">Total Tasks</h3>
              <p class="text-2xl font-bold text-navy-blue">{{ statistics.totalTasks }}</p>
            </div>
            <div class="p-4 bg-color-white shadow rounded">
              <h3 class="text-sm font-semibold text-gray-600">Completed Tasks</h3>
              <p class="text-2xl font-bold text-green">{{ statistics.completedTasks }}</p>
            </div>
            <div class="p-4 bg-color-white shadow rounded">
              <h3 class="text-sm font-semibold text-gray-600">Pending Tasks</h3>
              <p class="text-2xl font-bold text-red-warning">{{ statistics.pendingTasks }}</p>
            </div>
          </div>

          <!-- Tasks List -->
          <div v-if="isLoading" class="text-center py-10">
            <span>Loading tasks...</span>
          </div>
          <div v-else-if="tasks.data.length === 0" class="text-center py-10">
            <p class="text-navy-blue">No tasks found</p>
          </div>
          <div
            v-else
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
          >
            <TaskCardNew
              v-for="item in tasks.data"
                :key="item.id"
                :task="item"
                :tasks="item.project.tasks"
                :completedId="item.project.completed_status_id"
                :members="item.project.users"
                :project="item.project"
                :labels="item.project.labels"
            />
          </div>

          <!-- Pagination -->
          <div v-if="tasks.data.length" class="mt-6 flex justify-between items-center">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-4 py-2 bg-sky-blue text-color-white rounded hover:bg-crystal-blue"
            >
              Previous
            </button>
            <span class="text-sm">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-4 py-2 bg-sky-blue text-color-white rounded hover:bg-crystal-blue"
            >
              Next
            </button>
          </div>
        </div>

      </main>
    </div>
    <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
      <NewTaskModal @close="isTaskModalOpen = false" :projects="tasksProjects" :labels="labels"/>
    </Modal>
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
