<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, watch} from 'vue';
import NewColumnModal from './NewColumnModal.vue';
import AddMemberModal from './AddMemberModal.vue';
import Columns from './Columns.vue';
import Modal from '@/Components/Modal.vue';
import { eventBus } from '../eventBus';

let isModalOpen = ref(false);
let isMemberModalOpen = ref(false);
const isLgScreen = ref(false);
const isCollapsed = ref(false);

const props = defineProps({
  project: Object,
});

console.log('project', props.project)

const openModal = () => {
    isModalOpen.value = true;
}

const openMemberModal = () => {
    isMemberModalOpen.value = true;
}

function checkScreenSize() {
  isLgScreen.value = window.innerWidth >= 1024; // 1024px is Tailwind's lg breakpoint
}

const containerWidth = computed(() => {
    console.log(isCollapsed.value)
    if (isLgScreen.value) {
        return eventBus.isCollapsed.value ? 'calc(100vw - 130px)' : 'calc(100vw - 320px)';
        //return isCollapsed.value ? 'calc(100vw - 130px)' : 'calc(100vw - 320px)'; // 16rem when collapsed, 320px when expanded
    }
    return 'calc(100vw - 60px)'; // width for non-large screens
});

onMounted(() => {
    const storedCollapsed = localStorage.getItem('collapsed');
    isCollapsed.value = storedCollapsed === 'true';

    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkScreenSize);
});

</script>

<template>
    <Head title="Board" />

    <AuthenticatedLayout pageTitle="Board">
        <div class="w-full bg-linen p-4">
            <div class="justify-between flex">
                <div class="mb-4">
                    <div class="block xl:flex">
                        <div class="block sm:flex">
                            <h1 class="text-2xl text-navy-blue font-bold pr-3">{{ project.title }} - Kanban Board</h1>
                            <div class="flex items-center">
                                <div @click="openMemberModal" class="px-3 cursor-pointer my-3 sm:my-0 relative group">
                                    <div class="w-10 h-10 flex items-center justify-center bg-sky-blue rounded-full border-2 hover:bg-navy-blue border-color-white">
                                        <i class="fa-solid fa-user-plus text-md text-linen"></i>
                                    </div>
                                    <span class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                                        Invite Collaborator
                                    </span>
                                </div>

                                <div class="relative flex items-center">
                                    <div v-for="(member, index) in project.members.slice(0, 5)" :key="member.id" class="relative -mr-5">
                                        <img :src="'/' + member.user.profile_image" alt="Profile" class="w-10 h-10 rounded-full border-2 border-color-white" />
                                    </div>
                                    <!-- Show the '+' if there are more than 5 members -->
                                    <div v-if="project.members.length > 5" class="flex items-center text-navy-blue ml-6">
                                        <span class="text-xl font-bold">+{{ project.members.length - 5 }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <p v-if="project.description" class="text-navy-blue text-lg mb-3 pl-1">
                         {{ project.description }}
                    </p>
                    <p v-else class="text-navy-blue text-lg mb-3">
                        Visualize Your Workflow and Drive Progress.
                    </p>
                    <p v-if="project.statuses.length" class="text-xs pl-1">Effortlessly Shift Tasks Between Stages with Drag-and-Drop.</p>
                </div>
                <div v-if="project.statuses.length" class="mb-4">
                    <div class="relative group">
                        <button
                            v-if="project.user_id == $page.props.auth.user.id"
                            @click="openModal"
                            class="font-semibold text-lg bg-navy-blue text-linen w-10 h-10 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                        >
                            <i class="fas fa-plus"></i> 
                        </button>
                        <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Add New Column
                        </span>
                    </div>
                </div>
            </div>
                        
            <!-- Kanban Columns -->
            <div v-if="!project.statuses.length">
                <p class="text-navy-blue text-lg">
                    Start by adding columns to structure your workflow.
                </p>
                <p class="text-navy-blue mb-4">Columns help you organize tasks in different stages of completion, such as '<span class="font-bold">To Do</span>', '<span class="font-bold">In Progress</span>', and '<span class="font-bold">Done</span>'.</p>
                <button
                    v-if="project.user_id == $page.props.auth.user.id"
                    @click="openModal"
                    class="font-semibold text-lg bg-sky-blue text-linen py-2 px-6 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                    >
                    + Add New Column
                </button>
            </div>
        
            <div v-else class="flex overflow-x-auto" :style="{ width: containerWidth }">
            <!-- <div v-else class="flex overflow-x-auto" :style="!isLgScreen ? { width: `calc(100vw - 60px)` } : {width: `calc(100vw - 320px)`}"> -->
            <!-- <div v-else class="w-full overflow-x-auto">
                <div class="flex w-max"> -->
                    <Columns
                        v-for="column in project.statuses"
                        :key="column.id"
                        :column="column"
                        :auth_id="$page.props.auth.user.id"
                        :project_id="project.id"
                        :project="project"
                        class="bg-gray-100 p-4 min-w-80 mr-4 rounded shadow-lg mb-4 border border-dark-gray"
                    />
                <!-- </div> -->
            </div>

            <!-- Modal -->
            <Modal :show="isModalOpen" @close="isModalOpen = false">
                <NewColumnModal @close="isModalOpen = false" :project_id="project.id"/>
            </Modal>
            
            <!-- Add Member Modal -->
            <Modal :show="isMemberModalOpen" @close="isMemberModalOpen = false">
                <AddMemberModal @close="isMemberModalOpen = false" :project_id="project.id"/>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.grid {
    display: grid;
    gap: 1rem;
}

.bg-gray-100 {
    background-color: #f7f7f7;
}

.bg-white {
    background-color: #ffffff;
}

.text-blue-500 {
    color: #3182ce;
}

.text-gray-600 {
    color: #718096;
}

.shadow {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
