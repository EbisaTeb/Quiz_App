<script setup>
import { ref, onMounted } from 'vue';
import axiosClient from '@/axios';

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
  <div>
    <h1>Dashboard</h1>
    <div class="statistics">
      <div class="stat-item">
        <h2>Total Users</h2>
        <p>Students: {{ statistics.totalUsers.students }}</p>
        <p>Teachers: {{ statistics.totalUsers.teachers }}</p>
        <p>Admins: {{ statistics.totalUsers.admins }}</p>
      </div>
      <div class="stat-item">
        <h2>Total Quizzes</h2>
        <p>Published: {{ statistics.totalQuizzes.published }}</p>
        <p>Unpublished: {{ statistics.totalQuizzes.unpublished }}</p>
      </div>
      <div class="stat-item">
        <h2>Quiz Attempt Rate</h2>
        <p>Published: {{ statistics.quizAttemptRate.published }}</p>
        <p>Pending: {{ statistics.quizAttemptRate.pending }}</p>
      </div>
      <div class="stat-item">
        <h2>Top Performing Students</h2>
        <ul>
          <li v-for="student in statistics.topPerformingStudents" :key="student.id">
            {{ student.name }} - Average Score: {{ student.submissions.length ? (student.submissions.reduce((sum, submission) => sum + parseFloat(submission.score), 0) / student.submissions.length).toFixed(2) : 'N/A' }}
          </li>
        </ul>
      </div>
      <div class="stat-item">
        <h2>Upcoming Quizzes this</h2>
        <ul>
          <li v-for="quiz in statistics.upcomingQuizzes" :key="quiz.id">
            {{ quiz.title }} - Start Date: {{ quiz.start_time }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.statistics {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}
.stat-item {
  flex: 1 1 45%;
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>