<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axiosClient from '@/axios';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import ProgressSpinner from 'primevue/progressspinner';

const authStore = useAuthStore();
const toast = useToast();
const user = ref({ ...authStore.user });
const passwordDialog = ref(false);
const newPassword = ref('');
const confirmPassword = ref('');
const isUploading = ref(false);

watch(() => authStore.user, (newUser) => {
    user.value = { ...newUser };
});

const showToast = (severity, message) => {
    toast.add({ severity, summary: severity === 'success' ? 'Success' : 'Error', detail: message, life: 3000 });
};

async function updateAvatar(event) {
    if (!event.files || event.files.length === 0) {
        showToast('error', 'Please select a file first');
        return;
    }

    const file = event.files[0];
    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!validTypes.includes(file.type)) {
        showToast('error', 'Invalid file type. Only JPG, PNG, and GIF are allowed.');
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        showToast('error', 'File size too large (max 2MB)');
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        isUploading.value = true;
        const response = await axiosClient.post('/user/avatar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        user.value.avatar = `${response.data.avatar}?${Date.now()}`;
        authStore.user.avatar = user.value.avatar;
        showToast('success', 'Avatar updated successfully');
    } catch (error) {
        showToast('error', 'Failed to update avatar');
    } finally {
        isUploading.value = false;
    }
}

async function changePassword() {
    if (newPassword.value !== confirmPassword.value) {
        showToast('error', 'Passwords do not match');
        return;
    }

    try {
        await axiosClient.post('/user/change-password', {
            password: newPassword.value,
            password_confirmation: confirmPassword.value
        });
        passwordDialog.value = false;
        showToast('success', 'Password changed successfully');
    } catch (error) {
        showToast('error', 'Failed to change password');
    }
}

async function updateName() {
    try {
        const response = await axiosClient.post('/user/update-name', { name: user.value.name });
        authStore.user.name = response.data.name;
        showToast('success', 'Name updated successfully');
    } catch (error) {
        showToast('error', 'Failed to update name');
    }
}
</script>

<template>
    <div class="profile-container">
        <Toast />

        <Card class="w-full ">
            <template #title>Profile</template>

            <template #content>
                <div class="flex space-x-3 mb-4 space-y-3 items-center">
                    <Avatar :image="user.avatar" size="xlarge" shape="circle"  />
                    <div class="">
                        <FileUpload 
                            name="avatar" 
                            accept="image/*"
                            :customUpload="true"
                            :auto="false"
                            @select="updateAvatar"
                            :maxFileSize="2097152"
                            :disabled="isUploading"
                        >
                            <template #empty>
                                <p>Drag & drop your new avatar here</p>
                            </template>
                        </FileUpload>
                        <ProgressSpinner v-if="isUploading" style="width: 30px; height: 30px"/>
                    </div>
                </div>

                <div class="space-x-3 mb-4">
                    <label for="email">Email</label>
                    <InputText id="email" v-model="user.email" disabled class="p-inputtext-disabled" />
                </div>

                <div class="space-y-2 mb-4">
                    <label for="name">Name</label>
                    <div class="w-full space-x-4">
                        <InputText id="name" v-model="user.name" class="name-input" />
                        <Button label="Update Name" icon="pi pi-check" class="p-button-success" @click="updateName" />
                    </div>
                </div>

                   <Button label="Change Password" icon="pi pi-key" class="p-button-warning" @click="passwordDialog = true" />
                
            </template>
        </Card>

        <Dialog v-model:visible="passwordDialog" header="Change Password" :modal="true" :style="{ width: '400px' }">
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <Password id="newPassword" v-model="newPassword" toggleMask class="p-inputtext-lg"/>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <Password id="confirmPassword" v-model="confirmPassword" toggleMask class="p-inputtext-lg"/>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="passwordDialog = false" />
                <Button label="Change" icon="pi pi-check" class="p-button-primary" @click="changePassword" />
            </template>
        </Dialog>
    </div>
</template>

