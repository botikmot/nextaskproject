<script setup>
import { ref, defineEmits } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
const emit = defineEmits();

const props = defineProps({
  project_id: Number,
});

const form = useForm({
    name: '',
});


const cancel = () => {
    emit('close');
}

const submitColumn = () => {    
    form.post(`/status/${props.project_id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully created.')
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating column', error)
        }
    })
}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Create New Column</h3>
        <form @submit.prevent="createColumn">
            <div class="mb-4">
                <label for="projectName" class="block text-sm font-medium text-navy-blue">Column Name</label>
                <input type="text" id="projectName" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <!-- <div class="mb-4">
                <label for="projectDescription" class="block text-sm font-medium text-navy-blue">Description</label>
                <textarea id="projectDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
            </div> -->
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitColumn">Save</button>
            </div>
        </form>
    </div>
</template>