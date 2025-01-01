<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import ChallengeDetails from './ChallengeDetails.vue';
import ProgressDetails from './ProgressDetails.vue';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import moment from "moment";
import Dropdown from '@/Components/Dropdown.vue';
import NewChallenge from './NewChallenge.vue';

const isViewDetails = ref(false)
const isViewProgressDetails = ref(false)
const progressLoading = ref(null)
const details = ref({})
const progressDetails = ref([])
const isUpdateChallenge = ref(false)

const props = defineProps({
    challenges: Array,
});

const form = useForm({
    id: null,
});

const formatDate = (date) => {
    return moment(date).format('LL')
}

const viewDetails = (data) => {
    details.value = data
    isViewDetails.value = true
}

const joinChallenge = (id, name) => {
    form.id = id

    Swal.fire({
        html: `Are you sure you want to join the <strong style="color: #40a2e3;">"${name}"</strong> challenge?`,
        showCancelButton: true,
        confirmButtonText: "Yes, join it!",
        cancelButtonText: "No, cancel",
        confirmButtonColor: "#40a2e3", // Sky Blue
        cancelButtonColor: "#ff4d4f", // Red
    }).then((result) => {
        if (result.isConfirmed) {
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
    });
}

const leaveChallenge = (id, name) => {
    Swal.fire({
        html: `Are you sure you want to leave this <strong style="color: #40a2e3;">"${name}"</strong> challenge?`,
        showCancelButton: true,
        confirmButtonText: "Yes!",
        cancelButtonText: "No, cancel",
        confirmButtonColor: "#40a2e3", // Sky Blue
        cancelButtonColor: "#ff4d4f", // Red
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`challenges/${id}/leave`, {
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
                    console.error('Error leaving challenge', usePage().props.flash.message)
                }
            })
        }
    });
}

const viewProgress = async (id, data, index) => {
    try {
        progressLoading.value = index
        // Fetch progress data for the specific challenge
        const response = await axios.get(`/challenges/${id}/progress`);
        progressDetails.value = response.data;
        details.value = data
        console.log('progress', response.data)
        isViewProgressDetails.value = true
        progressLoading.value = null
      } catch (error) {
        progressLoading.value = null
        console.error('Error fetching progress:', error);
      }
}

const confirmDelete = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "Once deleted, this challenge cannot be recovered.",
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/challenges/${id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    console.log('Successfully deleted.', usePage().props.flash.message)
                    Swal.fire({
                        icon: usePage().props.flash.success ? "success" : "error",
                        text: usePage().props.flash.message
                    });
                },
                onError: (error) => {
                    console.error('Error deleting challenge', usePage().props.flash.message)
                }
            })
        }
    });
}

const updateChallenge = (challenge) => {
    isUpdateChallenge.value = true
    details.value = challenge
}

console.log('challenges', props.challenges)

</script>

<template>
    <div class="challenges">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="(challenge, index) in challenges"
                :key="challenge.id"
                :class="{
                    'p-4 border rounded shadow hover:shadow-xl transition hover:cursor-pointer': true,
                    'bg-linen border-dark-gray': challenge.isJoined, // Highlight joined challenges
                    'bg-color-white border-dark-gray': !challenge.isJoined
                }"
                class="relative pb-8"
                @click="viewDetails(challenge)"
            >
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold text-navy-blue">{{ challenge.name }}</h2>
                    <div class="text-sm"  @click.stop>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <i class="fa-solid fa-ellipsis"></i>
                            </template>
                            <template #content>
                                <div
                                    v-if="challenge.user_id == $page.props.auth.user.id"
                                    class="hover:bg-crystal-blue px-3 py-2"
                                    @click="confirmDelete(challenge.id)"
                                >
                                    Remove
                                </div>
                                <div
                                    v-if="challenge.user_id == $page.props.auth.user.id"
                                    class="hover:bg-crystal-blue px-3 py-2"
                                    @click="updateChallenge(challenge)"
                                >
                                    Edit
                                </div>
                                <div
                                    class="hover:bg-crystal-blue px-3 py-2"
                                    @click="leaveChallenge(challenge.id, challenge.name)"
                                >
                                    Leave
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <!-- <p class="text-sm text-gray-500">{{ challenge.description }}</p> -->
                <p class="text-sm mt-1">Points: {{ challenge.points }}</p>
                <p class="text-sm">Start: {{ formatDate(challenge.start_date) }}</p>
                <p class="text-sm">End: {{ formatDate(challenge.end_date) }}</p>

                <!-- Conditionally show buttons or badges -->
                <div v-if="challenge.isJoined" class="mt-4">
                    <button
                        @click.stop="viewProgress(challenge.id, challenge, index)"
                        class="mt-2 bg-crystal-blue text-sm absolute bottom-3 right-3 text-navy-blue py-1 px-3 rounded-full hover:bg-navy-blue hover:text-color-white"
                        >
                        {{ progressLoading == index ? 'Loading...' : 'View Progress' }}
                    </button>
                </div>
                <button
                    v-else
                    @click.stop="joinChallenge(challenge.id, challenge.name)"
                    class="mt-4 absolute bottom-3 right-3 text-sm bg-sky-blue text-color-white py-1 px-3 rounded-full hover:bg-navy-blue"
                >
                    Join Challenge
                </button>
            </div>
        </div>
    </div>

    <Modal :show="isViewDetails" @close="isViewDetails = false">
        <ChallengeDetails @close="isViewDetails = false" :challenge="details"/>
    </Modal>
    <Modal :show="isViewProgressDetails" @close="isViewProgressDetails = false">
        <ProgressDetails @close="isViewProgressDetails = false" :progress="progressDetails" :challenge="details"/>
    </Modal>
    <Modal :show="isUpdateChallenge" @close="isUpdateChallenge = false">
        <NewChallenge @close="isUpdateChallenge = false" :challenge="details"/>
    </Modal>
</template>
  