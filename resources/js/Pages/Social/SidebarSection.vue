<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue';
import UserImage from '@/Components/UserImage.vue';

const page = usePage();

const trendingTopics = ['#VueJS', '#ProjectManagement', '#Productivity', '#NexTask']
const sharedFriends = page.props.sharedFriends || [];

console.log('sharedFriends', sharedFriends)

const friends = computed(() => {
    return page.props.flash.friends || sharedFriends;
});
</script>

<template>
    <div>
        <h4 class="text-lg font-bold border-b border-dark-gray text-navy-blue">Trending Topics</h4>
        <ul class="mt-4">
            <li v-for="topic in trendingTopics" :key="topic" class="text-blue-600 hover:underline mb-2">{{ topic }}</li>
        </ul>
        <div v-if="friends.length > 0" class="mt-6">
            <h4 class="text-lg font-bold border-b border-dark-gray text-navy-blue mb-3">Friends</h4>
            <div
                class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
                v-for="friend in friends"
                :key="friend.id"
            >
                <div class="flex items-center justify-between">
                    <!-- Profile Section -->
                    <div class="flex items-center space-x-3">
                        <UserImage class="h-8 w-8 rounded-full object-cover" :user="friend" />
                        <div>
                            <div class="text-navy-blue font-medium text-sm">{{ friend.name }}</div>
                            <div class="text-xs text-gray">Mutual Projects: {{ friend.mutual_projects }}</div>
                        </div>
                    </div>
                    <!-- Add Friend Icon with Tooltip -->
                    <!-- <div class="relative group">
                        <button
                            @click="addFriend(friend.id)"
                            class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        >
                            <i class="fas fa-user-plus text-sky-blue text-sm"></i>
                        </button>
                        <div
                            class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap"
                        >
                            Add Friend
                        </div>
                    </div> -->
                </div>
            </div>

        </div>
    </div>
</template>