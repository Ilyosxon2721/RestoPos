<template>
  <div class="pos-layout">
    <slot />
  </div>
</template>

<style lang="scss">
.pos-layout {
  height: 100vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
</style>
