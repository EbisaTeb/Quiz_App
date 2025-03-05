<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosClient from '@/axios';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { useAuthStore } from '@/stores/auth';
import  InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import Dropdown from 'primevue/dropdown';
interface Quiz {
  id: number;
  time_limit: number;
  questions: Question[];
}

interface Question {
  id: number;
  content: string;
  marks: number;
  type: string;
  options?: Option[];
  matching_pairs?: MatchingPair[];
}

interface Option {
  id: number;
  content: string;
}

interface MatchingPair {
  id: number;
  left_value: string;
  right_value: string;
}

const route = useRoute();
const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();
const quizId = route.params.id as string;
const quiz = ref<Quiz | null>(null);
const isLoading = ref(true);
const isSubmitting = ref(false);
const answers = ref<Record<string, any>>({});
const error = ref<string | null>(null);
const currentQuestionIndex = ref(0);
const timeLeft = ref(0);
const timer = ref<number | null>(null);

const fetchQuiz = async () => {
  try {
    const response = await axiosClient.get(`/student/quizzes/${quizId}`);
    quiz.value = response.data;
    timeLeft.value = quiz.value.time_limit * 60; // Convert minutes to seconds
    startTimer();
  } catch (err) {
    console.error('Error fetching quiz:', err);
    error.value = 'Failed to fetch quiz. Please try again later.';
  } finally {
    isLoading.value = false;
  }
};

const startTimer = () => {
  timer.value = window.setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--;
    } else {
      if (timer.value) clearInterval(timer.value);
      submitQuiz();
    }
  }, 1000);
};

const submitQuiz = async () => {
  isSubmitting.value = true;
  try {
    const response = await axiosClient.post(`/quizzes/${quiz.value!.id}/submit`, {
      quiz_id: quiz.value!.id,
      answers: Object.keys(answers.value).map(questionId => ({
        question_id: questionId,
        student_answer: answers.value[questionId]
      }))
    });
    toast.add({ severity: 'success', summary: 'Success', detail: `Quiz submitted successfully! Your score: ${response.data.score}`, life: 3000 });
    router.push({ name: 'app.activequizzes' });
  } catch (err) {
    console.error('Error submitting quiz:', err);
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to submit quiz', life: 5000 });
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
};

const goNext = () => {
  if (currentQuestionIndex.value < quiz.value!.questions.length - 1) {
    currentQuestionIndex.value++;
  }
};

const formattedTime = computed(() => {
  const minutes = Math.floor(timeLeft.value / 60);
  const seconds = timeLeft.value % 60;
  return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
});

const getAnswer = (questionId: string, pairLeftValue: string) => {
  if (!answers.value[questionId]) {
    answers.value[questionId] = {};
  }
  return answers.value[questionId][pairLeftValue] || '';
};

const setAnswer = (questionId: string, pairLeftValue: string, value: string) => {
  if (!answers.value[questionId]) {
    answers.value[questionId] = {};
  }
  if (typeof answers.value[questionId] === 'string') {
    answers.value[questionId] = {};
  }
  answers.value[questionId][pairLeftValue] = value;
};

onMounted(() => {
  if (authStore.isAuthenticated && authStore.user.roles.some((role: { name: string }) => role.name === 'student')) {
    fetchQuiz();
  } else {
    error.value = 'You need to be logged in as a student to access this quiz.';
  }
});
</script>

<template>
  <div class="p-3">
    <Toast />
    <div class="sticky top-0 bg-gray-100 flex justify-between items-center mb-3 p-3 rounded-lg shadow-md">
      <h1 class="text-2xl font-bold">Time Left</h1>

      <div class="text-xl font-bold">{{ formattedTime }}</div>
    </div>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <div v-if="quiz" class="mb-4 p-4 bg-white rounded shadow">
        <div v-if="quiz.questions.length > 0">
          <div v-for="(question, index) in quiz.questions" :key="question.id" v-show="index === currentQuestionIndex" class="mb-4">
            <h3 class="font-semibold"><strong>{{ index + 1 }}.</strong> {{ question.content }} <strong>({{ question.marks }} marks) </strong></h3>
            <div v-if="question.type === 'mcq'">
              <div v-for="option in question.options" :key="option.id">
  <RadioButton 
    :inputId="`option-${question.id}-${option.id}`" 
    :name="`question-${question.id}`" 
    :value="option.content" 
    v-model="answers[question.id]" 
  />
  <label :for="`option-${question.id}-${option.id}`" class="ml-5">{{ option.content }}</label>
</div>
            </div>
            <div v-else-if="question.type === 'short_answer'">
              <InputText type="text" v-model="answers[question.id]" class="w-full p-2 border rounded" />
            </div>
            <div v-else-if="question.type === 'matching'" class="flex">
  <div class="w-full">
    <div v-for="(pair, index) in question.matching_pairs" :key="pair.id" class="flex items-center mb-2">
      <span class="w-6 text-center">{{ index + 1 }}.</span>
      <Dropdown
  :modelValue="getAnswer(question.id.toString(), pair.left_value)"
  @update:modelValue="(value: string) => setAnswer(question.id.toString(), pair.left_value, value)"
  :options="question.matching_pairs.map((p, idx) => ({
    value: p.right_value,
    label: String.fromCharCode(65 + idx)
  }))"
  optionLabel="label"
  optionValue="value"
  placeholder="Select"
  class="mx-2 max-w-sm"
/>
      <span class="w-1/2 ml-2">{{ pair.left_value }}</span>
      <span class="w-1/2 text-gray-600">
        {{ String.fromCharCode(65 + index) }}. {{ pair.right_value }}
      </span>
    </div>
  </div>
</div>
          </div>
        </div>

        <div class="flex justify-between mt-4">
  <Button label="Back" icon="pi pi-arrow-left"  @click="goBack" class="p-button-secondary" :disabled="currentQuestionIndex === 0" />

  <Button v-if="currentQuestionIndex < quiz.questions.length - 1" label="Next" icon="pi pi-arrow-right" @click="goNext" class="p-button-secondary" />

  <Button v-if="currentQuestionIndex === quiz.questions.length - 1" label="Submit" icon="pi pi-check" @click="submitQuiz"  class="p-button-success" :loading="isSubmitting" />
</div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add your styles here */
</style>
