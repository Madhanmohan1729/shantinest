<script setup lang="ts">
import emblemUrl from '@/assets/brand/santhi-nest-emblem.svg';

/** The Santhi Nest emblem from the official logo, set on a light disc so it reads on any background. */
</script>

<template>
    <span
        class="inline-grid shrink-0 place-items-center overflow-hidden rounded-full bg-white p-[6%] shadow-sm ring-1 ring-black/5"
    >
        <img
            :src="emblemUrl"
            alt=""
            aria-hidden="true"
            class="size-full object-contain"
            draggable="false"
        />
    </span>
</template>
