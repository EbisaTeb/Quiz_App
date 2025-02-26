<template>
    <div>
      <Toast />
      <div class="card">
        <h3>Manage Questions</h3>
        <div class="p-fluid grid">
          <div class="field col-12 md:col-4">
            <label>Select Quiz</label>
            <Select v-model="selectedQuizId" :options="teacherQuizzes" optionLabel="title" optionValue="id"
              placeholder="Select Quiz" class="w-full" />
          </div>
          <div class="field col-12 md:col-2">
            <Button label="Add Question" icon="pi pi-plus" @click="openQuestionDialog" :disabled="!selectedQuizId"
              class="w-full" />
          </div>
        </div>
        
        <!-- Questions Table -->
        <DataTable
          :value="questions"
          v-if="selectedQuizId"
          class="mt-4"
          :paginator="true"
          :rows="5"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          :rowsPerPageOptions="[5, 10, 25]"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} questions"
        >
          <Column field="content" header="Question" />
          <Column field="type" header="Type" />
          <Column field="marks" header="Marks" />
          <Column :exportable="false" header="Action" style="min-width: 12rem">
            <template #body="slotProps">
              <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openQuestionDialog(slotProps.data)" />
              <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteQuestion(slotProps.data)" />
            </template>
          </Column>
        </DataTable>
  
        <!-- Add/Edit Question Dialog -->
        <Dialog v-model:visible="questionDialog" header="Add/Edit Question" :style="{ width: '50vw' }" :modal="true">
          <div class="p-fluid grid gap-3">
            <div class="field col-12">
              <label>Question Type</label>
              <Select v-model="newQuestion.type" :options="questionTypes" optionLabel="label" optionValue="value"
                class="w-full" />
            </div>
  
            <div class="field col-12">
              <label>Question Content</label>
              <InputText v-model="newQuestion.content" class="w-full" />
            </div>
  
            <!-- MCQ Options -->
            <div v-if="newQuestion.type === 'mcq'" class="field col-12">
              <label>Options (Mark correct answers)</label>
              <div v-for="(option, index) in newQuestion.options" :key="index" class="p-inputgroup mb-2">
                <InputText v-model="option.content" placeholder="Option text" class="w-full" />
                <span class="p-inputgroup-addon">
                  <Checkbox v-model="option.is_correct" :binary="true" />
                </span>
                <Button icon="pi pi-times" class="p-button-danger" @click="removeOption(index)" />
              </div>
              <Button icon="pi pi-plus" label="Add Option" @click="addOption" class="p-button-text" />
            </div>
  
            <!-- Matching Pairs -->
            <div v-if="newQuestion.type === 'matching'" class="field col-12">
              <label>Matching Pairs</label>
              <div v-for="(pair, index) in newQuestion.matching_pairs" :key="index" class="p-inputgroup mb-2">
                <InputText v-model="pair.left_value" placeholder="Left item" class="w-full" />
                <InputText v-model="pair.right_value" placeholder="Right match" class="w-full" />
                <Button icon="pi pi-times" class="p-button-danger" @click="removePair(index)" />
              </div>
              <Button icon="pi pi-plus" label="Add Pair" @click="addPair" class="p-button-text" />
            </div>
  
            <!-- Short Answer -->
            <div v-if="newQuestion.type === 'short_answer'" class="field col-12">
              <label>Correct Answer</label>
              <InputText v-model="newQuestion.correct_answer" class="w-full" />
            </div>
  
            <div class="field col-12">
              <label>Marks</label>
              <InputNumber v-model="newQuestion.marks" :min="1" class="w-full" />
            </div>
          </div>
  
          <template #footer>
            <Button label="Cancel" icon="pi pi-times" @click="closeQuestionDialog" class="p-button-text" />
            <Button label="Save and Add New" icon="pi pi-plus" @click="saveQuestion(true)"
              :disabled="!isQuestionFormValid" />
            <Button label="Save" icon="pi pi-check" @click="saveQuestion(false)" :disabled="!isQuestionFormValid" />
          </template>
        </Dialog>

        <!-- Delete Question Dialog -->
        <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
          <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span>Are you sure you want to delete this question?</span>
          </div>
          <template #footer>
            <Button label="No" icon="pi pi-times" text @click="deleteDialog = false" />
            <Button label="Yes" icon="pi pi-check" text @click="deleteQuestion" />
          </template>
        </Dialog>
      </div>
    </div>
  </template>
  
  <script setup>
   import { ref, reactive, computed, onMounted, watch, h } from 'vue';
   import axiosClient from '@/axios';

   import Dialog from 'primevue/dialog';
   import Button from 'primevue/button';
   import DataTable from 'primevue/datatable';
   import Column from 'primevue/column';
    import InputText from 'primevue/inputtext';
    import Toast from 'primevue/toast';
    import { useToast } from 'primevue/usetoast';
    import Select from 'primevue/select';
    import InputNumber from 'primevue/inputnumber';
    import Checkbox  from 'primevue/checkbox';

  
  const toast = useToast();
  const selectedQuizId = ref(null);
  const teacherQuizzes = ref([]);
  const questions = ref([]);
  const questionDialog = ref(false);
  const editingQuestionId = ref(null);
  const deleteDialog = ref(false);
  
  const questionTypes = [
    { label: 'Multiple Choice', value: 'mcq' },
    { label: 'Short Answer', value: 'short_answer' },
    { label: 'Matching', value: 'matching' }
  ];
  
  const defaultQuestion = {
    type: 'mcq',
    content: '',
    options: [],
    matching_pairs: [],
    correct_answer: '',
    marks: 1
  };
  
  const newQuestion = reactive({ ...defaultQuestion });
  
  const isQuestionFormValid = computed(() => {
    if (!newQuestion.content || newQuestion.marks < 1) return false;
    
    switch (newQuestion.type) {
      case 'mcq':
        return newQuestion.options.length >= 2 && 
               newQuestion.options.some(o => o.is_correct) &&
               newQuestion.options.every(o => o.content);
      case 'matching':
        return newQuestion.matching_pairs.length >= 2 &&
               newQuestion.matching_pairs.every(p => p.left_value && p.right_value);
      case 'short_answer':
        return newQuestion.correct_answer.trim().length > 0;
      default:
        return false;
    }
  });
  
  onMounted(async () => {
    try {
      const response = await axiosClient.get('/quiz/teacher-quizzes');
      teacherQuizzes.value = response.data;
    } catch (error) {
      showError('Failed to load quizzes');
    }
  });
  
  watch(selectedQuizId, async (newQuizId) => {
    if (newQuizId) {
      try {
        const response = await axiosClient.get(`/quizzes/${newQuizId}/questions`);
        questions.value = response.data;
      } catch (error) {
        showError('Failed to load questions');
      }
    } else {
      questions.value = [];
    }
  });
  
  function openQuestionDialog(question = null) {
    if (question) {
      Object.assign(newQuestion, question);
      if (newQuestion.type === 'mcq' && !newQuestion.options) {
        newQuestion.options = [];
      }
      if (newQuestion.type === 'matching' && !newQuestion.matching_pairs) {
        newQuestion.matching_pairs = [];
      }
      editingQuestionId.value = question.id;
    } else {
      resetQuestionForm();
      editingQuestionId.value = null;
    }
    questionDialog.value = true;
    setTimeout(() => {
      const firstInput = document.querySelector('input');
      if (firstInput) {
        firstInput.focus();
      }
    }, 100);
  }
  
  function closeQuestionDialog() {
    questionDialog.value = false;
    resetQuestionForm();
  }
  
  function resetQuestionForm() {
    Object.assign(newQuestion, defaultQuestion);
    newQuestion.options = [];
    newQuestion.matching_pairs = [];
  }
  
  function addOption() {
    newQuestion.options.push({ content: '', is_correct: false });
  }
  
  function removeOption(index) {
    newQuestion.options.splice(index, 1);
  }
  
  function addPair() {
    newQuestion.matching_pairs.push({ left_value: '', right_value: '' });
  }
  
  function removePair(index) {
    newQuestion.matching_pairs.splice(index, 1);
  }
  
  async function saveQuestion(addAnother) {
    try {
      const payload = {
        quiz_id: selectedQuizId.value,
        questions: [
          {
            type: newQuestion.type,
            content: newQuestion.content,
            options: newQuestion.type === 'mcq' ? newQuestion.options : [],
            matching_pairs: newQuestion.type === 'matching' ? newQuestion.matching_pairs : [],
            correct_answer: newQuestion.type === 'short_answer' ? newQuestion.correct_answer : '',
            marks: newQuestion.marks
          }
        ]
      };
  
      if (editingQuestionId.value) {
        await axiosClient.put(`/questions/${editingQuestionId.value}`, payload.questions[0]);
        showSuccess('Question updated successfully');
      } else {
        await axiosClient.post('/questions', payload);
        showSuccess('Question added successfully');
      }
  
      if (addAnother) {
        resetQuestionForm();
      } else {
        closeQuestionDialog();
      }
  
      // Refresh questions list
      const response = await axiosClient.get(`/quizzes/${selectedQuizId.value}/questions`);
      questions.value = response.data;
    } catch (error) {
      if (error.response && error.response.status === 422) {
        showError('Validation error: Please check the input fields.');
      } else {
        handleApiError(error, 'save question');
      }
    }
  }
  
  function confirmDeleteQuestion(question) {
    editingQuestionId.value = question.id;
    deleteDialog.value = true;
  }

  async function deleteQuestion() {
    try {
      await axiosClient.delete(`/questions/${editingQuestionId.value}`);
      showSuccess('Question deleted successfully');
      // Refresh questions list
      const response = await axiosClient.get(`/quizzes/${selectedQuizId.value}/questions`);
      questions.value = response.data;
      deleteDialog.value = false;
    } catch (error) {
      handleApiError(error, 'delete question');
    }
  }
  
  function showSuccess(message) {
    toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
  }
  
  function showError(message) {
    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 5000 });
  }
  
  function handleApiError(error, action) {
    const message = error.response?.data?.message || error.message;
    showError(`Failed to ${action}: ${message}`);
  }

  function actionTemplate(rowData) {
    return h('div', [
      h(Button, {
        icon: 'pi pi-pencil',
        class: 'p-button-rounded p-button-success mr-2',
        onClick: () => openQuestionDialog(rowData)
      }),
      h(Button, {
        icon: 'pi pi-trash',
        class: 'p-button-rounded p-button-danger',
        onClick: () => deleteQuestion(rowData.id)
      })
    ]);
  }
  </script>