<script setup>
import { ref, onMounted, watch } from 'vue';
import axiosClient from '@/axios';
import { useAuthStore } from '@/stores/auth';
import { FilterMatchMode } from '@primevue/core/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import Calendar from 'primevue/calendar';
import InputNumber from 'primevue/inputnumber';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';



const toast = useToast();
const authStore = useAuthStore();
const isLoading = ref(true);
const isSubmitting = ref(false);
const quizzes = ref([]);
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const quizDialog = ref(false);
const deleteDialog = ref(false);
const currentQuiz = ref({
    id: null,
    title: '',
    subject_id: null,
    class_ids: [],
    time_limit: 30,
    start_time: '',
    end_time: ''
});
const subjects = ref([]);
const teacherAssignments = ref([]);
const availableClasses = ref([]);

onMounted(async () => {
    await fetchTeacherAssignments();
    await fetchQuizzes();
});

async function fetchTeacherAssignments() {
    try {
        const response = await axiosClient.get(`/quizzes/teacher-assignments/${authStore.user.id}`);
        teacherAssignments.value = response.data;
        
        // Extract unique subjects
        const seenSubjects = new Set();
        subjects.value = response.data.reduce((acc, assignment) => {
            if (!seenSubjects.has(assignment.subject.id)) {
                seenSubjects.add(assignment.subject.id);
                acc.push(assignment.subject);
            }
            return acc;
        }, []);
    } catch (error) {
        showError('Failed to load teacher assignments');
    }
}

async function fetchQuizzes() {
    try {
        const response = await axiosClient.get('/quizzes');
        quizzes.value = response.data.data; // Adjust based on your pagination structure
    } catch (error) {
        showError('Failed to fetch quizzes');
    } finally {
        isLoading.value = false;
    }
}

watch(() => currentQuiz.value.subject_id, (subjectId) => {
    if (subjectId) {
        const assignmentsForSubject = teacherAssignments.value.filter(ta => ta.subject.id === subjectId);
        const uniqueClasses = [];
        const seenClasses = new Set();
        assignmentsForSubject.forEach(ta => {
            if (!seenClasses.has(ta.class.id)) {
                seenClasses.add(ta.class.id);
                uniqueClasses.push(ta.class);
            }
        });
        availableClasses.value = uniqueClasses;
    } else {
        availableClasses.value = [];
    }
});

function openNew() {
    currentQuiz.value = {
        id: null,
        title: '',
        subject_id: null,
        class_ids: [],
        time_limit: 30,
        start_time: '',
        end_time: ''
    };
    quizDialog.value = true;
}

function editQuiz(quiz) {
    currentQuiz.value = {
        ...quiz,
        subject_id: quiz.subject_id,
        class_ids: [quiz.class_id], // Convert to array for MultiSelect
        start_time: new Date(quiz.start_time),
        end_time: new Date(quiz.end_time)
    };
    quizDialog.value = true;
}

async function saveQuiz() {
    try {
        isSubmitting.value = true;
        const payload = {
            title: currentQuiz.value.title,
            subject_id: currentQuiz.value.subject_id,
            class_id: currentQuiz.value.class_ids,
            time_limit: currentQuiz.value.time_limit,
            start_time: currentQuiz.value.start_time.toISOString(),
            end_time: currentQuiz.value.end_time.toISOString(),
        };

        if (currentQuiz.value.id) {
            await axiosClient.put(`/quizzes/${currentQuiz.value.id}`, payload);
        } else {
            await axiosClient.post('/quizzes', payload);
        }

        await fetchQuizzes();
        quizDialog.value = false;
        showSuccess('Quiz saved successfully');
    } catch (error) {
        handleApiError(error, 'save quiz');
    } finally {
        isSubmitting.value = false;
    }
}

function confirmDelete(quiz) {
    currentQuiz.value = { ...quiz };
    deleteDialog.value = true;
}

async function deleteQuiz() {
    try {
        isSubmitting.value = true;
        await axiosClient.delete(`/quizzes/${currentQuiz.value.id}`);
        await fetchQuizzes();
        deleteDialog.value = false;
        showSuccess('Quiz deleted successfully');
    } catch (error) {
        handleApiError(error, 'delete quiz');
    } finally {
        isSubmitting.value = false;
    }
}

function handleApiError(error, action) {
    const message = error.response?.data?.message || error.message;
    showError(`Failed to ${action}: ${message}`);
}

function showSuccess(message) {
    toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
}

function showError(message) {
    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 5000 });
}
</script>

<template>
    <div>
        <Toast />
        <div class="card">
            <Toolbar class="mb-2">
                <template #start>
                    <Button label="New Quiz" icon="pi pi-plus" @click="openNew" />
                </template>
                <template #end>
                    <IconField iconPosition="left">
                        <InputIcon class="pi pi-search" />
                        <InputText v-model="filters['global'].value" placeholder="Search quizzes..." />
                    </IconField>
                </template>
            </Toolbar>

            <DataTable
                :value="quizzes"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                :loading="isLoading"
            >
                <Column field="title" header="Title" sortable />
                <Column field="subject.name" header="Subject" sortable />
                <Column field="class.name" header="Class" sortable />
                <Column field="time_limit" header="Time Limit (min)" />
                <Column field="start_time" header="Start Time">
                    <template #body="{ data }">
                        {{ new Date(data.start_time).toLocaleString() }}
                    </template>
                </Column>
                <Column field="end_time" header="End Time">
                    <template #body="{ data }">
                        {{ new Date(data.end_time).toLocaleString() }}
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" @click="editQuiz(data)" class="p-button-rounded p-button-text" />
                        <Button icon="pi pi-trash" @click="confirmDelete(data)" class="p-button-rounded p-button-text p-button-danger" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="quizDialog" :style="{ width: '500px' }" :header="currentQuiz.id ? 'Edit Quiz' : 'Create Quiz'" :modal="true">
            <div class="p-fluid grid gap-3">
                <div class="field col-12 ">
                    <label for="title" class="mr-3">Title</label>
                    <InputText id="title" v-model="currentQuiz.title" />
                </div>
                <div class="field col-12">
                    <label for="subject" class="mr-3" >Subject</label>
                    <Dropdown
                        id="subject"
                        v-model="currentQuiz.subject_id"
                        :options="subjects"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Subject"
                    />
                </div>
                <div class="field col-12">
                    <label for="classes" class="mr-3">Classes</label>
                    <MultiSelect
                        id="classes"
                        v-model="currentQuiz.class_ids"
                        :options="availableClasses"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Classes"
                        :disabled="!currentQuiz.subject_id"
                    />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="time_limit" class="mr-3">Time Limit (minutes)</label>
                    <InputNumber id="time_limit" v-model="currentQuiz.time_limit" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="start_time" class="mr-3">Start Time</label>
                    <Calendar id="start_time" v-model="currentQuiz.start_time" showTime />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="end_time" class="mr-3">End Time</label>
                    <Calendar id="end_time" v-model="currentQuiz.end_time" showTime :minDate="currentQuiz.start_time" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" @click="quizDialog = false" class="p-button-text" />
                <Button label="Save" icon="pi pi-check" @click="saveQuiz" :loading="isSubmitting" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteDialog" header="Confirm Delete" :modal="true" :style="{ width: '400px' }">
            <div class="confirmation-content flex align-items-center">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="currentQuiz">Are you sure you want to delete <b>{{ currentQuiz.title }}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" @click="deleteDialog = false" class="p-button-text" />
                <Button label="Yes" icon="pi pi-check" @click="deleteQuiz" class="p-button-danger" :loading="isSubmitting" />
            </template>
        </Dialog>
    </div>
</template>