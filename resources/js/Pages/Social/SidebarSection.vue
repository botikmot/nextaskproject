<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue';
import UserImage from '@/Components/UserImage.vue';
import axios from 'axios';

const props = defineProps({
    userChangedStatus: Object,
});
const page = usePage();

const trendingTopics = ref([])
const sharedFriends = page.props.sharedFriends || [];

console.log('sharedFriends', sharedFriends)
const loading = ref(false)

const friends = computed(() => {
    return page.props.flash.friends || sharedFriends;
});

watch(
  () => props.userChangedStatus,
  (newValue, oldValue) => {
    if (newValue.user.id) {
        const user = friends.value.find(
                (element) => element.id === newValue.user.id
            );
        if (user) {
            user.status = newValue.status;
        }
    }
  },
  { deep: true }
);

const fetchTopics = async () => {
    try {
        loading.value = true
        // Fetch progress data for the specific challenge
        const response = await axios.get('/trending');
        trendingTopics.value = response.data;
        //details.value = data
        console.log('trending topics', response.data)
        //isViewProgressDetails.value = true
        loading.value = false
      } catch (error) {
        loading.value = false
        console.error('Error fetching progress:', error);
      }
}

onMounted(() => {
    fetchTopics()
});

</script>

<template>
    <div>
        <div v-if="trendingTopics.length > 0">
            <h4 class="text-lg font-bold border-b border-dark-gray text-navy-blue">Trending Topics</h4>
            <ul class="mt-4">
                <li v-for="topic in trendingTopics" :key="topic" class="text-blue-600 mb-1">
                    <a :href="`/hashtag/${topic.name}`" class="text-blue-600 hover:underline">#{{ topic.name }}</a>
                </li>
            </ul>
        </div>
        <div v-if="friends.length > 0" class="mt-6">
            <h4 class="text-lg font-bold border-b border-dark-gray text-navy-blue mb-3">Friends</h4>
            <div
                class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
                v-for="friend in friends"
                :key="friend.id"
            >
                <div class="flex items-center justify-between">
                    <!-- Profile Section -->
                    <div class="flex items-center relative">
                        <div v-if="friend.status == 'online'" class="absolute bg-green w-3 h-3 rounded-full top-0 text-color-white text-xs left-6 border-2 border-color-white"></div>
                        <UserImage class="h-8 w-8 rounded-full object-cover" :user="friend" />
                        <div class="pl-2">
                            <div class="text-navy-blue font-medium text-sm">{{ friend.name }}</div>
                            <div class="text-xs text-gray">Mutual Projects: {{ friend.mutual_projects }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>