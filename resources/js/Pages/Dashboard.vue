<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TaskOverview from './Dashboard/TaskOverview.vue';
import ProjectsWidget from './Dashboard/ProjectsWidget.vue';
import UpcomingEvents from './Dashboard/UpcomingEvents.vue';
import SocialHighlights from './Dashboard/SocialHighlights.vue';

const props = defineProps({
    userName: String,
    userRole: String,
    tasks: Object,
    projects: Array,
    events: Array,
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout pageTitle="Dashboard">
        <div class="dashboard flex-col w-full bg-linen p-6">
            <!-- Welcome Banner -->
            <div class="bg-sky-blue text-color-white p-6 rounded-lg shadow-md mb-6">
            <h1 class="text-2xl font-bold">Welcome back, {{ $page.props.auth.user.name }}!</h1>
            <p class="mt-2">Here's a summary of your day:</p>
            <p class="text-sm text-linen">{{ currentDate }}</p>
            </div>

            <!-- Main Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Task Overview -->
                    <TaskOverview :tasks="tasks"/>
                <!-- Project Highlights -->
                    <ProjectsWidget :projects="projects"/>

                <!-- Calendar Preview -->
                    <UpcomingEvents :events="events"/>

                <!-- Social Highlights -->
                <div class="bg-color-white px-6 pt-6 pb-14 relative rounded-lg shadow-md">
                  <SocialHighlights />
                </div>
            </div>

            <!-- Bottom Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Notifications -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-bold text-navy-blue">Notifications</h2>
                <ul class="mt-2">
                <li v-for="notification in notifications" :key="notification.id" class="py-2">
                    <p class="text-sm text-gray-500">{{ notification.message }}</p>
                </li>
                </ul>
            </div>

            <!-- Recent Chats -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-bold text-navy-blue">Recent Chats</h2>
                <ul class="mt-2">
                <li v-for="chat in recentChats" :key="chat.id" class="py-2">
                    <p class="font-semibold">{{ chat.sender }}</p>
                    <p class="text-sm text-gray-500">{{ chat.message }}</p>
                </li>
                </ul>
                <button
                class="mt-4 px-4 py-2 bg-sky-blue text-white rounded-full hover:bg-crystal-blue"
                @click="navigateToChat"
                >
                Open Chat
                </button>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<script>
export default {
  data() {
    return {
      user: { name: "John Doe" },
      currentDate: new Date().toLocaleDateString(),
      tasksDueToday: 2,
      taskCompletionRate: 70,
      activeProjects: [
        { id: 1, title: "Project A", progress: 50 },
        { id: 2, title: "Project B", progress: 80 },
      ],
      upcomingEvents: [
        { id: 1, title: "Team Meeting", date: "2024-12-16" },
        { id: 2, title: "Deadline: Report", date: "2024-12-18" },
      ],
      recentPosts: [
        { id: 1, author: "Jane Smith", content: "Excited for the new release!" },
      ],
      notifications: [
        { id: 1, message: "Task deadline approaching: 'Finish Proposal'" },
      ],
      recentChats: [
        { id: 1, sender: "Alice", message: "Can we discuss this later?" },
      ],
    };
  },
  methods: {
    navigateToTasks() {
      console.log("Navigating to tasks...");
    },
    navigateToProjects() {
      console.log("Navigating to projects...");
    },
    navigateToCalendar() {
      console.log("Navigating to calendar...");
    },
    navigateToSocialPage() {
      console.log("Navigating to social page...");
    },
    navigateToChat() {
      console.log("Navigating to chat...");
    },
  },
};
</script>
