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
import  InputIcon  from 'primevue/inputicon';
import IconField from 'primevue/iconfield';


const toast = useToast();
const isLoading = ref(true);
const isSumbmiting = ref(false);
const assignments = ref([]);
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const assignmentDialog = ref(false);
const deleteDialog = ref(false);
const currentAssignment = ref({ id: null, teacher_id: null, subject_id: null, class_id: null });
const subjects = ref([]);
const classes = ref([]);
const selectedTeacher = ref(null);
const teacherSearchResults = ref([]);
const searchingTeachers = ref(false);


onMounted(async () => {
    await fetchInitialData();
    await fetchAssignments();
});

async function fetchInitialData() {
    try {
        const scRes = await axiosClient.get('/teachers/subjects-classes');
        subjects.value = scRes.data.subjects;
        classes.value = scRes.data.classes;
    } catch (error) {
        showError('Failed to load initial data');
    }
    finally{
    isLoading.value = false;
}
}

async function fetchAssignments() {
    try {
        const response = await axiosClient.get('/teachers/1'); // Using dummy ID as per backend implementation
        assignments.value = response.data.map(a => ({
            ...a,
            teacher: a.teacher || { name: 'N/A' },
            subject: a.subject || { name: 'N/A' },
            class: a.class || { name: 'N/A' }
        }));
    } catch (error) {
        showError('Failed to fetch assignments');
    }
    finally{
    isLoading.value = false;
}
}

async function searchTeachers(event) {
    try {
        searchingTeachers.value = true;
        const res = await axiosClient.get('/teachers/search', { 
            params: { search: event.value } 
        });
        teacherSearchResults.value = res.data;
    } catch (error) {
        showError('Failed to search teachers');
    } finally {
        searchingTeachers.value = false;
    }
}

function openNew() {
    currentAssignment.value = { id: null, teacher_id: null, subject_id: null, class_id: null };
    selectedTeacher.value = null;
    assignmentDialog.value = true;
}

function editAssignment(assignment) {
    currentAssignment.value = { 
        id: assignment.id,
        teacher_id: assignment.teacher_id,
        subject_id: assignment.subject.id,
        class_id: assignment.class.id
    };
    selectedTeacher.value = assignment.teacher;
    assignmentDialog.value = true;
}

async function saveAssignment() {
    try {
        isSumbmiting.value = true;
        const payload = {
            teacher_id: selectedTeacher.value.id,
            subject_id: currentAssignment.value.subject_id,
            class_id: currentAssignment.value.class_id
        };

        if (currentAssignment.value.id) {
            await axiosClient.put(`/teachers/assignments/${currentAssignment.value.id}`, payload);
            // toast.add({ severity: 'success', summary: 'Success', detail: 'Assignment updated successfully', life: 3000 });
        } else {
            await axiosClient.post('/teachers/assignments', payload);
            // toast.add({ severity: 'success', summary: 'Success', detail: 'Assignment created successfully', life: 3000 });
        }

        await fetchAssignments();
        assignmentDialog.value = false;
        showSuccess('Assignment saved successfully');
    } catch (error) {
        handleApiError(error, 'save assignment');
    } finally {
        isSumbmiting.value = false;
    }
}

function confirmDeleteAssignment(assignment) {
    currentAssignment.value = { ...assignment };
    deleteDialog.value = true;
}

async function deleteAssignment() {
    try {
        isSumbmiting.value = true;
        await axiosClient.delete(`/teachers/assignments/${currentAssignment.value.id}`);
        await fetchAssignments();
        deleteDialog.value = false;
        showSuccess('Assignment deleted successfully');
        // toast.add({ severity: 'success', summary: 'Success', detail: 'Assignment deleted successfully', life: 3000 });
    } catch (error) {
        handleApiError(error, 'delete assignment');
    }finally{}
    isSumbmiting.value = false;
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
    <div >
        <div class="card">
                <Toast />
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Assignment Teacher" icon="pi pi-plus" class="mr-2" @click="openNew" />
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
                v-model="teacher_subject_class"
                :value="assignments"
                dataKey="id"
                :loading="isLoading"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} assignments"
            >
                <Column field="id" header="ID" :sortable="true" style="min-width: 4rem">           
                </Column>
                <Column field="teacher.name" header="Teacher" :sortable="true" style="min-width: 16rem"></Column>
                <Column field="teacher.email" header="Email" :sortable="true" style="min-width: 12rem"></Column>
                <Column field="subject.name" header="Subject" :sortable="true" style="min-width: 12rem"></Column>
                <Column field="class.name" header="Class" :sortable="true" style="min-width: 12rem"></Column>
                <Column :exportable="false" header="Action" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" :loading="isSumbmiting"   outlined rounded class="mr-2" @click="editAssignment(slotProps.data)" />
                        <Button icon="pi pi-trash" :loading="isSumbmiting"  outlined rounded severity="danger" @click="confirmDeleteAssignment(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="assignmentDialog" :style="{ width: '450px' }" header="Assignment Details" :modal="true">
            <div class="flex flex-col gap-4">
                <div>
                    <label class="block font-bold mb-2">Teacher</label>
                    <Dropdown 
                        v-model="selectedTeacher"
                        :options="teacherSearchResults"
                        optionLabel="email"
                        filter
                        placeholder="Search Teacher"
                        @filter="searchTeachers"
                        :loading="searchingTeachers"
                    />
                </div>
                <div>
                    <label class="block font-bold mb-2">Subject</label>
                    <Dropdown 
                        v-model="currentAssignment.subject_id"
                        :options="subjects"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Subject"
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
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" :loading="isSumbmiting"  text @click="assignmentDialog = false" />
                <Button label="Save" icon="pi pi-check" :loading="isSumbmiting"  @click="saveAssignment" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span>Are you sure you want to delete this assignment?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" :loading="isSumbmiting"  text @click="deleteDialog = false" />
                <Button label="Yes" icon="pi pi-check" :loading="isSumbmiting"  text @click="deleteAssignment" />
            </template>
        </Dialog>
    </div>
</template>

