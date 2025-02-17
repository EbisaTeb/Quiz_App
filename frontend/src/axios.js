import axios from 'axios';
import Cookies from 'js-cookie';
import { useAuthStore } from '@/stores/auth';
const axiosClient = axios.create({
    baseURL: `${import.meta.env.VITE_API_BASE_URL}/api`,
    responseType: 'json',
    headers: {
      'Accept': 'application/json',
      'Content-type': 'application/json'
}
});

// Request interceptor
axiosClient.interceptors.request.use(
  (config) => {
      const token = Cookies.get('access_token');
      if (token) {
          config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
  });
export default axiosClient;