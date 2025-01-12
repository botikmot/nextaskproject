<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import CreateProjectModal from './CreateProjectModal.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import moment from 'moment';
import Modal from '@/Components/Modal.vue';
import UpdateProjectModal from './UpdateProjectModal.vue';
import PieChart from './PieChart.vue';

let isModalOpen = ref(false);
let isProjectModalOpen = ref(false);
let project = ref({})

const props = defineProps({
  projects: Object,
  userRole: String,
});

console.log(props.projects);

const openModal = () => {
    isModalOpen.value = true;
}

const openProjectModal = (data) => {
    project.value = data
    isProjectModalOpen.value = true;
}

const searchQuery = ref('')
const filter = ref('')

const completedProjects = computed(() => {
    return props.projects.filter((project) => project.progress === 100).length;
});

const ongoingProjects = computed(() => {
    return props.projects.filter((project) => project.progress < 100).length;
});

const form = useForm({
    id: null,
    filter: localStorage.getItem('taskView') || 'myTasks',
});

const navigateToProject = (projectId) => {
    form.id = projectId
    form.get(`/project/${form.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully fetched.')
        },
        onError: (error) => {
            console.error('Error fetching data', error)
        }
    })
}

console.log('projects', props.projects)

const confirmDelete = (id) => {
    form.id = id
    Swal.fire({
        title: 'Are you sure?',
        text: "Once deleted, this project cannot be recovered.",
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/projects/${form.id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    console.log('Successfully deleted.', usePage().props.flash.message)
                    Swal.fire({
                        icon: usePage().props.flash.success ? "success" : "error",
                        text: usePage().props.flash.message
                    });
                },
                onError: (error) => {
                    console.error('Error deleting project', usePage().props.flash.message)
                }
            })
        }
    });
}

const formatDate = (date) => {
    return moment(date).fromNow()
}

const formatDeadline = (date) => {
    return moment(date).format('LL');
}

</script>

<template>
    <Head title="Projects" />
  
    <AuthenticatedLayout pageTitle="Projects">
      <div v-if="projects.length"class="flex flex-col space-y-6 w-full bg-light-gray p-6">
        <!-- Header Section -->
        <section class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
          <div>
            <p class="text-navy-blue">
              Organize and manage your projects effectively. Track progress, collaborate with your team, and achieve your goals.
            </p>
          </div>
          <!-- Search and Filters -->
          <div class="flex items-center space-x-4 mt-4 lg:mt-0">
            <input
              v-model="searchQuery"
              type="text"
              class="px-4 py-2 border rounded-lg focus:outline-none"
              placeholder="Search projects..."
            />
            <select v-model="filter" class="px-4 py-2 border rounded-lg focus:outline-none">
              <option value="">All</option>
              <option value="completed">Completed</option>
              <option value="ongoing">Ongoing</option>
            </select>
          </div>
        </section>
  
        <!-- Overview Stats -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="bg-color-white p-4 rounded-lg shadow flex items-center space-x-4">
            <i class="fa-solid fa-folder text-sky-blue text-3xl"></i>
            <div>
              <h2 class="text-lg font-bold text-navy-blue">{{ projects.length }}</h2>
              <p class="text-gray">Total Projects</p>
            </div>
          </div>
          <div class="bg-color-white p-4 rounded-lg shadow flex items-center space-x-4">
            <i class="fa-solid fa-check-circle text-green-leaf text-3xl"></i>
            <div>
              <h2 class="text-lg font-semibold text-navy-blue">{{ completedProjects }}</h2>
              <p class="text-gray">Completed Projects</p>
            </div>
          </div>
          <div class="bg-color-white p-4 rounded-lg shadow flex items-center space-x-4">
            <i class="fa-solid fa-spinner text-red-warning text-3xl"></i>
            <div>
              <h2 class="text-lg font-semibold text-navy-blue">{{ ongoingProjects }}</h2>
              <p class="text-gray">Ongoing Projects</p>
            </div>
          </div>
        </section>
  
        <!-- Project List -->
        <section>
          <h2 class="text-xl font-bold text-navy-blue mb-4">Project List</h2>
          <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">
            <div
                v-for="project in projects"
                :key="project.id"
                @click="navigateToProject(project.id)"
                class="bg-color-white p-6 rounded-lg shadow hover:shadow-lg cursor-pointer relative transition"
                >
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-sky-blue">{{ project.title }}</h3>
                    <div v-if="project.user_id == $page.props.auth.user.id" class="text-sm" @click.stop>
                    <Dropdown align="right" width="48">
                        <template #trigger>
                        <i class="fa-solid fa-ellipsis"></i>
                        </template>
                        <template #content>
                        <div class="hover:bg-crystal-blue px-3 py-2" @click="confirmDelete(project.id)">
                            Delete
                        </div>
                        <div class="hover:bg-crystal-blue px-3 py-2" @click="openProjectModal(project)">
                            Edit
                        </div>
                        </template>
                    </Dropdown>
                    </div>
                </div>
                <div class="flex justify-between">
                    <div>
                        <p class="text-gray text-sm mt-2">{{ project.description }}</p>
                        <p class="text-sm text-gray">Deadline: {{ formatDeadline(project.deadline) }}</p>
                        <p class="text-sm text-gray">Members: {{ project.users.length }}</p>
                        <div class="pt-2 flex justify-start">
                            <div class="relative flex items-center">
                                <div v-for="(member, index) in project.users.slice(0, 5)" :key="member.id" class="relative -mr-2">
                                    <img :src="'/' + member.profile_image" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-color-white" />
                                </div>
                                
                                <div v-if="project.users.length > 5" class="flex items-center text-navy-blue ml-6">
                                    <span class="text-lg font-bold">+{{ project.users.length - 5 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center">
                        <PieChart :progress="project.progress" :chartId="'chart-' + project.id" />
                        <p class="text-sm text-navy-blue">Progress {{ project.progress }}%</p>
                    </div>
                </div>
            </div>

  
            <!-- Add New Project Button -->
            <!-- <button
              v-if="userRole == 'admin'"
              @click="openModal"
              class="flex items-center justify-center bg-color-white p-6 rounded-lg shadow hover:shadow-lg cursor-pointer transition"
            >
              <i class="fa-solid fa-plus text-sky-blue text-3xl"></i>
              <span class="ml-2 text-sm font-semibold">Add New Project</span>
            </button> -->

            <button
              @click="openModal"
              class="flex items-center justify-center bg-color-white p-6 rounded-lg shadow hover:shadow-lg cursor-pointer transition"
            >
              <i class="fa-solid fa-plus text-sky-blue text-3xl"></i>
              <span class="ml-2 text-sm text-gray font-semibold">Add New Project</span>
            </button>

          </div>
        </section>
      </div>

      <!-- Placeholder for New Users -->
      <div v-else class="flex flex-col items-center justify-center w-full h-[calc(100vh-150px)] bg-light-gray p-6">
           <!--  <img 
                src="/images/placeholder-new-user.svg" 
                alt="Welcome" 
                class="w-1/2 max-w-md mb-6"
            /> -->
            <p class="text-navy-blue text-xl font-semibold mb-3 text-center">
                Welcome to <span class="font-bold">NexTask</span>! Let's get started with your first project.
            </p>
            <p class="text-gray text-center mb-6">
                Click the button below to create your first project and begin managing tasks, setting deadlines, and collaborating with your team.
            </p>
            <button
                @click="openModal"
                class="font-semibold text-lg hover:scale-105 bg-gradient-to-r from-navy-blue to-sky-blue text-linen py-2 px-6 rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
            >
                Create Your First Project
            </button>
        </div>

  
      <!-- Modal -->
      <Modal :show="isModalOpen" @close="isModalOpen = false">
        <CreateProjectModal @close="isModalOpen = false" />
      </Modal>
      <Modal :show="isProjectModalOpen" @close="isProjectModalOpen = false">
        <UpdateProjectModal :project="project" @close="isProjectModalOpen = false" />
      </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any additional styles here if needed */
</style>
