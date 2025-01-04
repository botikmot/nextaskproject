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
import axios from 'axios';

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


</script>

<template>
    <div class="challenges">
        <div class="grid grid-cols-1 lg:grid-cols-1 2xl:grid-cols-2 xl:grid-cols-1 gap-6">
            <div
                v-for="(challenge, index) in challenges"
                :key="challenge.id"
                class="relative bg-color-white p-4 border border-dark-gray rounded shadow hover:shadow-xl transition hover:cursor-pointer"
                @click="viewDetails(challenge)"
            >
                <div class="flex justify-between border-b border-dark-gray pb-2">
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
                <div class="sm:flex block justify-between mt-2">
                    <div>
                        <span class="font-bold text-sky-blue">Start:</span>
                        <span class="pl-1 text-navy-blue">{{ formatDate(challenge.start_date) }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-sky-blue">End:</span>
                        <span class="pl-1 text-navy-blue">{{ formatDate(challenge.end_date) }}</span>
                    </div>
                </div>
                <div>
                    <span class="font-bold text-sky-blue">Points:</span>
                    <span class="pl-1 font-bold text-red-warning">{{ challenge.points }}</span>
                </div>
                <div>
                    <span class="font-bold text-sky-blue">Rewards:</span>
                    <span v-for="reward in challenge.rewards" class="pl-1 text-navy-blue font-bold">{{ reward.name }},</span>
                </div>
                <!-- Conditionally show buttons or badges -->
                <div class="flex mt-1 justify-between items-center">
                    <div>
                        <div class="flex items-center">
                            <div v-for="(participant, index) in challenge.users.slice(0, 5)" :key="participant.id" class="relative -mr-3">
                                <img :src="'/' + participant.profile_image" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-color-white" />
                            </div>
                            
                            <div v-if="challenge.users.length > 5" class="flex items-center text-navy-blue ml-3">
                                <span class="text-lg font-bold">+{{ challenge.users.length - 5 }}</span>
                            </div>
                        </div>
                    </div>
                    <button
                        v-if="challenge.isJoined"
                        @click.stop="viewProgress(challenge.id, challenge, index)"
                        class="bg-crystal-blue text-sm text-navy-blue py-1 px-3 rounded-full hover:bg-navy-blue hover:text-color-white"
                        >
                        {{ progressLoading == index ? 'Loading...' : 'View Progress' }}
                    </button>
                
                    <button
                        v-else
                        @click.stop="joinChallenge(challenge.id, challenge.name)"
                        class="text-sm bg-sky-blue text-color-white py-1 px-3 rounded-full hover:bg-navy-blue"
                    >
                        Join Challenge
                    </button>
                </div>
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
  