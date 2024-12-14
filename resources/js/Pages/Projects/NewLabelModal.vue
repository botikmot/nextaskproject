<script setup>
import { ref, defineEmits } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';

const emit = defineEmits();

const props = defineProps({
  labels: Object,
  project_id: String,
});

const form = useForm({
    name: '',
    color: '#d3d3d3',
});

let isUpdate = ref(false);
const labelId = ref(null);

const cancel = () => {
    if(!isUpdate.value){
        emit('close');
    }else{
        form.name = ''
        form.color = '#d3d3d3'
        isUpdate.value = false
        labelId.value = null
    }
    
}

const updateLabel = (label) => {
    form.name = label.name
    form.color = label.color
    isUpdate.value = true
    labelId.value = label.id
}

const submitLabel = () => {
    if(!isUpdate.value){    
        form.post(`/tasks/label/${props.project_id}`, {
            data: form,
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
                console.log('Successfully created.')
                Swal.fire({
                    text: "Label/Tag successfully created!",
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'success',
                });
            },
            onError: (error) => {
                console.error('Error creating label', error)
            }
        })
    }else{
        console.log('Update label')
        form.put(`/tasks/label/${labelId.value}`, {
            data: form,
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
                isUpdate.value = false
                labelId.value = null
                console.log('Successfully updated.')
                Swal.fire({
                    text: "Label/Tag successfully updated!",
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'success',
                });
            },
            onError: (error) => {
                console.error('Error updating label', error)
            }
        })
    }
}


</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Add New Label/Tag</h3>
        <div class="pb-3">
            <div class="my-2" v-for="label in labels" :key="label.id">
                <span @click="updateLabel(label)" :style="{ backgroundColor: label.color }" class="text-sm cursor-pointer rounded-full px-3 py-1">{{ label.name }}</span>
            </div>
        </div>
        <form @submit.prevent="createLabel">
            <div class="mb-4">
                <label for="labelName" class="block text-sm font-medium text-navy-blue">Label Name</label>
                <input type="text" id="labelName" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
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
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">
                    {{ isUpdate ? 'Cancel Update' : 'Cancel'}}
                </button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitLabel">
                    {{ isUpdate ? 'Update' : 'Add'}}
                </button>
            </div>
        </form>
    </div>
</template>