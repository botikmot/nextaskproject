<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, watch} from 'vue';
import NewColumnModal from './NewColumnModal.vue';
import AddMemberModal from './AddMemberModal.vue';
import Columns from './Columns.vue';
import Modal from '@/Components/Modal.vue';
import { eventBus } from '../eventBus';
import Dropdown from '@/Components/Dropdown.vue';
import NewLabelModal from './NewLabelModal.vue';
import CompletionIndicator from './CompletionIndicator.vue';

let isModalOpen = ref(false);
let isMemberModalOpen = ref(false);
let isLabelModalOpen = ref(false)
let isCompletionModalOpen = ref(false)

const isLgScreen = ref(false);
const isCollapsed = ref(false);
const taskView = ref(localStorage.getItem('taskView') || 'myTasks');

const props = defineProps({
  project: Object,
  roles: Object,
  userRole: String,
});

const page = usePage();
console.log('project', props.project)
console.log('userRole', props.userRole)
//const selectedCompletedStatus = ref(props.project.completed_status_id);
const form = useForm({
    filter: '',
    user_id: null,
    completed_status_id: null,
});

const openModal = () => {
    isModalOpen.value = true;
}

const openMemberModal = () => {
    isMemberModalOpen.value = true;
}

const openLabelModal = () => {
    isLabelModalOpen.value = true;
}

function checkScreenSize() {
  isLgScreen.value = window.innerWidth >= 1024; // 1024px is Tailwind's lg breakpoint
}

const containerWidth = computed(() => {
    console.log(isCollapsed.value)
    if (isLgScreen.value) {
        //return eventBus.isCollapsed.value ? 'calc(100vw - 140px)' : 'calc(100vw - 320px)';
        return eventBus.isCollapsed.value ? 'calc(100vw - 110px)' : 'calc(100vw - 280px)';
    }
    return 'calc(100vw - 30px)'; // width for non-large screens
    //return 'calc(100vw - 60px)'; 
});

/* const updateCompletedStatus = () => {
    console.log('selectedCompletedStatus', selectedCompletedStatus.value)
    form.completed_status_id = selectedCompletedStatus.value
    form.post(`/projects/${props.project.id}/update-completed-status`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully updated.')
        },
        onError: (error) => {
            console.error('Error updating project', error)
        }
    })
} */

const filterTasks = () => {
    form.filter = taskView.value
    form.user_id = page.props.auth.user.id
    
    localStorage.setItem('taskView', taskView.value);

    form.get(`/project/${props.project.id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    console.log('Successfully fetched.')
                },
                onError: (error) => {
                    console.error('Error fetching project', error)
                }
            })
};


onMounted(() => {
    const storedCollapsed = localStorage.getItem('collapsed');
    isCollapsed.value = storedCollapsed === 'true';
    taskView.value = localStorage.getItem('taskView') || 'myTasks';
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkScreenSize);
});

</script>

<template>
    <Head :title="`${project.title}`" />

    <AuthenticatedLayout :pageTitle="`Board - ${project.title} (${project.progress}%)`">
        <div class="w-full bg-crystal-blue p-4">
            <div class="justify-between flex mb-2">
                <div class="flex items-center">
                    <div>
                        <p v-if="project.description" class="text-navy-blue font-bold text-lg hidden sm:flex">
                            {{ project.description }}
                        </p>
                        <p v-else class="text-navy-blue text-lg mb-3 hidden sm:flex">
                            Visualize Your Workflow and Drive Progress.
                        </p>
                        <p v-if="project.statuses.length" class="text-xs py-1 text-center sm:text-left sm:py-0 hidden sm:flex">Effortlessly Shift Tasks Between Stages with Drag-and-Drop.</p>
                    </div>
                    <!-- <div class="relative flex items-center pl-4 justify-center sm:justify-start">
                        <div v-for="(member, index) in project.members.slice(0, 5)" :key="member.id" class="relative -mr-5">
                            <img :src="'/' + member.user.profile_image" alt="Profile" class="w-8 h-8 rounded-full border-2 border-color-white" />
                        </div>
                        
                        <div v-if="project.members.length > 5" class="flex items-center text-navy-blue ml-6">
                            <span class="text-lg font-bold">+{{ project.members.length - 5 }}</span>
                        </div>
                    </div> -->

                    <!-- <div v-if="project.user_id == $page.props.auth.user.id" class="block sm:flex pl-6">
                        <div class="flex items-center">
                            <div @click="openMemberModal" class="cursor-pointer ml-2 my-3 sm:my-0 relative group">
                                <div class="flex items-center justify-center rounded-full">
                                    <i class="fa-solid fa-users text-xl text-sky-blue hover:text-navy-blue"></i>
                                </div>
                                <span class="absolute top-full mt-1 -left-6 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                                    Members
                                </span>
                            </div>
                        </div>

                    </div> -->


                </div>
                <div v-if="project.statuses.length" class="flex items-center justify-center sm:justify-start">
                    <!-- <div  v-if="project.user_id == $page.props.auth.user.id" class="text-md xl:flex items-center text-navy-blue hidden">
                        <label>Completion Indicator:</label>
                        <select class="ml-2 py-1 rounded-full text-md" v-model="selectedCompletedStatus" @change="updateCompletedStatus">
                            <option v-for="status in project.statuses" :key="status.id" :value="status.id">
                                {{ status.name }}
                            </option>
                        </select>
                    </div> -->
                    <div class="text-lg flex items-center py-1 rounded-full bg-linen text-navy-blue pl-3 mr-3">
                        <i class="fa-solid fa-filter text-navy-blue"></i>
                        <select class="filter py-1 bg-linen rounded-full text-sm border-0 cursor-pointer focus:outline-none focus:border-transparent" v-model="taskView" @change="filterTasks">
                            <option class="py-1 cursor-pointer" value="all">All Tasks</option>
                            <option class="py-1 cursor-pointer" value="myTasks">My Tasks</option>
                        </select>
                    </div>

                    <!-- <div class="relative group">
                        <button
                            v-if="project.user_id == $page.props.auth.user.id"
                            @click="openModal"
                            class="flex items-center justify-center font-semibold text-sm bg-sky-blue text-color-white py-2 px-2 lg:px-4 rounded-full hover:bg-navy-blue hover:shadow-lg"
                        >
                            <i class="fas fa-plus mr-0 lg:mr-2 text-md "></i><span class="hidden lg:flex">Add Column</span>
                        </button>
                    </div> -->

                    <div v-if="project.user_id == $page.props.auth.user.id" class="cursor-pointer relative">
                        <Dropdown align="right" width="64">
                            <template #trigger>
                                <i class="fa-solid fa-gear text-navy-blue text-3xl hover:text-sky-blue"></i>
                            </template>
                            <template #content>
                                <div @click="openModal" class="hover:bg-crystal-blue px-3 flex items-center py-2">
                                    <i class="fa-solid fa-table-columns"></i> <span class="pl-2 text-sm">+ Column</span>
                                </div>
                                <div @click="openMemberModal" class="hover:bg-crystal-blue px-3 flex items-center py-2">
                                    <i class="fa-solid fa-users-gear"></i> <span class="pl-2 text-sm">Members</span>
                                </div>
                                <div @click="openLabelModal" class="hover:bg-crystal-blue px-3 flex items-center py-2">
                                    <i class="fa-solid fa-tag"></i> <span class="pl-2 text-sm">Labels/Tags</span>
                                </div>
                                <div @click="isCompletionModalOpen = true" class="hover:bg-crystal-blue px-3 flex items-center py-2">
                                    <i class="fa-solid fa-check-double"></i> <span class="pl-2 text-sm">Completion Indicator</span>
                                </div>
                            </template>
                        </Dropdown>
                        
                    </div>

                </div>
            </div>
                        
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
                <Columns
                    v-for="column in project.statuses"
                    :key="column.id"
                    :column="column"
                    :auth_id="$page.props.auth.user.id"
                    :project_id="project.id"
                    :project="project"
                    :labels="project.labels"
                    class="p-4 min-w-72 mr-2 rounded shadow-lg mb-4"
                    :style="{ backgroundColor: column.color }"
                />
            </div>

            
            <Modal :show="isModalOpen" @close="isModalOpen = false">
                <NewColumnModal @close="isModalOpen = false" :project_id="project.id"/>
            </Modal>
            
            <Modal :show="isMemberModalOpen" @close="isMemberModalOpen = false">
                <AddMemberModal @close="isMemberModalOpen = false" :project_id="project.id" :members="project.users" :roles="roles"/>
            </Modal>

            <Modal :show="isLabelModalOpen" @close="isLabelModalOpen = false">
                <NewLabelModal :labels="project.labels" :project_id="project.id" @close="isLabelModalOpen = false" />
            </Modal>

            <Modal :show="isCompletionModalOpen" @close="isCompletionModalOpen = false">
                <CompletionIndicator :project="project" @close="isCompletionModalOpen = false" />
            </Modal>
            
        </div>
    </AuthenticatedLayout>
</template>

<style>
select:focus {
  outline: none;
  box-shadow: none;
  border: none;
}

</style>