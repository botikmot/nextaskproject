<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Social" />

    <AuthenticatedLayout pageTitle="Social">
        <!-- Social Feed and Connections -->
        <div class="flex flex-col space-y-6 w-full">
            <!-- Welcome to Social Network Section -->
            <section>
                <h1 class="text-2xl font-semibold">Social Network</h1>
                <p class="text-gray-600">
                    Stay connected with your colleagues, share updates, and engage with your team.
                </p>
            </section>

            <!-- Create New Post Section -->
            <section class="bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold mb-2">Create a New Post</h2>
                <textarea
                    placeholder="Share an update with your team..."
                    class="w-full p-2 border rounded-md mb-3"
                    rows="3"
                ></textarea>
                <button class="bg-blue-500 text-white py-2 px-4 rounded shadow hover:bg-blue-600">
                    Post Update
                </button>
            </section>

            <!-- Feed Section -->
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">Recent Updates</h2>

                <!-- Sample Feed Post -->
                <div class="bg-white p-4 shadow rounded space-y-2">
                    <div class="flex items-center space-x-2">
                        <img
                            src="https://via.placeholder.com/40"
                            alt="User Profile"
                            class="w-10 h-10 rounded-full"
                        />
                        <div>
                            <p class="font-semibold">John Doe</p>
                            <p class="text-sm text-gray-500">Posted 1 hour ago</p>
                        </div>
                    </div>
                    <p class="mt-2">
                        Excited to announce that our team just completed the Alpha phase of Project X!
                    </p>
                    <div class="flex items-center space-x-4 mt-2">
                        <button class="text-blue-500">Like</button>
                        <button class="text-blue-500">Comment</button>
                        <button class="text-blue-500">Share</button>
                    </div>
                </div>

                <!-- Another Sample Feed Post -->
                <div class="bg-white p-4 shadow rounded space-y-2">
                    <div class="flex items-center space-x-2">
                        <img
                            src="https://via.placeholder.com/40"
                            alt="User Profile"
                            class="w-10 h-10 rounded-full"
                        />
                        <div>
                            <p class="font-semibold">Jane Smith</p>
                            <p class="text-sm text-gray-500">Posted 3 hours ago</p>
                        </div>
                    </div>
                    <p class="mt-2">
                        Team meeting scheduled tomorrow at 10:00 AM. Let’s discuss our Q4 goals!
                    </p>
                    <div class="flex items-center space-x-4 mt-2">
                        <button class="text-blue-500">Like</button>
                        <button class="text-blue-500">Comment</button>
                        <button class="text-blue-500">Share</button>
                    </div>
                </div>
            </section>

            <!-- Friend Requests Section -->
            <section class="bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold">Friend Requests</h2>
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center space-x-2">
                        <img
                            src="https://via.placeholder.com/40"
                            alt="User Profile"
                            class="w-10 h-10 rounded-full"
                        />
                        <p class="font-medium">Michael Brown</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="bg-green-500 text-white px-3 py-1 rounded">Accept</button>
                        <button class="bg-red-500 text-white px-3 py-1 rounded">Decline</button>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Additional styles if needed */
</style>
