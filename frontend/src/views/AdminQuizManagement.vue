<template>
  <div>
    <Toast />
    <Toolbar class="mb-2">
      <template #start>
        <h1>Admin Quiz Management</h1>
      </template>
      <template #end>
        <IconField>
          <InputIcon>
            <i class="pi pi-search" />
          </InputIcon>
          <InputText v-model="searchQuery" placeholder="Search quizzes..." />
        </IconField>
      </template>
    </Toolbar>
    <DataTable :value="filteredQuizzes" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" responsiveLayout="scroll" :loading="isLoading">
      <Column field="title" header="Title" sortable />
      <Column field="teacher.name" header="Teacher" sortable />
      <Column field="subject.name" header="Subject" sortable />
      <Column field="start_time" header="Start Time" sortable />
      <Column field="end_time" header="End Time" sortable />
      <Column field="time_limit" header="Time Limit" sortable />
      <Column header="Published">
        <template #body="slotProps">
          <span @click="toggleQuizStatus(slotProps.data)" :style="{ cursor: 'pointer', color: slotProps.data.is_published ? 'green' : 'red' }">
            {{ slotProps.data.is_published ? 'Published' : 'Unpublished' }}
          </span>
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axiosClient from '@/axios';
import Toolbar from 'primevue/toolbar';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';

export default {
  components: {
    InputText,
    DataTable,
    Column,
    Toast,
    Toolbar,
    InputIcon,
    IconField
  },
  setup() {
    const toast = useToast();
    const isLoading = ref(true);
    const isSubmitting = ref(false);

    return {
      toast,
      isLoading,
      isSubmitting
    };
  },
  data() {
    return {
      quizzes: [],
      searchQuery: '',
      isAdmin: true // Assume the role is Admin for this example
    };
  },
  created() {
    this.fetchQuizzes();
  },
  computed: {
    filteredQuizzes() {
      return this.quizzes.filter(quiz => 
        quiz.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        quiz.teacher.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        quiz.subject.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  },
  methods: {
    fetchQuizzes() {
      // Fetch quizzes from the backend
      axiosClient.get('/admin/quizzes')
        .then(response => {
          this.quizzes = response.data;
          this.isLoading = false;
        })
        .catch(error => {
          console.error('Error fetching quizzes:', error);
          this.isLoading = false;
        });
    },
    toggleQuizStatus(quiz) {
      // Toggle the quiz status
      quiz.is_published = !quiz.is_published;
      this.updateQuizStatus(quiz);
    },
    updateQuizStatus(quiz) {
      // Logic to update quiz status
      this.isSubmitting = true;
      axiosClient.put(`/admin/quizzes/${quiz.id}/status`, {
        is_published: Boolean(quiz.is_published)
      })
        .then(() => {
          this.fetchQuizzes();
          this.toast.add({ severity: 'success', summary: 'Success', detail: 'Quiz status updated successfully', life: 3000 });
        })
        .catch(error => {
          console.error('Error updating quiz status:', error);
          this.toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update quiz status', life: 5000 });
        })
        .finally(() => {
          this.isSubmitting = false;
        });
    }
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
