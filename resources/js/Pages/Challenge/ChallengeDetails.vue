<script setup>
import ProgressTracker from "./ProgressTracker.vue";
import UserImage from "@/Components/UserImage.vue";
const props = defineProps({
    challenge: Object,
});

</script>

<template>
    <div class="challenge-details w-full mx-auto p-6 bg-white">
        <h1 class="text-2xl font-bold text-navy-blue">{{ challenge.name }}</h1>
        <p class="text-sm">{{ challenge.description }}</p>
        <div class="py-3 w-full">
            <h5 class="font-bold text-navy-blue">Participants</h5>
            <div class="mt-2">
                <div class="py-2" v-for="participant in challenge.users" :key="participant.id">
                    <div class="flex w-full items-center">
                        <UserImage class="w-8 h-8 rounded-full object-cover mr-2" :user="participant" />
                        <div class="w-full">
                            <div class="flex justify-between">
                                <h3 class="text-navy-blue text-sm font-semibold">{{ participant.name }}</h3>
                                <div class="text-navy-blue text-sm font-semibold">{{participant.pivot.progress}}% Progress</div>
                            </div>
                            <div class="w-full h-2 bg-crystal-blue rounded-full mt-1">
                                <div :style="{ width: participant.pivot.progress + '%' }" class="h-full bg-navy-blue rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      <!-- <progress-tracker 
        :progress="challenge.progress" 
        :max="100" 
        @update-progress="updateProgress"
      /> -->
      <h2 class="mt-6 text-xl font-semibold">Rewards</h2>
      <ul class="mt-4">
        <li v-for="reward in challenge.rewards" :key="reward.id" class="mb-4">
          <p>{{ reward.name }} ({{ reward.points_required }} points)</p>
          <button 
            @click="redeemReward(reward.id)" 
            class="bg-green text-color-white py-1 px-3 rounded hover:bg-green-600"
          >
            Redeem
          </button>
        </li>
      </ul>
    </div>
  </template>
  
  <!-- <script>
  import ProgressTracker from "./ProgressTracker.vue";
  import { Inertia } from "@inertiajs/inertia";
  
  export default {
    props: ["challenge"],
    components: { ProgressTracker },
    methods: {
      updateProgress(newProgress) {
        Inertia.post(`/challenges/${this.challenge.id}/progress`, { progress: newProgress });
      },
      redeemReward(rewardId) {
        Inertia.post(`/rewards/${rewardId}/redeem`, {}, {
          onSuccess: () => alert("Reward redeemed successfully!"),
        });
      },
    },
  };
  </script> -->
  