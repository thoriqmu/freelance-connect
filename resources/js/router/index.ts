import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Lazy load views
const LoginView = () => import('@/views/Auth/LoginView.vue')
const RegisterView = () => import('@/views/Auth/RegisterView.vue')

// Client
const ClientDashboardView = () => import('@/views/Client/DashboardView.vue')
const ClientMyProjectsView = () => import('@/views/Client/MyProjectsView.vue')
const ClientCreateProjectView = () => import('@/views/Client/CreateProjectView.vue')
const ClientEditProjectView = () => import('@/views/Client/EditProjectView.vue')
const ClientProjectDetailView = () => import('@/views/Client/ProjectDetailView.vue')
const ClientProfileView = () => import('@/views/Client/ProfileView.vue')

// Freelancer
const FreelancerJobExplorerView = () => import('@/views/Freelancer/JobExplorerView.vue')
const FreelancerSavedJobsView = () => import('@/views/Freelancer/SavedJobsView.vue')
const FreelancerJobDetailView = () => import('@/views/Freelancer/JobDetailView.vue')
const FreelancerMyProposalsView = () => import('@/views/Freelancer/MyProposalsView.vue')
const FreelancerMyJobsView = () => import('@/views/Freelancer/MyJobsView.vue')
const FreelancerMyJobDetailView = () => import('@/views/Freelancer/MyJobDetailView.vue')
const FreelancerProfileView = () => import('@/views/Freelancer/ProfileView.vue')
const FreelancerPublicProfileView = () => import('@/views/Freelancer/PublicProfileView.vue')

const routes: RouteRecordRaw[] = [
  // Public routes
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { layout: 'auth', guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { layout: 'auth', guest: true },
  },

  // Client routes
  {
    path: '/client',
    redirect: '/client/dashboard',
  },
  {
    path: '/client/dashboard',
    name: 'client-dashboard',
    component: ClientDashboardView,
    meta: { requiresAuth: true, role: 'client' },
  },
  {
    path: '/client/projects',
    name: 'client-projects',
    component: ClientMyProjectsView,
    meta: { requiresAuth: true, role: 'client' },
  },
  {
    path: '/client/projects/create',
    name: 'client-project-create',
    component: ClientCreateProjectView,
    meta: { requiresAuth: true, role: 'client' },
  },
  {
    path: '/client/projects/:id/edit',
    name: 'client-project-edit',
    component: ClientEditProjectView,
    meta: { requiresAuth: true, role: 'client' },
  },
  {
    path: '/client/projects/:id',
    name: 'client-project-detail',
    component: ClientProjectDetailView,
    meta: { requiresAuth: true, role: 'client' },
  },
  {
    path: '/client/profile',
    name: 'client-profile',
    component: ClientProfileView,
    meta: { requiresAuth: true, role: 'client' },
  },
  {
    path: '/client/freelancers/:id',
    name: 'client-freelancer-profile',
    component: FreelancerPublicProfileView,
    meta: { requiresAuth: true, role: 'client' },
  },

  // Freelancer routes
  {
    path: '/freelancer',
    redirect: '/freelancer/jobs',
  },
  {
    path: '/freelancer/jobs',
    name: 'freelancer-jobs',
    component: FreelancerJobExplorerView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },
  {
    path: '/freelancer/jobs/:id',
    name: 'freelancer-job-detail',
    component: FreelancerJobDetailView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },
  {
    path: '/freelancer/saved-jobs',
    name: 'freelancer-saved-jobs',
    component: FreelancerSavedJobsView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },
  {
    path: '/freelancer/my-proposals',
    name: 'freelancer-proposals',
    component: FreelancerMyProposalsView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },
  {
    path: '/freelancer/my-jobs',
    name: 'freelancer-my-jobs',
    component: FreelancerMyJobsView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },
  {
    path: '/freelancer/my-jobs/:id',
    name: 'freelancer-my-job-detail',
    component: FreelancerMyJobDetailView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },
  {
    path: '/freelancer/profile',
    name: 'freelancer-profile',
    component: FreelancerProfileView,
    meta: { requiresAuth: true, role: 'freelancer' },
  },

  // Home redirect
  {
    path: '/',
    redirect: (to) => {
      const authStore = useAuthStore()
      if (!authStore.isLoggedIn) {
        return '/login'
      }
      return authStore.isClient ? '/client/dashboard' : '/freelancer/jobs'
    },
  },

  // Catch all - redirect to home
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

declare module 'vue-router' {
  interface RouteMeta {
    layout?: 'default' | 'auth'
    guest?: boolean
    requiresAuth?: boolean
    role?: 'client' | 'freelancer'
  }
}

// Global navigation guard
router.beforeEach(async (to, from) => {
  const authStore = useAuthStore()

  // Auto fetch user on app load if token exists
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchMe()
    } catch (error) {
      console.error('Failed to fetch user:', error)
      authStore.logout()
      return '/login'
    }
  }

  // Redirect guests away from protected routes
  if (to.meta.guest && authStore.isLoggedIn) {
    return authStore.isClient ? '/client/dashboard' : '/freelancer/jobs'
  }

  // Redirect to login if accessing protected route without auth
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return '/login'
  }

  // Role-based access control
  if (to.meta.requiresAuth && to.meta.role && authStore.user?.role !== to.meta.role) {
    return authStore.isClient ? '/client/dashboard' : '/freelancer/jobs'
  }
})

export default router
