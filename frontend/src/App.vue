<template>
  <router-view v-slot="{ Component, route }">
    <Transition name="page" mode="out-in">
      <component :is="Component" :key="route.path" />
    </Transition>
  </router-view>
  <ToastContainer />
</template>

<script setup>
import ToastContainer from './components/ToastContainer.vue'
</script>
