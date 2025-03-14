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

function showSuccess(message) {
    toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
}

function showError(message) {
    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 5000 });
}

async function updateAvatar(event) {
    if (!event.files || event.files.length === 0) {
        showError('Please select a file first');
        return;
    }

    const file = event.files[0];
    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Client-side validation
    if (!validTypes.includes(file.type)) {
        showError('Invalid file type. Only JPG, PNG, and GIF are allowed.');
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        showError('File size too large (max 2MB)');
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        isUploading.value = true;
        const response = await axiosClient.post('/user/avatar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        // Force avatar update with cache buster
        user.value.avatar = `${response.data.avatar}?${Date.now()}`;
        authStore.user.avatar = user.value.avatar; // Update the auth store
        showSuccess('Avatar updated successfully');
    } catch (error) {
        console.error('Upload error:', error);
        let message = 'Failed to update avatar';
        
        if (error.response) {
            message = error.response.data?.message || 
                     error.response.data?.error || 
                     message;
        } else if (error.request) {
            message = 'Network error - please check your connection';
        }

        showError(message);
    } finally {
        isUploading.value = false;
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
        const response = await axiosClient.post('/user/update-name', { name: user.value.name });
        authStore.user.name = response.data.name; // Update the auth store
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
                <label class="pr-1" for="email">Email</label>
                <InputText id="email" v-model="user.email" disabled />
            </div>
            <div class="field col-12">
                <label for="avatar">Avatar</label>
                <div class="flex align-items-center gap-3 mb-2">
                    <img 
                        :src="user.avatar" 
                        alt="Avatar" 
                        class="profile-avatar"
                        style="width: 100px; height: 100px; object-fit: cover"
                    />
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
                            <p>Drag and drop files to here to upload.</p>
                        </template>
                    </FileUpload>
                    <ProgressSpinner v-if="isUploading" style="width: 30px; height: 30px"/>
                </div>
                <div class="field col-12 ">
                <label class="pr-1" for="name">Name</label>
                <InputText class="mr-1"  id="name" v-model="user.name" />
                <Button  label="Update Name" icon="pi pi-check" @click="updateName" />
            </div>
            </div>
                <div class="field col-12">
                 <Button label="Change Password" icon="pi pi-key" @click="passwordDialog = true" />
             </div>
        </div>

        <Dialog v-model:visible="passwordDialog" header="Change Password" :modal="true" :style="{ width: '400px' }">
            <div class="p-fluid grid gap-3">
                <div class="field col-12">
                    <label class="pr-10" for="newPassword">New Password</label>
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