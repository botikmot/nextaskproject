<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ProfileSection from './ProfileSection.vue';
import FeedSection from './FeedSection.vue';
import SidebarSection from './SidebarSection.vue';
import Test from './Test.vue';

const props = defineProps({
    posts: Object,
    suggestedFriends: Array,
});

console.log('friends', props.friends)
</script>

<template>
    <Head title="Social" />

    <AuthenticatedLayout pageTitle="Social">
        <div class="social-page flex flex-col lg:flex-row gap-6 p-6 w-full bg-linen">
            <!-- Profile Section (Fixed) -->
            <div class="profile-section shadow bg-crystal-blue p-6 rounded-lg text-center lg:w-1/4 hidden lg:block lg:sticky lg:top-6 h-fit">
                <ProfileSection />
            </div>

            <!-- Feed Section (Scrollable) -->
            <!-- <div class="feed-section w-full lg:w-2/4 overflow-y-auto h-screen pr-2"> -->
                <FeedSection :posts="posts" />
                 <!-- <Test :posts="posts"/> -->
            <!-- </div> -->

            <!-- Sidebar Section (Fixed) -->
            <div class="sidebar bg-gray-100 p-6 rounded-lg lg:w-1/4 hidden lg:block lg:sticky lg:top-6 h-fit">
                <SidebarSection />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollable feed section styling */
.feed-section {
    /* Full height with scrolling enabled */
    height: calc(100vh - 124px); /* Adjust for padding or headers */
    overflow-y: auto;
}

/* Hide scrollbars (optional, for better design) */
.feed-section::-webkit-scrollbar {
    width: 8px;
}

.feed-section::-webkit-scrollbar-thumb {
    background-color: #d1d5db; /* Customize scrollbar color */
    border-radius: 4px;
}

.feed-section::-webkit-scrollbar-thumb:hover {
    background-color: #a1a1aa;
}
</style>
