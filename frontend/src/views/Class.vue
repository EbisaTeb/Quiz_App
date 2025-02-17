<script setup>
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast'; // Import Toast
import { useToast } from 'primevue/usetoast';
import { ClassService } from '@/components/adminSide/ClassService';

const toast = useToast();
const classes = ref([]);
const classDialog = ref(false);
const classForm = ref({});
const isEditMode = ref(false);
const isLoading = ref(true);
const isSumbmiting = ref(false);

const fetchClasses = async () => {
  try {
    classes.value = await ClassService.getClasses();
  } catch (error) {
    console.error('Failed to fetch classes:', error);
  } finally {
    isLoading.value = false;
  }
};

const openNew = () => {
  classForm.value = {};
  isEditMode.value = false;
  classDialog.value = true;
};

const editClass = (classData) => {
  classForm.value = { ...classData };
  isEditMode.value = true;
  classDialog.value = true;
};

const saveClass = async () => {
  try {
    isSumbmiting.value = true;
    if (isEditMode.value) {
      await ClassService.updateClass(classForm.value.id, classForm.value);
      toast.add({ severity: 'success', summary: 'Success', detail: 'Classes edit successfully', life: 3000 });
    } else {
      await ClassService.createClass(classForm.value);
      toast.add({ severity: 'success', summary: 'Success', detail: 'Classes add successfully', life: 3000 });
    }
    classDialog.value = false;
    fetchClasses();
  } catch (error) {
    console.error('Failed to save class:', error);
  }finally{
    isSumbmiting.value = false;
  }
};

const confirmDeleteClass = async (classData) => {
  try {
    isSumbmiting.value = true;
    await ClassService.deleteClass(classData.id);
    toast.add({ severity: 'success', summary: 'Success', detail: 'Classes delete successfully', life: 3000  });
    fetchClasses();
  } catch (error) {
    console.error('Failed to delete class:', error);
  }finally{
    isSumbmiting.value=false;
  }
};

onMounted(() => {
  fetchClasses();
});
</script>

<template>
  <div class="card">
    <Toast />
    <Toolbar class="mb-4">
      <template #start>
        <Button label="Add Class" icon="pi pi-plus" class="mr-2" @click="openNew" />
      </template>
    </Toolbar>

    <DataTable
      ref="dt"
      :value="classes"
      :loading="isLoading"
      dataKey="id"
      :paginator="true"
      :rows="10"
      paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
      :rowsPerPageOptions="[5, 10, 25]"
      currentPageReportTemplate="Showing {first} to {last} of {totalRecords} classes"
    >
      <template #header>
        <div class="flex flex-wrap gap-2 items-center justify-between">
          <h4 class="m-0">Class management</h4>
        </div>
      </template>
      <Column field="name" header="Name" sortable></Column>
      <Column field="grade_level" header="Grade Level" sortable></Column>
      <Column field="year" header="Year" sortable></Column>
      <Column field="students_count" header="Student" sortable></Column>
      <Column field="teachers_count" header="Teacher" sortable></Column>
      <Column :exportable="false" header="Action">
        <template #body="slotProps">
          <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editClass(slotProps.data)" />
          <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteClass(slotProps.data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="classDialog" header="Class Details" :modal="true">
      <div class="flex flex-col gap-4">
        <div>
          <label for="name" class="block">Name</label>
          <InputText id="name" v-model="classForm.name" required />
        </div>
        <div>
          <label for="grade_level" class="block">Grade Level</label>
          <InputText id="grade_level" v-model="classForm.grade_level" required />
        </div>
        <div>
          <label for="year" class="block">Year</label>
          <InputText id="year" v-model="classForm.year" required />
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" :loading="isSumbmiting"  icon="pi pi-times" text @click="hideDialog" />
        <Button label="Save" :loading="isSumbmiting" icon="pi pi-check" @click="saveClass" />
      </template>
    </Dialog>
  </div>
</template>

