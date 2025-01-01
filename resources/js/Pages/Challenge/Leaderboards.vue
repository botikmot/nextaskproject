<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import UserImage from '@/Components/UserImage.vue';

const activeSlide = ref(0)

const props = defineProps({
    challenges: Array,
});

const userChallenges = computed(() => {
    return props.challenges
});

const nextSlide = () => {
    if (activeSlide.value < props.challenges.length - 1) {
        activeSlide.value++;
     }
}

const prevSlide = () => {
    if (activeSlide.value > 0) {
        activeSlide.value--;
    }
}

const goToSlide = (index) => {
    activeSlide.value = index;
}



</script>

<template>
    <div class="w-full">
        <div class="bg-navy-blue text-color-white flex justify-center py-1 mb-1 rounded">
            Leaderboards
        </div>
      <!-- Carousel Wrapper -->
      <div class="relative w-full overflow-hidden">
        <!-- Carousel Content -->
        <div 
          class="flex transition-transform duration-300 ease-in-out"
          :style="{ transform: `translateX(-${activeSlide * 100}%)` }"
        >
          <div 
            v-for="challenge in userChallenges" 
            :key="challenge.id" 
            class="flex-shrink-0 w-full"
          >
            <!-- Challenge Card -->
            <div class="bg-color-white p-3 rounded-lg">
              <h5 class="font-bold text-navy-blue">{{ challenge.name }}</h5>
              <div class="mt-2">
                <div class="py-2" v-for="participant in challenge.users" :key="participant.id">
                  <div class="flex w-full items-center">
                    <UserImage class="w-8 h-8 rounded-full object-cover" :user="participant" />
                    <div class="w-full pl-2">
                      <div class="flex justify-between">
                        <div class="flex items-center">
                          <h3 class="text-navy-blue text-sm font-semibold">{{ participant.name }}</h3>
                          <!-- <span class="pl-2 text-xs font-bold text-sky-blue">({{ participant.participant_points }} points)</span> -->
                        </div>
                        <div class="text-navy-blue text-sm font-semibold">{{ participant.completion_percentage }}% Progress</div>
                      </div>
                      <div class="w-full h-2 bg-dark-gray rounded-full mt-1">
                        <div :style="{ width: participant.completion_percentage + '%' }" class="h-full bg-navy-blue rounded-full"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Navigation Arrows -->
        <!-- <button 
          class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-light-gray text-white px-2 py-1 rounded-lg shadow-md"
          @click="prevSlide"
          v-if="activeSlide > 0"
        >
          &lt;
        </button>
        <button 
          class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-light-gray text-white px-2 py-1 rounded-lg shadow-md"
          @click="nextSlide"
          v-if="activeSlide < challenges.length - 1"
        >
          &gt;
        </button> -->
      </div>
  
      <!-- Dots Indicator -->
      <div class="flex justify-center mt-3">
        <span 
          v-for="(challenge, index) in userChallenges" 
          :key="index" 
          :class="`h-2 w-2 mx-1 rounded-full ${activeSlide === index ? 'bg-navy-blue' : 'bg-light-gray'}`"
          @click="goToSlide(index)"
        ></span>
      </div>
    </div>
</template>

<style scoped>
.carousel {
  width: 100%;
  overflow: hidden;
}

.carousel-content {
  display: flex;
  transition: transform 0.3s ease-in-out;
}
</style>