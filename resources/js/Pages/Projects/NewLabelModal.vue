<script setup>
import { ref, defineEmits } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'

const emit = defineEmits();

const props = defineProps({
  labels: Object,
});

const form = useForm({
    name: '',
});

const cancel = () => {
    emit('close');
}

const submitLabel = () => {    
    form.post('/tasks/label', {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully created.')
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating label', error)
        }
    })
}


</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Add New Label/Tag</h3>
        <div class="pb-3">
            <div v-for="label in labels" :key="label.id">
                {{ label.name }}
            </div>
        </div>
        <form @submit.prevent="createLabel">
            <div class="mb-4">
                <label for="labelName" class="block text-sm font-medium text-navy-blue">Label Name</label>
                <input type="text" id="labelName" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <!-- <div class="mb-4">
                <label for="projectDescription" class="block text-sm font-medium text-navy-blue">Description</label>
                <textarea id="projectDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
            </div> -->
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitLabel">Save</button>
            </div>
        </form>
    </div>
</template>