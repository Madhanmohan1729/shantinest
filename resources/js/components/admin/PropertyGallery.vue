<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, ImagePlus, Star, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import PropertyImageController from '@/actions/App/Http/Controllers/Admin/PropertyImageController';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import type { AdminPhoto, AdminRoom } from '@/types';

const props = defineProps<{
    propertySlug: string;
    images: AdminPhoto[];
    rooms: AdminRoom[];
    maxKilobytes: number;
    maxFiles: number;
    canManage: boolean;
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const upload = useForm({ images: [] as File[] });

function send(files: FileList | File[] | null | undefined): void {
    const images = Array.from(files ?? []).filter((file) =>
        file.type.startsWith('image/'),
    );

    if (images.length === 0) {
        return;
    }

    upload.images = images;
    upload.submit(PropertyImageController.store(props.propertySlug), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            upload.reset();

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

function drop(event: DragEvent): void {
    isDragging.value = false;
    send(event.dataTransfer?.files);
}

function save(
    image: AdminPhoto,
    changes: { caption?: string | null; room_id?: number | null },
): void {
    router.visit(PropertyImageController.update(image.id), {
        data: { caption: image.caption, room_id: image.roomId, ...changes },
        preserveScroll: true,
    });
}

function move(index: number, offset: number): void {
    const ids = props.images.map((image) => image.id);
    const target = index + offset;

    if (target < 0 || target >= ids.length) {
        return;
    }

    [ids[index], ids[target]] = [ids[target], ids[index]];
    router.visit(PropertyImageController.reorder(props.propertySlug), {
        data: { image_ids: ids },
        preserveScroll: true,
    });
}

function remove(image: AdminPhoto): void {
    if (
        confirm(
            `Remove “${image.caption ?? image.fileName}”? The file is deleted.`,
        )
    ) {
        router.visit(PropertyImageController.destroy(image.id), {
            preserveScroll: true,
        });
    }
}

function roomValue(event: Event): number | null {
    const value = (event.target as HTMLSelectElement).value;

    return value === '' ? null : Number(value);
}
</script>

<template>
    <section class="grid gap-5 rounded-2xl border bg-card p-5 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="font-medium">Photos</h2>
                <p class="text-sm text-muted-foreground">
                    The first photo is the cover. Link a photo to a room to show
                    it on that room's card.
                </p>
            </div>
            <span class="text-sm text-muted-foreground"
                >{{ images.length }} photos</span
            >
        </div>

        <label
            v-if="canManage"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-8 text-center transition"
            :class="
                isDragging
                    ? 'border-clay bg-clay/5'
                    : 'hover:border-foreground/30 hover:bg-muted/40'
            "
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="drop"
        >
            <Spinner v-if="upload.processing" class="size-6" />
            <ImagePlus v-else class="size-7 text-muted-foreground" />
            <span class="text-sm font-medium">{{
                upload.processing
                    ? 'Uploading…'
                    : 'Drop photos here or click to choose'
            }}</span>
            <span class="text-xs text-muted-foreground">
                JPG, PNG or WebP · up to
                {{ Math.round(maxKilobytes / 1024) }} MB each ·
                {{ maxFiles }} at a time
            </span>
            <input
                ref="fileInput"
                type="file"
                class="sr-only"
                multiple
                accept="image/jpeg,image/png,image/webp"
                @change="send(($event.target as HTMLInputElement).files)"
            />
        </label>
        <InputError
            :message="upload.errors.images ?? Object.values(upload.errors)[0]"
        />

        <div
            v-if="images.length === 0"
            class="py-8 text-center text-sm text-muted-foreground"
        >
            No photos yet.
        </div>

        <ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <li
                v-for="(image, index) in images"
                :key="image.id"
                class="group overflow-hidden rounded-xl border bg-background"
            >
                <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                    <img
                        :src="image.url"
                        :alt="image.alt"
                        loading="lazy"
                        class="size-full object-cover"
                    />
                    <span
                        v-if="index === 0"
                        class="absolute top-2 left-2 inline-flex items-center gap-1 rounded-full bg-honey px-2.5 py-1 text-xs font-medium text-ink"
                    >
                        <Star class="size-3" /> Cover
                    </span>
                    <div
                        v-if="canManage"
                        class="absolute top-2 right-2 flex gap-1 opacity-100 transition md:opacity-0 md:group-hover:opacity-100"
                    >
                        <button
                            type="button"
                            class="grid size-8 place-items-center rounded-full bg-background/90 shadow disabled:opacity-40"
                            :disabled="index === 0"
                            aria-label="Move earlier"
                            @click="move(index, -1)"
                        >
                            <ArrowLeft class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="grid size-8 place-items-center rounded-full bg-background/90 shadow disabled:opacity-40"
                            :disabled="index === images.length - 1"
                            aria-label="Move later"
                            @click="move(index, 1)"
                        >
                            <ArrowRight class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="grid size-8 place-items-center rounded-full bg-background/90 text-destructive shadow"
                            aria-label="Remove photo"
                            @click="remove(image)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>
                <div class="grid gap-2 p-3">
                    <input
                        :value="image.caption ?? ''"
                        :disabled="!canManage"
                        placeholder="Caption, e.g. Living room"
                        class="h-8 w-full rounded-md border border-transparent bg-transparent px-2 text-sm outline-none hover:border-input focus:border-ring"
                        @change="
                            save(image, {
                                caption:
                                    ($event.target as HTMLInputElement).value ||
                                    null,
                            })
                        "
                    />
                    <select
                        :value="image.roomId ?? ''"
                        :disabled="!canManage"
                        class="h-8 w-full rounded-md border border-input bg-transparent px-2 text-xs text-muted-foreground outline-none dark:bg-input/30"
                        @change="save(image, { room_id: roomValue($event) })"
                    >
                        <option value="">Not linked to a room</option>
                        <option
                            v-for="room in rooms"
                            :key="room.id"
                            :value="room.id"
                        >
                            Shows {{ room.name }}
                        </option>
                    </select>
                </div>
            </li>
        </ul>
    </section>
</template>
