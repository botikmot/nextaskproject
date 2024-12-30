<template>
    <div class="challenge-details w-full mx-auto p-6 bg-white">
      <h1 class="text-2xl font-bold">{{ challenge.name }}</h1>
      <p class="text-sm">{{ challenge.description }}</p>
      <progress-tracker 
        :progress="challenge.progress" 
        :max="100" 
        @update-progress="updateProgress"
      />
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
  
  <script>
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
  </script>
  