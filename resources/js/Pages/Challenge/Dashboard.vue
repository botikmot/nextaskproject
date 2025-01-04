<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import NewChallenge from './NewChallenge.vue';
import Challenges from './Challenges.vue';
import Leaderboards from './Leaderboards.vue';

const isNewChallenge = ref(false)
//const activeMenu = ref('all')
const searchQuery = ref('')

const userchallenges = usePage().props.participantChallenges


const props = defineProps({
    challenges: Array,
});

console.log('userchallenges-->>', props.challenges)
/* const setActiveMenu = (menu) => {
    activeMenu.value = menu;
    if (menu === "all") {
        //this.filteredChallenges = this.challenges;
    } else if (menu === "ongoing") {
        const today = new Date().toISOString().slice(0, 10);
        this.filteredChallenges = this.challenges.filter(
        (challenge) =>
            challenge.start_date <= today && challenge.end_date >= today
        );
    } else if (menu === "completed") {
        const today = new Date().toISOString().slice(0, 10);
        this.filteredChallenges = this.challenges.filter(
        (challenge) => challenge.end_date < today
        );
    }
} */
const columnHeight = computed(() => {
    return 'calc(100vh - 215px)';
});

</script>

<template>
    <Head title="Challenges" />

    <AuthenticatedLayout pageTitle="Challenges">
        <div class="lg:flex block w-full p-6 bg-linen">
            <!-- Sidebar -->
            <div class="lg:w-1/2 2xl:w-1/4 xl:w-1/2 bg-crystal-blue p-4 rounded-lg shadow mr-4 w-full">
                <button
                    @click="isNewChallenge = true"
                    class="block w-full mb-4 py-2 text-linen hover:font-bold bg-sky-blue rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                >
                    Create New Challenge
                </button>
                <div class="">
                    <Leaderboards :challenges="userchallenges"/>
                </div>
            </div>
        
            <!-- Main Content -->
            <main class="flex-1 w-full lg:w-1/2 2xl:w-3/4 xl:w-1/2 sm:p-4 py-4 rounded-lg flex flex-col">
                <header class="main-header">
                <h1 class="font-bold text-navy-blue text-xl pl-3">Challenges Lists</h1>
                <input
                    type="text"
                    class="search-bar"
                    placeholder="Search Challenges..."
                />
                </header>
        
                <section class="challenges-grid">
                    <Challenges :challenges="challenges"/>
                </section>
            </main>

            <Modal :show="isNewChallenge" @close="isNewChallenge = false">
                <NewChallenge @close="isNewChallenge = false"/>
            </Modal>

        </div>
    </AuthenticatedLayout>
  </template>
  
  <style scoped>

  .leaderboards::-webkit-scrollbar {
      width: 8px;
  }

  .leaderboards::-webkit-scrollbar-thumb {
      background-color: #40a2e3; /* Customize scrollbar color */
      border-radius: 4px;
  }

  .leaderboards::-webkit-scrollbar-thumb:hover {
      background-color: #a1a1aa;
  }

  .dashboard-container {
    display: flex;
    min-height: 100vh;
  }
    
  .menu {
    list-style: none;
    padding: 0;
  }
  
  .menu li {
    padding: 10px;
    cursor: pointer;
    transition: background 0.3s;
  }
  
  .menu li.active,
  .menu li:hover {
    /* background: #40a2e3; */
    color: #16325B;
    font-weight: 800;
    font-size: larger;
  }
  
  .main-content {
    flex: 1;
    padding: 20px;
  }
  
  .main-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }
  
  .search-bar {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  
  .challenges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
  }
  
  .challenge-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }
  
  .challenge-card h3 {
    margin: 0 0 10px;
  }
  
  .challenge-card p {
    margin: 0 0 10px;
  }
  
  .challenge-card .dates span {
    display: block;
    font-size: 0.9rem;
    color: #555;
  }
  
  .challenge-card .actions {
    margin-top: 10px;
  }
  
  .btn {
    padding: 8px 12px;
    margin-right: 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    color: white;
  }
  
  .btn-info {
    background: #5bc0de;
  }
  
  .btn-warning {
    background: #f0ad4e;
  }
  
  .btn-danger {
    background: #d9534f;
  }
  </style>
  