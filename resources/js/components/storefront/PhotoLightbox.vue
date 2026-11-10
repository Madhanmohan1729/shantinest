<script setup lang="ts">
import { ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { onKeyStroke, useScrollLock } from '@vueuse/core';
import { computed, nextTick, ref, watch } from 'vue';
import type { PropertyPhoto } from '@/types';

const props = defineProps<{
    photos: PropertyPhoto[];
}>();

const index = defineModel<number | null>({ required: true });

const isOpen = computed(() => index.value !== null && props.photos.length > 0);
const current = computed(() =>
    index.value === null ? null : props.photos[index.value],
);
const thumbnails = ref<HTMLElement | null>(null);
const scrollLock = useScrollLock(
    typeof document === 'undefined' ? null : document.body,
);

watch(isOpen, (open) => (scrollLock.value = open));

watch(index, async (value) => {
    await nextTick();
    thumbnails.value
        ?.querySelector<HTMLElement>(`[data-index="${value}"]`)
        ?.scrollIntoView({
            behavior: 'smooth',
            inline: 'center',
            block: 'nearest',
        });
});

function step(offset: number): void {
    if (index.value === null) {
        return;
    }

    index.value =
        (index.value + offset + props.photos.length) % props.photos.length;
}

onKeyStroke('ArrowRight', () => isOpen.value && step(1));
onKeyStroke('ArrowLeft', () => isOpen.value && step(-1));
onKeyStroke('Escape', () => (index.value = null));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen && current"
                class="fixed inset-0 z-[100] flex flex-col bg-[#0f0d0b]/97 text-ivory backdrop-blur"
                role="dialog"
                aria-modal="true"
                aria-label="Photo viewer"
            >
                <div
                    class="flex items-center justify-between px-5 py-4 sm:px-8"
                >
                    <p class="text-sm text-ivory/60">
                        {{ (index ?? 0) + 1 }} / {{ photos.length }}
                    </p>
                    <button
                        type="button"
                        class="grid size-11 place-items-center rounded-full bg-ivory/10 transition hover:bg-ivory/20"
                        aria-label="Close"
                        @click="index = null"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    class="relative flex min-h-0 flex-1 items-center justify-center px-4 sm:px-20"
                >
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="scale-[0.98] opacity-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-to-class="opacity-0"
                    >
                        <figure
                            :key="current.id"
                            class="flex max-h-full flex-col items-center"
                        >
                            <img
                                :src="current.url"
                                :alt="current.alt"
                                class="max-h-[72vh] w-auto rounded-xl object-contain shadow-2xl"
                            />
                            <figcaption
                                v-if="current.caption"
                                class="mt-4 font-display text-xl text-ivory/90 italic"
                            >
                                {{ current.caption }}
                            </figcaption>
                        </figure>
                    </Transition>

                    <button
                        type="button"
                        class="absolute left-3 grid size-12 place-items-center rounded-full bg-ivory/10 transition hover:bg-ivory/25 sm:left-6"
                        aria-label="Previous photo"
                        @click="step(-1)"
                    >
                        <ChevronLeft class="size-6" />
                    </button>
                    <button
                        type="button"
                        class="absolute right-3 grid size-12 place-items-center rounded-full bg-ivory/10 transition hover:bg-ivory/25 sm:right-6"
                        aria-label="Next photo"
                        @click="step(1)"
                    >
                        <ChevronRight class="size-6" />
                    </button>
                </div>

                <div
                    ref="thumbnails"
                    class="flex [scrollbar-width:none] gap-2 overflow-x-auto px-5 py-5 sm:justify-center sm:px-8"
                >
                    <button
                        v-for="(photo, photoIndex) in photos"
                        :key="photo.id"
                        type="button"
                        :data-index="photoIndex"
                        class="h-16 w-20 shrink-0 overflow-hidden rounded-lg transition"
                        :class="
                            photoIndex === index
                                ? 'opacity-100 ring-2 ring-honey'
                                : 'opacity-45 hover:opacity-80'
                        "
                        :aria-label="`Show photo ${photoIndex + 1}`"
                        @click="index = photoIndex"
                    >
                        <img
                            :src="photo.url"
                            :alt="photo.alt"
                            class="size-full object-cover"
                            loading="lazy"
                        />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
