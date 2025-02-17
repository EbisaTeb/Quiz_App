import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axiosClient from '@/axios';
import Cookies from 'js-cookie';
import { useRouter } from 'vue-router';
export const useAuthStore = defineStore('auth', () => {
  const router = useRouter();  
  const state = ref({
    user: null,
    isAuthenticated: false,
    loading: false
  });

  // Getters
  const isAuthenticated = computed(() => state.value.isAuthenticated);
  const user = computed(() => state.value.user);
  const isLoading = computed(() => state.value.loading);

  // Actions
  const login = async (credentials) => {
    try {
      state.value.loading = true;
      const response = await axiosClient.post('/login', credentials);

      if (response.data.token) {
        Cookies.set('access_token', response.data.token, {
          expires: credentials.remember ? 30 : undefined,
          secure: import.meta.env.PROD,
          sameSite: 'Strict'
        });

        state.value.user = response.data.user;
        state.value.isAuthenticated = true;
        
        await router.push({ name: 'app.dashboard' });
        return true;
      }
    } catch (error) {
      console.error('Login error:', error);
      let message = 'Login failed. Please check your credentials.';
      
      if (error.response) {
        message = error.response.data.message || 
          error.response.data.error ||
          message;
      }
      
      throw new Error(message);
    } finally {
      state.value.loading = false;
    }
  };

  const authCheck = async () => {
    try {
      state.value.loading = true;
      const token = Cookies.get('access_token'); // Get token
      if (!token) throw new Error('No token found');
      const response = await axiosClient.get('/user', {
        headers: { Authorization: `Bearer ${token}` }
      });
      state.value.user = response.data;
      state.value.isAuthenticated = true;
    } catch (error) {
      console.error('Auth check error:', error);
      login(); // Logout if token is invalid
    } finally {
      state.value.loading = false;
    }
  };

  const logout = async () => {
    try {
      await axiosClient.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      Cookies.remove('access_token');
      state.value.isAuthenticated = false;
      state.value.user = null;
      await router.push({ name: 'login' });
    }
  };

  return {
    state,
    isAuthenticated,
    user,
    isLoading,
    login,
    authCheck,
    logout
  };
}, {
  persist: {
    paths: ['state.isAuthenticated', 'state.user'],
    storage: sessionStorage
  }
});