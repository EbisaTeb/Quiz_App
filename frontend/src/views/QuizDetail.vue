<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axiosClient from '@/axios';

const route = useRoute();
const quizId = route.params.id;
const quiz = ref(null);
const isLoading = ref(true);

onMounted(async () => {
  try {
    const response = await axiosClient.get(`/quizzes/${quizId}`);
    quiz.value = response.data;
  } catch (error) {
    console.error('Failed to fetch quiz details', error);
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="quiz-detail">
    <h2>Quiz Details</h2>
    <div v-if="isLoading">Loading...</div>
    <div v-else>
      <h3>{{ quiz.title }}</h3>
      <p><strong>Subject:</strong> {{ quiz.subject.name }}</p>
      <p><strong>Time Limit:</strong> {{ quiz.time_limit }} minutes</p>
      <p><strong>Start Time:</strong> {{ new Date(quiz.start_time).toLocaleString() }}</p>
      <p><strong>End Time:</strong> {{ new Date(quiz.end_time).toLocaleString() }}</p>
      <h4>Questions</h4>
      <div v-for="(question, index) in quiz.questions" :key="question.id" class="question-detail">
        <p><strong>Question {{ index + 1 }}:</strong> {{ question.content }}</p>
        <p><strong>Type:</strong> {{ question.type }}</p>
        <p><strong>Marks:</strong> {{ question.marks }}</p>
        <div v-if="question.type === 'mcq'">
          <p><strong>Options:</strong></p>
          <ul>
            <li v-for="option in question.options" :key="option.id">
              {{ option.content }} <span v-if="option.is_correct">(Correct)</span>
            </li>
          </ul>
        </div>
        <div v-else-if="question.type === 'matching'">
          <p><strong>Matching Pairs:</strong></p>
          <ul>
            <li v-for="pair in question.matching_pairs" :key="pair.id">
              {{ pair.left_value }} - {{ pair.right_value }}
            </li>
          </ul>
        </div>
        <div v-else-if="question.type === 'short_answer'">
          <p><strong>Correct Answer:</strong> {{ question.correct_answer }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-detail {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.quiz-detail h2 {
  text-align: center;
  margin-bottom: 20px;
}

.question-detail {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.question-detail p {
  margin: 5px 0;
}

.question-detail ul {
  list-style-type: none;
  padding: 0;
}

.question-detail ul li {
  background-color: #f1f1f1;
  margin: 5px 0;
  padding: 10px;
  border-radius: 4px;
}
</style>
