<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';

const errorMessage = ref('')
const successMessage = ref('')

const emit = defineEmits();

const form = useForm({
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    is_team_based: false,
    points: 0,
});

const createChallenge = () => {
    console.log(form)
    form.post('/challenges', {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            Swal.fire({
                icon: usePage().props.flash.success ? "success" : "error",
                text: usePage().props.flash.message
            });
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating challenge', usePage().props.flash.message)
        }
    })

}

</script>

<template>
    <div class="w-full mx-auto p-6 bg-white">
        <h2 class="text-2xl font-semibold mb-6">Create New Challenge</h2>

        <form @submit.prevent="createChallenge" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Challenge Name</label>
                <input
                type="text"
                id="name"
                v-model="form.name"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
                />
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                id="description"
                v-model="form.description"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                rows="4"
                required
                ></textarea>
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input
                type="date"
                id="start_date"
                v-model="form.start_date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
                />
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input
                type="date"
                id="end_date"
                v-model="form.end_date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
                />
            </div>

            <div class="flex items-center">
                <input
                type="checkbox"
                id="is_team_based"
                v-model="form.is_team_based"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label for="is_team_based" class="ml-2 block text-sm font-medium text-gray-700">Is Team Based?</label>
            </div>

            <div>
                <label for="points" class="block text-sm font-medium text-gray-700">Points for Completion</label>
                <input
                type="number"
                id="points"
                v-model="form.points"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-sky-blue text-color-white py-2 px-4 rounded-md shadow hover:bg-navy-blue focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Create Challenge
            </button>
        </form>

        <div v-if="errorMessage" class="mt-4 text-sm text-red-600">{{ errorMessage }}</div>
        <div v-if="successMessage" class="mt-4 text-sm text-green-600">{{ successMessage }}</div>
  </div>
</template>