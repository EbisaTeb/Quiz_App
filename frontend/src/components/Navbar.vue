<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth'; 
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Dropdown from 'primevue/dropdown';
import 'primeicons/primeicons.css';

const authStore = useAuthStore();
const router = useRouter();
const emit = defineEmits(['toggle-sidebar']);

// Dropdown Options
const menuItems = [
  { label: 'Profile', icon: 'pi pi-user', command: () => viewProfile() },
  { label: 'Logout', icon: 'pi pi-sign-out', command: () => logout() }
];

// Functions
function viewProfile() {
  router.push({ name: 'app.profile' });
}

function logout() {
  authStore.logout().then(() => {
    router.push({ name: 'login' });
  });
}

const user = ref({ ...authStore.user });

watch(() => authStore.user, (newUser) => {
    user.value = { ...newUser };
});
</script>

<template>
  <header class="h-14 p-2 flex justify-between items-center shadow bg-white">
    <!-- Toggle Sidebar Button -->
    <Button 
      icon="pi pi-bars" 
      class="p-button-text p-button-rounded" 
      @click="emit('toggle-sidebar')" 
    />

    <!-- User Dropdown -->
    <Dropdown 
      :options="menuItems" 
      optionLabel="label" 
      optionValue="command" 
      placeholder="Select"
      class="w-36"
      @change="(e) => e.value()"
    >
      <template #value>
        <div class="flex items-center">
          <Avatar 
            :image="user.avatar ? `${user.avatar}?${Date.now()}` : 'https://randomuser.me/api/portraits/men/34.jpg'" 
            class="mr-2" 
            shape="circle" 
          />
          <small>{{ user.name }}</small>
          <!-- <i class="pi pi-chevron-down ml-2"></i> -->
        </div>
      </template>
    </Dropdown>
  </header>
</template>
