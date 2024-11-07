<script setup>
import { ref, defineEmits } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
const emit = defineEmits();

const props = defineProps({
  project_id: Number,
  column_id: Number,
  project: Object,
});

const form = useForm({
    title: '',
    description: '',
    due_date: '',
    status: 'To Do',
    priority: 'medium',
    labels: '',
    status_id: props.column_id,
    assigned_members: []
});


const cancel = () => {
    emit('close');
}

const submitTask = () => {    

    console.log(form)

    form.post(`/task/${props.project_id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully created.')
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating task', error)
        }
    })
}

</script>

<template>
    <div class="bg-color-white rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Add Task</h3>
        <form @submit.prevent="createTask">
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
                    id="assignedMembers"
                    v-model="form.assigned_members"
                    multiple
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option class="text-navy-blue" v-for="member in project.members" :key="member.id" :value="member.user_id">{{ member.user.name }}</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="labels" class="block text-sm font-medium">Labels <span class="text-xs text-gray pl-1">(e.g., Bug, Feature, Enhancement)</span></label>
                <input
                    type="text"
                    id="labels"
                    v-model="form.labels"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    placeholder="Add labels, separated by commas"
                />
            </div>

            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitTask">Save</button>
            </div>
        </form>
    </div>
</template>