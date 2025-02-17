<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Button from 'primevue/button';
import 'primeicons/primeicons.css';

const router = useRouter();

// Functions for Navigation
function addClass() {
  router.push({ name: 'dashboard' });
}

function addSubject() {
  router.push({ name: 'dashboard' });
}
</script>

<template>
  <div class="w-60 text-gray-300">
    <Accordion 
     expandIcon="pi pi-chevron-down" 
     collapseIcon="pi pi-chevron-up">
      <!-- Main Dropdown Item -->
      <AccordionTab header="Resources" class="bg-gray-800 text-white">
        <!-- Add Class Item with Pro Badge -->
        <div class="flex justify-between items-center mb-2 cursor-pointer" @click="addClass">
          <span>Add Class</span>
          <Button label="Pro" class="p-button-rounded p-button-primary text-xs" />
        </div>
        
        <!-- Add Subject Item with Pro Badge -->
        <div class="flex justify-between items-center cursor-pointer" @click="addSubject">
          <span>Add Subject</span>
          <Button label="Pro" class="p-button-rounded p-button-primary text-xs" />
        </div>
      </AccordionTab>
    </Accordion>
  </div>
</template>
