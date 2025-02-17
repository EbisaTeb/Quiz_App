import './index.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice'
import App from './App.vue'
import router from './router'
import 'primeicons/primeicons.css';
import Aura from '@primevue/themes/aura';

const app = createApp(App)

app.use(createPinia())
app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
});

app.use(ToastService);
app.use(router)

app.mount('#app')

