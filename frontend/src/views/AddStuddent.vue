<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import axiosClient from '@/axios';
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
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';

const toast = useToast();
const isLoading = ref(true);
const isSubmitting = ref(false);
const assignments = ref([]);
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const assignmentDialog = ref(false);
const deleteDialog = ref(false);
const currentAssignment = ref({ id: null, student_id: null, class_id: null, subject_ids: [] });
const subjects = ref([]);
const classes = ref([]);
const selectedStudent = ref(null);
const studentSearchResults = ref([]);
const searchingStudents = ref(false);

onMounted(async () => {
    await fetchInitialData();
    await fetchAssignments();
});

async function fetchInitialData() {
    try {
        const scRes = await axiosClient.get('/students/subjects-classes');
        subjects.value = scRes.data.subjects;
        classes.value = scRes.data.classes;
    } catch (error) {
        showError('Failed to load initial data');
    } finally {
        isLoading.value = false;
    }
}

async function fetchAssignments() {
    try {
        const response = await axiosClient.get('/students/classes');
        assignments.value = response.data.map(a => ({
            ...a,
            student: a.student || { name: 'N/A', email: 'N/A' },
            class: a.class || { name: 'N/A' },
            subjects: a.subjects || []
        }));
    } catch (error) {
        showError('Failed to fetch student assignments');
    }
}

async function searchStudents(event) {
    try {
        searchingStudents.value = true;
        const res = await axiosClient.get('/students/search', {
            params: { search: event.value }
        });
        studentSearchResults.value = res.data;
    } catch (error) {
        showError('Failed to search students');
    } finally {
        searchingStudents.value = false;
    }
}

function openNew() {
    currentAssignment.value = { id: null, student_id: null, class_id: null, subject_ids: [] };
    selectedStudent.value = null;
    assignmentDialog.value = true;
}

function editAssignment(assignment) {
    currentAssignment.value = {
        id: assignment.id,
        student_id: assignment.student_id,
        class_id: assignment.class_id,
        subject_ids: Array.isArray(assignment.subjects) ? assignment.subjects.map(s => s.id) : []
    };
    selectedStudent.value = assignment.student;
    assignmentDialog.value = true;
}

async function saveAssignment() {
    try {
        isSubmitting.value = true;
        const payload = {
            student_id: selectedStudent.value.id,
            class_id: currentAssignment.value.class_id,
            subject_id: currentAssignment.value.subject_ids
        };

        if (currentAssignment.value.id) {
            await axiosClient.put(`/students/classes/${currentAssignment.value.id}`, payload);
        } else {
            await axiosClient.post('/students/classes', payload);
        }

        await fetchAssignments();
        assignmentDialog.value = false;
        showSuccess('Assignment saved successfully');
    } catch (error) {
        handleApiError(error, 'save assignment');
    } finally {
        isSubmitting.value = false;
    }
}

function confirmDeleteAssignment(assignment) {
    currentAssignment.value = { ...assignment };
    deleteDialog.value = true;
}

async function deleteAssignment() {
    try {
        isSubmitting.value = true;
        await axiosClient.delete(`/students/classes/${currentAssignment.value.id}`);
        await fetchAssignments();
        deleteDialog.value = false;
        showSuccess('Assignment deleted successfully');
    } catch (error) {
        handleApiError(error, 'delete assignment');
    } finally {
        isSubmitting.value = false;
    }
}

function handleApiError(error, action) {
    if (error.response?.data?.errors) {
        showError(`Validation failed: ${Object.values(error.response.data.errors).join(', ')}`);
    } else {
        showError(`Failed to ${action}: ${error.message}`);
    }
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
        <div class="card">
            <Toast />
            <Toolbar class="mb-2">
                <template #start>
                    <Button label="Assignment Student" icon="pi pi-plus" class="mr-2" @click="openNew" />
                </template>
                <template #end>
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Search assignments..." />
                    </IconField>
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                :value="assignments"
                dataKey="id"
                :loading="isLoading"
                :paginator="true"
                :rows="5"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} assignments"
            >
                <Column field="id" header="ID" :sortable="true" style="min-width: 4rem"></Column>
                <Column field="student.name" header="Student" :sortable="true" style="min-width: 16rem"></Column>
                <Column field="student.email" header="Email" :sortable="true" style="min-width: 12rem"></Column>
                <Column field="class.name" header="Class" :sortable="true" style="min-width: 12rem"></Column>
                <Column field="subject.name" header="Subject" :sortable="true" style="min-width: 12rem"></Column>
                <Column :exportable="false" header="Action"  style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" :loading="isSubmitting" outlined rounded class="mr-2" 
                            @click="editAssignment(slotProps.data)" />
                        <Button icon="pi pi-trash" :loading="isSubmitting" outlined rounded severity="danger" 
                            @click="confirmDeleteAssignment(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="assignmentDialog" :style="{ width: '450px' }" header="Student Assignment" :modal="true">
            <div class="flex flex-col gap-4">
                <div>
                    <label class="block font-bold mb-2">Student</label>
                    <Dropdown 
                        v-model="selectedStudent"
                        :options="studentSearchResults"
                        optionLabel="email"
                        filter
                        placeholder="Search Student"
                        @filter="searchStudents"
                        :loading="searchingStudents"
                    />
                </div>
                <div>
                    <label class="block font-bold mb-2">Class</label>
                    <Dropdown 
                        v-model="currentAssignment.class_id"
                        :options="classes"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Class"
                    />
                </div>
                <div>
                    <label class="block font-bold mb-2">Subjects</label>
                    <MultiSelect 
                        v-model="currentAssignment.subject_ids"
                        :options="subjects"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Subjects"
                        :maxSelectedLabels="3"
                        class="w-full"
                    />
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" :loading="isSubmitting" text 
                    @click="assignmentDialog = false" />
                <Button label="Save" icon="pi pi-check" :loading="isSubmitting" 
                    @click="saveAssignment" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm Deletion" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span>Are you sure you want to delete this assignment?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" :loading="isSubmitting" text 
                    @click="deleteDialog = false" />
                <Button label="Yes" icon="pi pi-check" :loading="isSubmitting" text 
                    @click="deleteAssignment" />
            </template>
        </Dialog>
    </div>
</template>