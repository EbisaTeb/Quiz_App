<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axiosClient from '@/axios';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
const authStore = useAuthStore();
const toast = useToast();
const user = ref({ ...authStore.user });
const passwordDialog = ref(false);
const newPassword = ref('');
const confirmPassword = ref('');

function showSuccess(message) {
    toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
}

function showError(message) {
    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 5000 });
}

async function updateAvatar(event) {
    const formData = new FormData();
    formData.append('avatar', event.files[0]);

    try {
        const response = await axiosClient.post('/user/avatar', formData);
        user.value.avatar = response.data.avatar;
        showSuccess('Avatar updated successfully');
    } catch (error) {
        showError('Failed to update avatar');
    }
}

async function changePassword() {
    if (newPassword.value !== confirmPassword.value) {
        showError('Passwords do not match');
        return;
    }

    try {
        await axiosClient.post('/user/change-password', {
            password: newPassword.value,
            password_confirmation: confirmPassword.value
        });
        passwordDialog.value = false;
        showSuccess('Password changed successfully');
    } catch (error) {
        showError('Failed to change password');
    }
}

async function updateName() {
    try {
        await axiosClient.post('/user/update-name', { name: user.value.name });
        showSuccess('Name updated successfully');
    } catch (error) {
        showError('Failed to update name');
    }
}
</script>

<template>
    <div>
        <h1>Profile</h1>
        <Toast />
        <div class="p-fluid grid gap-3">
            <div class="field col-12">
                <label for="name">Name</label>
                <InputText id="name" v-model="user.name" />
                <Button label="Update Name" icon="pi pi-check" @click="updateName" />
            </div>
            <div class="field col-12">
                <label for="email">Email</label>
                <InputText id="email" v-model="user.email" disabled />
            </div>
            <div class="field col-12">
                <label for="avatar">Avatar</label>
                <img :src="user.avatar" alt="Avatar" class="profile-avatar" />
                <FileUpload name="avatar" accept="image/*" customUpload :auto="true" @upload="updateAvatar" />
            </div>
            <div class="field col-12">
                <Button label="Change Password" icon="pi pi-key" @click="passwordDialog = true" />
            </div>
        </div>

        <Dialog v-model:visible="passwordDialog" header="Change Password" :modal="true" :style="{ width: '400px' }">
            <div class="p-fluid grid gap-3">
                <div class="field col-12">
                    <label for="newPassword">New Password</label>
                    <Password id="newPassword" v-model="newPassword" toggleMask />
                </div>
                <div class="field col-12">
                    <label for="confirmPassword">Confirm Password</label>
                    <Password id="confirmPassword" v-model="confirmPassword" toggleMask />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" @click="passwordDialog = false" class="p-button-text" />
                <Button label="Change" icon="pi pi-check" @click="changePassword" />
            </template>
        </Dialog>
    </div>
</template>