<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    task: Object,
});

let isUpdateSubtask = ref(false);

const form = useForm({
    subtask: '',
    name: '', // subtasks name
    is_completed: null,
    subtask_id: null,
});

const updateSubtaskStatus = (subtask) => {
    console.log('subtask', subtask)
    subtask.is_completed = !subtask.is_completed;

    form.name = subtask.name
    form.is_completed = subtask.is_completed
    form.put(`/task/${subtask.id}/subtask`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            if(response.props.flash.success){
                form.reset()
            }else{
                Swal.fire({
                    text: response.props.flash.message,
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'error',
                });
            }
        },
        onError: (error) => {
            console.error('Error updating subtasks', error)           
        }
    })
}

const submitSubtask = () => {
    console.log(form.subtask)
    if(isUpdateSubtask.value){
        updateSubtask()
        return
    }

    form.post(`/task/${props.task.id}/subtask`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {            
            if(response.props.flash.success){
                form.reset()
                form.subtask = ''
            }else{
                Swal.fire({
                    text: response.props.flash.message,
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'error',
                });
            }
        },
        onError: (error) => {
            console.error('Error adding subtasks', error)
        }
    })

}

const editSubtask = (id, subtask, is_completed) => {
    isUpdateSubtask.value = true
    form.subtask_id = id
    form.subtask = subtask
    form.is_completed = is_completed
}

const updateSubtask = () => {
    console.log('update')
    form.name = form.subtask
    form.put(`/task/${form.subtask_id}/subtask`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            if(response.props.flash.success){
                form.reset()
                isUpdateSubtask.value = false
                form.subtask = ''
            }else{
                Swal.fire({
                    text: response.props.flash.message,
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'error',
                });
            }

        },
        onError: (error) => {
            console.error('Error updating subtasks', error)
        }
    })
}

const confirmDelete = (id) => {
    console.log('subtask -id', id)

    form.delete(`/task/${id}/subtask`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            if(response.props.flash.success){
                form.reset()
            }else{
                Swal.fire({
                    text: response.props.flash.message,
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'error',
                });
            }
        },
        onError: (error) => {
            console.error('Error deleting subtasks', error)
        }
    })

}

const cancelUpdate = () => {
    isUpdateSubtask.value = false
    form.subtask = ''
}

</script>


<template>
    <div>
        <div v-for="(subtask, index) in task.subtasks" :key="subtask.id" class="py-1 px-3 flex justify-between" :class="index % 2 === 0 ? 'bg-light-gray' : 'bg-dark-gray'">
            <div class="flex items-center">
                <input
                    type="checkbox"
                    :checked="subtask.is_completed"
                    @change="updateSubtaskStatus(subtask)"
                    class="form-checkbox h-4 w-4 text-blue-600"
                />
                
                <div class="text-sm pl-2" :class="{ 'line-through text-gray-500': subtask.is_completed }">
                    {{ subtask.name }}
                </div>
            </div>
            <div class="cursor-pointer">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <i class="fa-solid fa-ellipsis"></i>
                    </template>
                    <template #content>
                        <div
                            class="hover:bg-crystal-blue px-3 py-2 text-sm"
                            @click="editSubtask(subtask.id, subtask.name, subtask.is_completed)"
                        >
                            Edit
                        </div>
                        <div
                            class="hover:bg-crystal-blue px-3 py-2 text-sm"
                            @click.stop="confirmDelete(subtask.id)"
                        >
                            Remove
                        </div>
                    </template>
                </Dropdown>
            </div>
        </div>
        <TextInput
            placeholder="Enter subtask here..."
            v-model="form.subtask"
            class="mt-4 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
            @keyup.enter="submitSubtask"
        />
        <div class="flex justify-end" v-if="isUpdateSubtask">
            <button
                @click="cancelUpdate"
                class="font-semibold text-xs bg-color-white text-navy-blue py-1 mt-2 px-3 mr-2 rounded-full inline-block hover:bg-gray hover:text-color-white"
                >
                Cancel Update
            </button>

            <button
                @click="updateSubtask"
                class="font-semibold text-xs bg-sky-blue text-linen py-1 mt-2 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                >
               Save
            </button>
        </div>

    </div>
</template>