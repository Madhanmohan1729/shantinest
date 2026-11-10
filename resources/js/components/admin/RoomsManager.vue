<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { BedDouble, Pencil, Plus, Trash2, Users } from '@lucide/vue';
import { computed, ref } from 'vue';
import RoomController from '@/actions/App/Http/Controllers/Admin/RoomController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { AdminPhoto, AdminRoom } from '@/types';

const props = defineProps<{
    propertySlug: string;
    rooms: AdminRoom[];
    images: AdminPhoto[];
    canManage: boolean;
}>();

const editing = ref<AdminRoom | 'new' | null>(null);
const form = useForm({
    name: '',
    description: '',
    bed_type: '',
    max_guests: 2,
    features: '',
    is_active: true,
});

const isOpen = computed({
    get: () => editing.value !== null,
    set: (open) => !open && (editing.value = null),
});

function photoFor(room: AdminRoom): AdminPhoto | undefined {
    return props.images.find((image) => image.roomId === room.id);
}

function open(room: AdminRoom | 'new'): void {
    form.clearErrors();

    if (room === 'new') {
        form.reset();
    } else {
        form.name = room.name;
        form.description = room.description ?? '';
        form.bed_type = room.bedType ?? '';
        form.max_guests = room.maxGuests;
        form.features = room.features.join('\n');
        form.is_active = room.isActive;
    }

    editing.value = room;
}

function submit(): void {
    const route =
        editing.value === 'new' || editing.value === null
            ? RoomController.store(props.propertySlug)
            : RoomController.update(editing.value.id);

    form.submit(route, {
        preserveScroll: true,
        onSuccess: () => (editing.value = null),
    });
}

function remove(room: AdminRoom): void {
    if (
        confirm(
            `Delete ${room.name}? Its holds are removed too. Photos stay in the gallery.`,
        )
    ) {
        router.visit(RoomController.destroy(room.id), { preserveScroll: true });
    }
}
</script>

<template>
    <div>
        <section class="grid gap-5 rounded-2xl border bg-card p-5 md:p-6">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="font-medium">Rooms</h2>
                    <p class="text-sm text-muted-foreground">
                        Guests pick from these rooms when they request a stay.
                    </p>
                </div>
                <Button v-if="canManage" size="sm" @click="open('new')"
                    ><Plus /> Add room</Button
                >
            </div>

            <div
                v-if="rooms.length === 0"
                class="rounded-xl border border-dashed py-10 text-center text-sm text-muted-foreground"
            >
                No rooms yet. Add the rooms guests can request.
            </div>

            <ul class="grid gap-3 md:grid-cols-2">
                <li
                    v-for="room in rooms"
                    :key="room.id"
                    class="flex gap-4 rounded-xl border p-3"
                >
                    <div
                        class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-muted"
                    >
                        <img
                            v-if="photoFor(room)"
                            :src="photoFor(room)!.url"
                            :alt="room.name"
                            class="size-full object-cover"
                        />
                        <BedDouble
                            v-else
                            class="size-6 text-muted-foreground"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate font-medium">{{ room.name }}</p>
                            <span
                                v-if="!room.isActive"
                                class="rounded bg-muted px-1.5 text-[0.65rem] text-muted-foreground"
                                >Hidden</span
                            >
                        </div>
                        <p
                            class="mt-0.5 flex items-center gap-3 text-xs text-muted-foreground"
                        >
                            <span class="flex items-center gap-1"
                                ><Users class="size-3" />
                                {{ room.maxGuests }}</span
                            >
                            <span v-if="room.bedType">{{ room.bedType }}</span>
                        </p>
                        <p
                            v-if="room.description"
                            class="mt-1 line-clamp-2 text-xs text-muted-foreground"
                        >
                            {{ room.description }}
                        </p>
                    </div>
                    <div v-if="canManage" class="flex shrink-0 flex-col gap-1">
                        <Button
                            size="icon"
                            variant="ghost"
                            aria-label="Edit room"
                            @click="open(room)"
                            ><Pencil
                        /></Button>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="text-destructive"
                            aria-label="Delete room"
                            @click="remove(room)"
                            ><Trash2
                        /></Button>
                    </div>
                </li>
            </ul>
        </section>

        <Dialog v-model:open="isOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{
                        editing === 'new' ? 'Add a room' : 'Edit room'
                    }}</DialogTitle>
                    <DialogDescription
                        >Shown to guests on the property
                        page.</DialogDescription
                    >
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-[1fr_7rem]">
                        <div class="grid gap-2">
                            <Label for="room-name">Name</Label>
                            <Input
                                id="room-name"
                                v-model="form.name"
                                required
                                placeholder="Bedroom 3"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="room-guests">Sleeps</Label>
                            <Input
                                id="room-guests"
                                v-model="form.max_guests"
                                type="number"
                                min="1"
                                required
                            />
                            <InputError :message="form.errors.max_guests" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="room-bed">Bed</Label>
                        <Input
                            id="room-bed"
                            v-model="form.bed_type"
                            placeholder="Queen bed"
                        />
                        <InputError :message="form.errors.bed_type" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="room-description">Description</Label>
                        <textarea
                            id="room-description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="room-features">Features</Label>
                        <textarea
                            id="room-features"
                            v-model="form.features"
                            rows="3"
                            placeholder="One per line, e.g. Air conditioning"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                        />
                        <InputError :message="form.errors.features" />
                    </div>
                    <label class="flex items-center gap-3 text-sm">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="size-4 accent-[var(--color-clay)]"
                        />
                        Guests can request this room
                    </label>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="editing = null"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ editing === 'new' ? 'Add room' : 'Save room' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
