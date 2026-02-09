// resources/js/Routes.js
import AdminDashboard from '../Pages/AdminDashboard.vue'
import UserDashboard from './Pages/UserDashboard.vue'

export const routes = [
  { path: '/admin', component: AdminDashboard, meta: { role: 'admin' } },
  { path: '/user', component: UserDashboard, meta: { role: 'user' } },
]
