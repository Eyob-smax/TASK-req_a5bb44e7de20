<template>
  <div class="dashboard">
    <StudentDashboard  v-if="auth.isStudent && !auth.isAdmin && !auth.isRegistrar && !auth.isTeacher" />
    <TeacherDashboard  v-else-if="auth.isTeacher && !auth.isAdmin" />
    <RegistrarDashboard v-else-if="auth.isRegistrar && !auth.isAdmin" />
    <AdminDashboard    v-else-if="auth.isAdmin" />
    <div v-else class="dashboard-placeholder">
      <p>Welcome, {{ auth.user?.name ?? 'User' }}. Your role-specific dashboard is loading.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import StudentDashboard   from '@/views/dashboard/StudentDashboard.vue'
import TeacherDashboard   from '@/views/dashboard/TeacherDashboard.vue'
import RegistrarDashboard from '@/views/dashboard/RegistrarDashboard.vue'
import AdminDashboard     from '@/views/dashboard/AdminDashboard.vue'

const auth = useAuthStore()
</script>
