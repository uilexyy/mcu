<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-emerald-900 to-slate-800 relative overflow-hidden px-4">
    <!-- Medical pattern background -->
    <div class="absolute inset-0 opacity-[0.04]">
      <div class="absolute inset-0" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10z'/%3E%3Cpath d='M40 20c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM60 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;); background-size: 160px 160px;"></div>
    </div>
    <!-- Animated gradient orbs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl animate-pulse" />
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s" />
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-3xl" />

    <div class="w-full max-w-md relative animate-slide-up">
      <div class="text-center mb-8">
        <img src="/logo.png" alt="RS Juwita" class="h-16 mx-auto mb-2" />
        <p class="text-emerald-200/80 text-sm">MCU System</p>
      </div>
      <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl p-8 ring-1 ring-white/30">
        <router-view v-slot="{ Component }">
          <Transition name="page" mode="out-in">
            <component :is="Component" />
          </Transition>
        </router-view>
      </div>
    </div>
  </div>
</template>
