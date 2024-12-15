<script setup>
import { ref, defineEmits } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
const emit = defineEmits();

const form = useForm({
    title: '',
    description: '',
    deadline: '',
    color: '#FFFFFF',
});


const cancel = () => {
    emit('close');
}

const submitProject = () => {    
    form.post('/projects', {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully created.')
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating post', error)
        }
    })
}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Create New Project</h3>
        <form @submit.prevent="createProject">
            <div class="mb-4">
                <label for="projectName" class="block text-sm font-medium text-navy-blue">Project Name</label>
                <input type="text" id="projectName" v-model="form.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <div class="mb-4">
                <label for="projectDescription" class="block text-sm font-medium text-navy-blue">Description</label>
                <textarea id="projectDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
            </div>
            <div class="mb-4">
                <label for="projectDescription" class="block text-sm font-medium text-navy-blue">Project Deadline</label>
                <input
                    type="date"
                    id="dueDate"
                    v-model="form.deadline"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                />
            </div>
            <div class="mb-4">
                <label for="columnColor" class="block text-sm font-medium text-navy-blue">Color</label>
                <div class="flex items-center">
                    <input 
                        type="color" 
                        id="columnColor" 
                        v-model="form.color" 
                        class="mt-1 block border-gray-300 rounded-md h-12 shadow-sm focus:ring focus:ring-sky-blue"
                    > 
                    <input type="text" id="projectColor" v-model="form.color" class="mt-1 border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitProject">Save Project</button>
            </div>
        </form>
    </div>
</template>