<script setup>
import { ref, onMounted } from 'vue';
import axiosClient from '@/axios';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';

const statistics = ref({
  totalUsers: {},
  totalQuizzes: {},
  quizAttemptRate: {},
  topPerformingStudents: [],
  upcomingQuizzes: [],
});

const fetchStatistics = async () => {
  try {
    const response = await axiosClient.get('/dashboard/statistics');
    statistics.value = response.data;
  } catch (error) {
    console.error('Error fetching statistics:', error);
  }
};

onMounted(() => {
  fetchStatistics();
});
</script>

<template>
  <div class="p-4">
    <h1 class="text-3xl font-bold mb-5">Dashboard Overview</h1>
    
    <div class="grid gap-5">
      <!-- Statistics Row -->
      <div class="col-12 md:col-6 lg:col-4">
        <Card class="shadow-3">
            <template #title>
            <div class="flex align-items-center gap-2">
              <i class="pi pi-users text-primary"></i>
              <span>Total Users</span>
            </div>
            </template>
            <template #content>
            <div class="flex flex-column gap-3">
              <div class="flex justify-content-between items-center">
              <span class="text-lg pr-1">Students:</span>
              <span class="font-bold text-xl text-blue-500">{{ statistics.totalUsers.students }}</span>
              </div>
              <div class="flex justify-content-between items-center">
              <span class="text-lg pr-1">Teachers:</span>
              <span class="font-bold text-xl text-green-500">{{ statistics.totalUsers.teachers }}</span>
              </div>
              <div class="flex justify-content-between items-center">
              <span class="text-lg pr-1">Admins:</span>
              <span class="font-bold text-xl text-red-500">{{ statistics.totalUsers.admins }}</span>
              </div>
            </div>
            </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-4">
        <Card class="shadow-3">
            <template #title>
            <div class="flex align-items-center gap-2">
              <i class="pi pi-file-edit text-blue-500"></i>
              <span>Total Quizzes</span>
            </div>
            </template>
            <template #content>
            <div class="flex flex-column gap-3">
              <div class="flex justify-content-between items-center">
              <span class="text-lg pr-1">Published:</span>
              <span class="font-bold text-xl text-green-500">{{ statistics.totalQuizzes.published }}</span>
              </div>
              <div class="flex justify-content-between items-center">
              <span class="text-lg pr-1">Unpublished:</span>
              <span class="font-bold text-xl text-orange-500">{{ statistics.totalQuizzes.unpublished }}</span>
              </div>
            </div>
            </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-4">
        <Card class="shadow-3">
            <template #title>
            <div class="flex align-items-center gap-2">
              <i class="pi pi-chart-bar text-purple-500"></i>
              <span >Quiz Attempts</span>
            </div>
            </template>
            <template #content>
            <div class="flex flex-column gap-3">
              <div class="flex flex-column gap-2">
              <span class="text-lg font-medium">Completed: {{ statistics.quizAttemptRate.published }}%</span>
              <ProgressBar :value="statistics.quizAttemptRate.published" class="h-1rem bg-purple-200" />
              </div>
              <div class="flex flex-column gap-2">
              <span class="text-lg font-medium">Pending: {{ statistics.quizAttemptRate.pending }}%</span>
              <ProgressBar :value="statistics.quizAttemptRate.pending" class="h-1rem bg-orange-200" />
              </div>
            </div>
            </template>
        </Card>
      </div>

      <!-- Data Tables Row -->
      <div class="col-12 lg:col-6">
        <Card class="shadow-3">
          <template #title>
            <div class="flex align-items-center gap-2">
              <i class="pi pi-star-fill text-yellow-500"></i>
              <span>Top Performing Students</span>
            </div>
          </template>
          <template #content>
            <DataTable :value="statistics.topPerformingStudents" stripedRows class="p-datatable-sm">
              <Column field="name" header="Name" style="width: 60%"></Column>
              <Column header="Average Score" style="width: 40%">
                <template #body="{ data }">
                  <span class="font-medium">
                    {{
                      data.submissions.length ? 
                      (data.submissions.reduce((sum, s) => sum + parseFloat(s.score), 0) / 
                      data.submissions.length).toFixed(2) : 'N/A'
                    }}
                  </span>
                </template>
              </Column>
            </DataTable>
          </template>
        </Card>
      </div>

      <div class="col-12 lg:col-6">
        <Card class="shadow-3">
          <template #title>
            <div class="flex align-items-center gap-2">
              <i class="pi pi-calendar text-green-500"></i>
              <span>Upcoming Quizzes This Week</span>
            </div>
          </template>
          <template #content>
            <DataTable :value="statistics.upcomingQuizzes" stripedRows class="p-datatable-sm">
              <Column field="title" header="Quiz Title" style="width: 60%"></Column>
              <Column field="start_time" header="Start Date" style="width: 40%"></Column>
            </DataTable>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-card-title) {
  font-size: 1.1rem;
  font-weight: 600;
}

:deep(.p-card-content) {
  padding: 1rem 0;
}
</style>