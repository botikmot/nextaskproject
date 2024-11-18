<script setup>
import { ref, defineEmits } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
const emit = defineEmits();

const props = defineProps({
  column: Object,
});

const form = useForm({
    name: props.column.name,
    color: props.column.color,
});


const cancel = () => {
    emit('close');
}

const updateColumn = () => {    
    form.put(`/status-update/${props.column.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully updated.')
            emit('close')
        },
        onError: (error) => {
            console.error('Error updating column', error)
        }
    })
}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Update Column</h3>
        <form @submit.prevent="createColumn">
            <div class="mb-4">
                <label for="columnName" class="block text-sm font-medium text-navy-blue">Column Name</label>
                <input type="text" id="columnName" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <div class="mb-4">
                <label for="columnColor" class="block text-sm font-medium text-navy-blue">Color</label>
                <div class="flex items-center">
                    <input 
                        type="color" 
                        id="columnColor" 
                        v-model="form.color" 
                        class="mt-1 block border-gray-300 rounded-md h-12 shadow-sm focus:ring focus:ring-sky-blue"
                    > <span class="pl-3">{{ form.color }}</span>
                </div>

            </div>
            <!-- <div class="mb-4">
                <label for="projectDescription" class="block text-sm font-medium text-navy-blue">Description</label>
                <textarea id="projectDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
            </div> -->
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="updateColumn">Save</button>
            </div>
        </form>
    </div>
</template>