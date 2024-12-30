<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import ChallengeDetails from './ChallengeDetails.vue';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';

const isViewDetails = ref(false)
const details = ref({})

const props = defineProps({
    challenges: Array,
});

const form = useForm({
    id: null,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
}

const viewDetails = (data) => {
    details.value = data
    isViewDetails.value = true
}

const joinChallenge = (id) => {
    form.id = id
    form.post(`challenges/${id}/join`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            Swal.fire({
                icon: usePage().props.flash.success ? "success" : "error",
                text: usePage().props.flash.message
            });
        },
        onError: (error) => {
            console.error('Error joining challenge', usePage().props.flash.message)
        }
    })
}

console.log('challenges', props.challenges)

</script>

<template>
    <div class="challenges">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
            v-for="challenge in challenges"
            :key="challenge.id"
            :class="{
                'p-4 border rounded shadow hover:shadow-xl transition hover:cursor-pointer': true,
                'bg-light-gray border-dark-gray': challenge.isJoined, // Highlight joined challenges
                'bg-color-white border-dark-gray': !challenge.isJoined
            }"
            @click="viewDetails(challenge)"
            >
            <h2 class="text-lg font-semibold text-navy-blue">{{ challenge.name }}</h2>
            <p class="text-sm text-gray-500">{{ challenge.description }}</p>
            <p class="text-xs mt-2">Points: {{ challenge.points }}</p>
            <p class="text-xs">Start: {{ formatDate(challenge.start_date) }}</p>
            <p class="text-xs">End: {{ formatDate(challenge.end_date) }}</p>

            <!-- Conditionally show buttons or badges -->
            <div v-if="challenge.isJoined" class="mt-4">
                <!-- <span class="inline-block bg-green-500 text-white py-1 px-3 rounded text-xs font-medium">
                Joined
                </span> -->
                <button
                @click.stop="viewProgress(challenge.id)"
                class="mt-2 bg-crystal-blue text-navy-blue py-1 px-3 rounded hover:bg-navy-blue hover:text-color-white"
                >
                View Progress
                </button>
            </div>
            <button
                v-else
                @click.stop="joinChallenge(challenge.id)"
                class="mt-4 bg-sky-blue text-color-white py-1 px-3 rounded hover:bg-navy-blue"
            >
                Join Challenge
            </button>
            </div>
        </div>
    </div>

    <Modal :show="isViewDetails" @close="isViewDetails = false">
        <ChallengeDetails @close="isViewDetails = false" :challenge="details"/>
    </Modal>
</template>
  