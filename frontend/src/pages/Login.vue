<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'

const auth = useAuth()
const router = useRouter()

const email = ref('demo@example.com')
const password = ref('password')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''

  try {
    await auth.login(email.value, password.value)
    router.push('/markets')
  } catch (e) {
    error.value = 'Invalid login details'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 rounded shadow w-80">
      <h1 class="text-xl font-bold mb-4">Login</h1>

      <div v-if="error" class="text-red-600 mb-2">{{ error }}</div>

      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="border rounded p-2 w-full mb-3"
      />

      <input
        v-model="password"
        type="password"
        placeholder="Password"
        class="border rounded p-2 w-full mb-3"
      />

      <button
        @click="submit"
        class="bg-blue-600 text-white px-4 py-2 w-full rounded"
        :disabled="loading"
      >
        {{ loading ? "Logging in..." : "Login" }}
      </button>
    </div>
  </div>
</template>