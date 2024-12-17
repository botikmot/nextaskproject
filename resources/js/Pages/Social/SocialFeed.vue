<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ProfileSection from './ProfileSection.vue';
import FeedSection from './FeedSection.vue';
import SidebarSection from './SidebarSection.vue';

const props = defineProps({
    posts: Array,
    suggestedFriends: Array,
});

console.log('friends', props.friends)
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
                <SidebarSection />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
