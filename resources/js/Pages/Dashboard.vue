<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    userName: {
        type: String,
    },
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout pageTitle="Dashboard">
        <!-- Welcome Message and Summary Snapshot -->
        <div class="flex flex-col space-y-6 w-full">
            <section>
                <div v-if="$page.props.auth.user.is_new">
                    <h1 class="text-2xl font-semibold">Welcome to NexTask, {{ userName }}!</h1>
                    <p>We’re excited to have you on board! Start by setting up your first project, adding tasks, and connecting with your team to unlock productivity.</p>
                </div>
                <div v-else>
                    <h1 class="text-2xl font-semibold">Welcome back, {{ userName }}!</h1>
                    <p>Stay on top of your tasks and connect with your team to accomplish more together.</p>
                </div>
            </section>

            <!-- Welcome Section for New User -->
            <section class="bg-white p-6 shadow rounded mt-4">
                <!-- <h2 class="text-xl font-semibold">Welcome to NexTask, [User's Name]!</h2> -->
                <p class="text-gray-600 mt-2">We're excited to help you get started. Here are some quick steps to set up your workspace:</p>
                <ul class="mt-4 space-y-3">
                    <li>✅ <button class="text-blue-500">Complete Your Profile</button></li>
                    <li>✅ <button class="text-blue-500">Create Your First Project</button></li>
                    <li>✅ <button class="text-blue-500">Invite Team Members</button></li>
                </ul>
                <button class="mt-6 bg-blue-500 text-white py-2 px-4 rounded">Start Tutorial</button>
            </section>

            <!-- Sample Data Panel -->
            <section class="bg-gray-100 p-4 shadow rounded mt-4">
                <h3 class="font-semibold text-lg">Explore a Sample Project</h3>
                <p class="text-gray-600">We've created a sample project to help you familiarize yourself with task management. Feel free to complete or explore it!</p>
                <div class="mt-3 bg-white p-4 rounded shadow">
                    <h4 class="font-semibold">Sample Project: Website Redesign</h4>
                    <ul class="mt-2 space-y-2">
                        <li>📋 Design Homepage - <span class="text-gray-500">Due Nov 10</span></li>
                        <li>📋 Update User Profile - <span class="text-gray-500">Due Nov 12</span></li>
                    </ul>
                </div>
            </section>

            <!-- Suggested Actions Panel -->
            <section class="bg-white p-4 shadow rounded mt-4">
                <h3 class="font-semibold text-lg">Suggested Actions</h3>
                <ul class="mt-3 space-y-3">
                    <li>📨 Invite your team to start collaborating</li>
                    <li>🎯 Set productivity goals</li>
                    <li>📅 Add upcoming meetings to your calendar</li>
                </ul>
            </section>

            <!-- Quick Links and Resources -->
            <section class="bg-white p-4 shadow rounded mt-4">
                <h3 class="font-semibold text-lg">Resources to Help You Get Started</h3>
                <ul class="mt-3 space-y-2">
                    <li><a href="#" class="text-blue-500">Getting Started Guide</a></li>
                    <li><a href="#" class="text-blue-500">FAQ & Support Center</a></li>
                    <li><a href="#" class="text-blue-500">Watch Intro Video</a></li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>


