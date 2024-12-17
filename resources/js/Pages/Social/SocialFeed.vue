<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ProfileSection from './ProfileSection.vue';
import FeedSection from './FeedSection.vue';

const props = defineProps({
    posts: Array,
    suggestedFriends: Array,
});

const trendingTopics = ['#VueJS', '#ProjectManagement', '#Productivity', '#NexTask']

</script>

<template>
    <Head title="Social" />

    <AuthenticatedLayout pageTitle="Social">
        <div class="social-page flex flex-col lg:flex-row gap-6 p-6 w-full bg-linen">
            <!-- Profile Section -->
            <div class="profile-section shadow bg-crystal-blue p-6 rounded-lg text-center lg:w-1/4 hidden lg:block">
                <ProfileSection />
            </div>

            <!-- Feed Section -->
            <div class="feed-section w-full lg:w-2/4">
                <FeedSection :posts="posts"/>
            </div>

            <!-- Sidebar Section (Trending Topics) -->
            <div class="sidebar bg-gray-100 p-6 rounded-lg lg:w-1/4 hidden lg:block">
                <h4 class="text-xl font-semibold">Trending Topics</h4>
                <ul class="mt-4">
                    <li v-for="topic in trendingTopics" :key="topic" class="text-blue-600 hover:underline mb-2">{{ topic }}</li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
