// main.js or main.ts
import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App)
app.config.globalProperties.t = t
app.config.globalProperties.n = n

app.mount('#iamdiskbg')
