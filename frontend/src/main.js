import './index.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice'
import App from './App.vue'
import router from './router'
import 'primeicons/primeicons.css';
import Aura from '@primevue/themes/aura';
import 'primevue/resources/themes/saga-blue/theme.css'; // Choose a theme
import 'primevue/resources/primevue.min.css';
import 'primeflex/primeflex.css';

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

