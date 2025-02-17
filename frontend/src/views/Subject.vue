
<script setup>
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { SubjectService } from '@/components/adminSide/SubjectService';

const isLoading = ref(true);
const isSumbmiting = ref(false);
const subjects = ref([]);
const subjectDialog = ref(false);
const subjectForm = ref({});
const isEditMode = ref(false);
const toast = useToast();

const fetchSubjects = async () => {
    try {
        subjects.value = await SubjectService.getSubjects();
    } catch (error) {
        console.error('Failed to fetch subjects:', error);
    }finally {
    isLoading.value = false;
  }
};

const openNew = () => {
    subjectForm.value = {};
    isEditMode.value = false;
    subjectDialog.value = true;
};

const editSubject = (subjectData) => {
    subjectForm.value = { ...subjectData };
    isEditMode.value = true;
    subjectDialog.value = true;
};

const saveSubject = async () => {
    try {
        isSumbmiting.value = true;
        if (isEditMode.value) {
            await SubjectService.updateSubject(subjectForm.value.id, subjectForm.value);
            toast.add({ severity: 'success', summary: 'Successful', detail: 'Subject Updated', life: 3000 });
        } else {
            await SubjectService.createSubject(subjectForm.value);
            toast.add({ severity: 'success', summary: 'Successful', detail: 'Subject Created', life: 3000 });
        }
        subjectDialog.value = false;
        fetchSubjects();
    } catch (error) {
        console.error('Failed to save subject:', error);
    }finally{
        isSumbmiting.value = false;  
    }

};

const confirmDeleteSubject = async (subjectData) => {
    try {
        isSumbmiting.value = true;
        await SubjectService.deleteSubject(subjectData.id);
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Subject Deleted', life: 3000 });
        fetchSubjects();
    } catch (error) {
        console.error('Failed to delete subject:', error);
    }finally{
        isSumbmiting.value = false;  
    }

};

onMounted(() => {
    fetchSubjects();
});
</script>

<template >
    <div class="flex flex-wrap justify-center items-center">
    <div class="card">
        <Toast />
        <Toolbar class="mb-4">
            <template #start>
                <Button label="Add Subject" icon="pi pi-plus" class="mr-2" @click="openNew" />
            </template>
        </Toolbar>

        <DataTable
            ref="dt"
            :value="subjects"
            :loading="isLoading"
            dataKey="id"
            :paginator="true"
            :rows="10"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[5, 10, 25]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} subjects"
        >
        <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Subject management</h4>
                    </div>
                </template>
            <Column field="name" header="Name" sortable></Column>
            <Column :exportable="false" header="Action">
                <template #body="slotProps">
                    <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSubject(slotProps.data)" />
                    <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSubject(slotProps.data)" />
                </template>
            </Column>
        </DataTable>

        <Dialog v-model:visible="subjectDialog" header="Subject Details" :modal="true">
            <div class="flex flex-col gap-4">
                <div>
                    <label for="name" class="block">Name</label>
                    <InputText id="name" v-model="subjectForm.name" required />
                </div>

                
            </div>

            <template #footer>
                <Button label="Cancel" :loading="isSumbmiting" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" :loading="isSumbmiting" icon="pi pi-check" @click="saveSubject" />
            </template>
        </Dialog>
    </div>
</div>
</template>

