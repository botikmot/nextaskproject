<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import { eventBus } from '../eventBus';

const emit = defineEmits();

const props = defineProps({
  project_id: String,
  column_id: String,
  projects: Array,
  project: Object,
  index: Number,
  //labels: Object,
});

const statuses = ref([])
const project_id = ref(null)
const labels = ref(props.project ? props.project.labels : [])

const challenges = usePage().props.participantChallenges

const form = useForm({
    title: '',
    description: '',
    due_date: '',
    status: 'To Do',
    priority: 'medium',
    labels: [],
    status_id: props.column_id,
    assigned_members: [],
    index: props.index,
    project: null,
    points: 0,
    challenge_ids: [],
});

const hideSidebar = computed(() => route().current('project.show'));

const cancel = () => {
    emit('close');
}

const submitTask = () => {    
    
    let projectId = props.project_id ? props.project_id : project_id.value
    
    if(!projectId || !form.status_id){
        return
    }

    form.post(`/task/${projectId}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully created.')
            // Emit taskAdded event
            eventBus.emitTaskAdded(); 
            Swal.fire({
                icon: usePage().props.flash.success ? "success" : "error",
                text: usePage().props.flash.message
            });
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating task', error)
        }
    })
}

const selectProject = () => {
    console.log('form.project', form.project)
    statuses.value = form.project.statuses
    project_id.value = form.project.id
    form.status_id = form.project.statuses[0].id
    labels.value = form.project.labels
}

onMounted(() => {
    form.assigned_members.push(usePage().props.auth.user.id)
});

</script>

<template>
    <div class="bg-color-white rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">New Task</h3>
        <form @submit.prevent="createTask">

            <div v-if="!hideSidebar">
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium">Projects</label>
                    <select
                        id="status"
                        v-model="form.project"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                        @change="selectProject"
                        required
                    >
                        <option v-for="project in projects" :key="project.id" :value="project">{{ project.title }}</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium">Status/Column</label>
                    <select
                        id="status"
                        v-model="form.status_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                        required
                    >
                        <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="projectName" class="block text-sm font-medium">Task Name</label>
                <input type="text" id="projectName" v-model="form.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <div class="mb-4">
                <label for="projectDescription" class="block text-sm font-medium">Description</label>
                <textarea id="projectDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
            </div>

            <div class="block sm:flex w-full">
                <div class="mb-4 w-full sm:w-1/3 mr-1">
                    <label for="dueDate" class="block text-sm font-medium">Due Date</label>
                    <input
                        type="date"
                        id="dueDate"
                        v-model="form.due_date"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    />
                </div>
                <div class="mb-4 w-full sm:w-1/3 mx-1">
                    <label for="status" class="block text-sm font-medium">Priority</label>
                    <select
                        id="status"
                        v-model="form.priority"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="mb-4 w-full sm:w-1/3 ml-1">
                    <label for="status" class="block text-sm font-medium">Status</label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    >
                        <option value="To Do">To Do</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="assignedMembers" class="block text-sm font-medium">Assigned Members</label>
                <select
                    v-if="hideSidebar"
                    id="assignedMembers"
                    v-model="form.assigned_members"
                    multiple
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option class="text-navy-blue" v-for="member in project.users" :key="member.id" :value="member.id">{{ member.name }}</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="labels" class="block text-sm font-medium">Labels/Tags</label>
                <select
                    id="assignedMembers"
                    v-model="form.labels"
                    multiple
                    class="mt-1 block w-full border-gray-300 rounded-md focus:ring focus:ring-sky-blue"
                >
                    <option class="text-navy-blue" v-for="label in labels" :key="label.id" :value="label.id">{{ label.name }}</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="points" class="block text-sm font-medium text-gray-700">Points for Completion</label>
                <input
                    type="number"
                    id="points"
                    min="0"
                    v-model="form.points"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
            </div>
            <div class="mb-4">
                <label for="points" class="block text-sm font-medium text-gray-700">Related Challenge</label>
                <select
                    id="challengeIds"
                    v-model="form.challenge_ids"
                    multiple
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option v-for="challenge in challenges" :key="challenge.id" :value="challenge.id">{{ challenge.name }}</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitTask">Save</button>
            </div>
        </form>
    </div>
</template>