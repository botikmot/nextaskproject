<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3'
import UserImage from "@/Components/UserImage.vue";
import moment from "moment";
import Swal from 'sweetalert2';

const props = defineProps({
    challenge: Object,
});

const isAddReward = ref(false)

const form = useForm({
    name: '',
    description: '',
    points_required: 0,
});

const addReward = () => {
  form.post(`/challenges/${props.challenge.id}/rewards`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            Swal.fire({
                icon: usePage().props.flash.success ? "success" : "error",
                text: usePage().props.flash.message
            });
            isAddReward.value = false
        },
        onError: (error) => {
            console.error('Error creating reward', usePage().props.flash.message)
        }
    })
}

const formatDate = (date) => {
    return moment(date).format('LL')
}

</script>

<template>
    <div class="challenge-details w-full mx-auto p-6 bg-white">
        <h1 class="text-2xl font-bold text-navy-blue">{{ challenge.name }}</h1>
        <p class="text-sm">{{ challenge.description }}</p>
        <div class="pt-3">
            <div class="text-sm">Start Date: <span class="pl-1 font-bold text-navy-blue">{{ formatDate(challenge.start_date) }}</span></div>
            <div class="text-sm">End Date: <span class="pl-1 font-bold text-navy-blue">{{ formatDate(challenge.end_date) }}</span></div>
            <div class="text-sm">Points: <span class="pl-1 font-bold text-navy-blue">{{ challenge.points }}</span></div>
        </div>
        <hr class="text-dark-gray mt-3" />
        <div class="py-3 w-full">
            <h5 class="font-bold text-xl text-navy-blue">Leaderboard</h5>
            <div class="mt-2">
                <div class="py-2" v-for="participant in challenge.users" :key="participant.id">
                    <div class="flex w-full items-center">
                        <UserImage class="w-8 h-8 rounded-full object-cover mr-2" :user="participant" />
                        <div class="w-full">
                            <div class="flex justify-between">
                                <div class="flex items-center">
                                  <h3 class="text-navy-blue text-sm font-semibold">{{ participant.name }}</h3>
                                  <span class="pl-2 text-xs font-bold text-sky-blue">({{participant.participant_points}} points)</span>
                                </div>
                                <div class="text-navy-blue text-sm font-semibold">{{participant.completion_percentage}}% Progress</div>
                            </div>
                            <div class="w-full h-2 bg-crystal-blue rounded-full mt-1">
                                <div :style="{ width: participant.completion_percentage + '%' }" class="h-full bg-navy-blue rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="text-dark-gray mt-3" />

        <div class="flex mt-3 justify-between">
            <h2 class="text-xl font-semibold">Rewards</h2>
            <div v-if="challenge.user_id == $page.props.auth.user.id"> 
              <button 
                @click="isAddReward = !isAddReward" 
                class="bg-sky-blue text-color-white py-1 px-3 rounded hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
              >
                Add Reward
              </button>
            </div>
        </div>
        
        <ul class="mt-4">
          <li v-for="(reward, index) in challenge.rewards" :key="reward.id" class="mb-2 flex items-center">
              <div class="w-8 h-8 flex justify-center items-center rounded-full bg-navy-blue mr-2">
                <i :class="`${ index == 0 ? 'fa-solid fa-trophy text-gold' : 'fa-solid fa-medal text-silver'} `"></i>
              </div>  
              <div class="">
                  <span class="font-bold text-sky-blue">{{ reward.name }}</span> 
                  <span class="text-sm pl-2">({{ reward.points_required }} points)</span>
              </div>
            <!-- <button 
              @click="redeemReward(reward.id)" 
              class="bg-teal text-xs text-color-white ml-3 px-3 rounded hover:bg-green-600"
            >
              Redeem
            </button> -->
          </li>
        </ul>

        <div v-if="isAddReward" class="mt-6">
          <hr class="text-dark-gray mb-3" />
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700">Reward Name</label>
                <input
                  type="text"
                  id="name"
                  v-model="form.name"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  required
                />
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  rows="2"
                ></textarea>
            </div>
            <div class="mb-4">
                <label for="points" class="block text-sm font-medium text-gray-700">Points for Completion</label>
                <input
                  type="number"
                  id="points"
                  v-model="form.points_required"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  required
                />
            </div>
            <button
                type="button"
                @click="addReward"
                class="w-full bg-sky-blue text-color-white py-2 px-4 rounded-md shadow hover:bg-navy-blue focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Submit Reward
            </button>

        </div>

    </div>
  </template>