<script setup>
import { ref, defineEmits } from 'vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';

const props = defineProps({
  project: Object,
});
const emit = defineEmits();

const selectedCompletedStatus = ref(props.project.completed_status_id);

const form = useForm({
    filter: '',
    user_id: null,
    completed_status_id: null,
});

const updateCompletedStatus = () => {
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
}

const cancel = () => {
    emit('close');
}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Completion Indicator</h3>
        <form v-if="project.user_id == $page.props.auth.user.id">
            <div class="text-md text-navy-blue">
                <!-- <label>Completion Indicator:</label> -->
                <select class="ml-2 py-1 rounded-full text-md" v-model="selectedCompletedStatus">
                    <option v-for="status in project.statuses" :key="status.id" :value="status.id">
                        {{ status.name }}
                    </option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="updateCompletedStatus">Save</button>
            </div>
        </form>
    </div>
</template>