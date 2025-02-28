<script setup>
const {title}=defineProps(
  {
    title:String
  }
)
import Navbar from './Navbar.vue'
import Sidebar from './Sidebar.vue'
import { ref,onMounted,onUnmounted } from 'vue'
const sidebarOpen=ref(true)
function toggleSidebar(){
  sidebarOpen.value=!sidebarOpen.value
}
onMounted(()=>{
  handleSibarOpen();
  window.addEventListener('resize',handleSibarOpen)
})
onUnmounted(()=>{
  window.removeEventListener('resize',handleSibarOpen)
})
function handleSibarOpen(){
  sidebarOpen.value=window.outerWidth>768;
}
</script>
<template >
<div class="flex bg-gray-200 h-screen overflow-hidden">
  <!-- sidebar -->
      <Sidebar :class="{'-ml-[200px]':!sidebarOpen, 'md:ml-0': sidebarOpen}" class="fixed md:relative h-full z-20 border-r border-gray-300"/>
    <!-- sidebar -->
      <div class="flex-1 flex flex-col overflow-hidden">
    <Navbar @toggle-sidebar="toggleSidebar" class="z-20 border-b border-gray-300"/>
       
        <!-- content  -->
        <main  class="p-1 overflow-y-auto mt-3 border-l border-gray-300">
          <div class="p-2 rounded ">
          <router-view></router-view>
          </div>
        </main>
        <!-- content  -->
      </div> 
</div>
</template>