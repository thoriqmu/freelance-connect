<template>
  <footer class="bg-gray-900 text-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- Top -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        
        <!-- Brand -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">FC</span>
          </div>
          <span class="font-semibold">Freelance Connect</span>
        </div>

        <!-- Simple Links -->
        <div class="flex gap-6 text-sm text-gray-400">
          <a href="#" class="hover:text-blue-400 transition-colors">About</a>
          <a href="#" class="hover:text-blue-400 transition-colors">Contact</a>
          <a href="#" class="hover:text-blue-400 transition-colors">Privacy</a>
        </div>
      </div>

      <!-- Bottom -->
      <div class="border-t border-gray-800 pt-4 flex flex-col md:flex-row items-center justify-between gap-4">
        
        <p class="text-sm text-gray-400">
          &copy; {{ new Date().getFullYear() }} Freelance Connect
        </p>

        <!-- Social -->
        <div class="flex gap-4">
          <!-- LinkedIn -->
          <a href="https://www.linkedin.com/in/thoriq-muchlishin/" target="_blank" class="text-gray-400 hover:text-blue-400 transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6.94 6.5A1.94 1.94 0 115 4.56 1.94 1.94 0 016.94 6.5zM5.25 8.25h3.38V19H5.25zM10.75 8.25h3.24v1.47h.05c.45-.85 1.56-1.74 3.2-1.74 3.42 0 4.05 2.25 4.05 5.18V19h-3.38v-4.84c0-1.15-.02-2.63-1.6-2.63s-1.85 1.25-1.85 2.54V19h-3.38z"/>
            </svg>
          </a>
        </div>

      </div>
    </div>
  </footer>
</template>