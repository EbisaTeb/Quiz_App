<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import axiosClient from '@/axios';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import  FileUpload  from 'primevue/fileupload';
import Toolbar from 'primevue/toolbar';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import  InputText  from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast'; // Import Toast


const toast = useToast();
const dt = ref();
const users = ref([]);
const roles = ref([]);
const selectedUsers = ref([]);
const approvedUsersDialog = ref(false);
const filters = ref({
'global': {value: null, matchMode: FilterMatchMode.CONTAINS},});
const isLoading = ref(true);
const isSumbmiting = ref(false);
const findIndexById = (id) => {
    let index = -1;
    for (let i = 0; i < users.value.length; i++) {
        if (users.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};
const createId = () => {
    let id = '';
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ( var i = 0; i < 5; i++ ) {
        id += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return id;
}
const exportCSV = () => {
    dt.value.exportCSV();
};


////////////////////////////////////////////////////////////
const fetchUsers = async () => {
  try {
    const response = await axiosClient.get('/admin/users');
    users.value = response.data.users;
  } catch (error) {
    console.error('Failed to fetch users:', error);
  }finally{
    isLoading.value = false;
  }
 
};

const fetchRoles = async () => {
  try {
    const response = await axiosClient.get('/roles');
    roles.value = response.data;
  } catch (error) {
    console.error('fetch role is faild',error)
  }
 
};

const confirmApprovedUsers = () => {
  approvedUsersDialog.value = true;
};

const approveUser = async (user) => {
  try {
    isSumbmiting.value = true;
  await axiosClient.post(`/users/${user.id}/approve`);
  user.is_approved = true;
  toast.add({ severity: 'success', summary: 'Success', detail: 'User approved', life: 3000 }); 
  } catch (error) {
    console.error('approveUser is fail')
  }finally{
    isSumbmiting.value = false;
  }
};

const approveSelectedUsers = async () => {
  try {
    isSumbmiting.value = true;
    await Promise.all(selectedUsers.value.map(user => axiosClient.post(`/users/${user.id}/approve`)));
  users.value.forEach(user => {
    if (selectedUsers.value.includes(user)) user.is_approved = true;
  });
  approvedUsersDialog.value = false;
  selectedUsers.value = [];
  toast.add({ severity: 'success', summary: 'Success', detail: 'Users approved successfully', life: 3000 });
  } catch (error) {
    console.error('approveUser users fail')
  }finally{
    isSumbmiting.value = false;
  }
};

const updateRole = async (user) => {
  
  try {
    isSumbmiting.value = true;
    await axiosClient.post(`/users/${user.id}/roles`, { role_id: user.selectedRole });
  toast.add({ severity: 'success', summary: 'Success', detail: 'Role updated', life: 3000 });
  } catch (error) {
   console.error('update role fail') 
  }finally{
    isSumbmiting.value = false;
  }
};

onMounted(() => {
    fetchUsers();
    fetchRoles();
});
</script>

<template>  
    <div>
        <div class="card">
          <Toast/>
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Approved" icon="pi pi-verified" severity="success" outlined @click="confirmApprovedUsers" :disabled="!selectedUsers || !selectedUsers.length" />
                </template>

                <template #end>
                    <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" customUpload chooseLabel="Import" class="mr-2" auto :chooseButtonProps="{ severity: 'secondary' }" />
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedUsers"
                :value="users"
                dataKey="id"
                :loading="isLoading"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users"
            >
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage User</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="name" header="Name" sortable style="min-width: 12rem"></Column>
                <Column field="email" header="Email" sortable style="min-width: 16rem"></Column>
                <Column field="is_approved" header="Status" sortable style="min-width: 8rem">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.is_approved ? 'Approved' : 'Pending'" :severity="slotProps.data.is_approved ? 'success' : 'warning'" />
                    </template>
                </Column>
                <Column field="role" header="Role">
                 
                    <template #body="slotProps">
                      <Toast />
                        <Select v-model="slotProps.data.selectedRole" :loading="isSumbmiting" :options="roles" optionValue="id"  optionLabel="name" 
                            class="w-full" @change="updateRole(slotProps.data)" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                      <Toast />
                        <Button v-if="!slotProps.data.is_approved" :loading="isSumbmiting" label="Approve" icon="pi pi-check" class="mr-2"
                            @click="approveUser(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

          
    <Dialog v-model:visible="approvedUsersDialog" :style="{ width: '450px' }" header="Confirm Approval" :modal="true">
      <div class="flex items-center gap-4">
        <i class="pi pi-exclamation-triangle !text-3xl" />
        <span>Are you sure you want to approve the selected users?</span>
      </div>
      <template #footer>
        
        <Button label="No" :loading="isSumbmiting" icon="pi pi-times" text @click="approvedUsersDialog = false" />
        <Button label="Yes"  :loading="isSumbmiting" icon="pi pi-check" @click="approveSelectedUsers" />
      </template>
    </Dialog>
	</div>
</template>


