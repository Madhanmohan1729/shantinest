<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed, watch } from 'vue';
import RoomHoldController from '@/actions/App/Http/Controllers/Admin/RoomHoldController';
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
import type { HoldDraft } from '@/types';

const props = defineProps<{
    properties: {
        id: number;
        name: string;
        rooms: { id: number; name: string }[];
    }[];
    canManage: boolean;
}>();

const draft = defineModel<HoldDraft | null>({ required: true });

const form = useForm({
    room_id: null as number | null,
    starts_at: '',
    ends_at: '',
    guest_name: '',
    note: '',
});

const isEditing = computed(() => draft.value?.hold != null);

watch(draft, (value) => {
    if (!value) {
        return;
    }

    form.clearErrors();
    form.room_id = value.hold?.roomId ?? value.roomId;
    form.starts_at = value.hold?.startsAt ?? value.startsAt;
    form.ends_at = value.hold?.endsAt ?? value.endsAt;
    form.guest_name = value.hold?.guestName ?? '';
    form.note = value.hold?.note ?? '';
});

function submit(): void {
    const hold = draft.value?.hold;
    const route = hold
        ? RoomHoldController.update(hold.id)
        : RoomHoldController.store();

    form.submit(route, {
        preserveScroll: true,
        onSuccess: () => (draft.value = null),
    });
}

function release(): void {
    const hold = draft.value?.hold;

    if (
        !hold ||
        !confirm('Release this hold? The room can then be requested again.')
    ) {
        return;
    }

    form.submit(RoomHoldController.destroy(hold.id), {
        preserveScroll: true,
        onSuccess: () => (draft.value = null),
    });
}
</script>

<template>
    <Dialog
        :open="draft !== null"
        @update:open="(open) => !open && (draft = null)"
    >
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{
                    isEditing ? 'Edit hold' : 'Hold a room'
                }}</DialogTitle>
                <DialogDescription>
                    A held room is shown as unavailable to guests for this time.
                    <template v-if="draft?.hold?.stayRequestReference">
                        Placed for request
                        {{ draft.hold.stayRequestReference }}.
                    </template>
                </DialogDescription>
            </DialogHeader>

            <form class="grid gap-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="hold-room">Room</Label>
                    <select
                        id="hold-room"
                        v-model="form.room_id"
                        required
                        :disabled="!canManage"
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                    >
                        <optgroup
                            v-for="property in props.properties"
                            :key="property.id"
                            :label="property.name"
                        >
                            <option
                                v-for="room in property.rooms"
                                :key="room.id"
                                :value="room.id"
                            >
                                {{ room.name }}
                            </option>
                        </optgroup>
                    </select>
                    <InputError :message="form.errors.room_id" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="hold-starts">From</Label>
                        <Input
                            id="hold-starts"
                            v-model="form.starts_at"
                            type="datetime-local"
                            required
                            :disabled="!canManage"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="hold-ends">Until</Label>
                        <Input
                            id="hold-ends"
                            v-model="form.ends_at"
                            type="datetime-local"
                            required
                            :disabled="!canManage"
                        />
                    </div>
                </div>
                <InputError
                    :message="form.errors.starts_at ?? form.errors.ends_at"
                />

                <div class="grid gap-2">
                    <Label for="hold-guest">Guest or reason</Label>
                    <Input
                        id="hold-guest"
                        v-model="form.guest_name"
                        placeholder="e.g. Priya Sharma, or Deep cleaning"
                        :disabled="!canManage"
                    />
                    <InputError :message="form.errors.guest_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="hold-note">Note</Label>
                    <textarea
                        id="hold-note"
                        v-model="form.note"
                        rows="2"
                        :disabled="!canManage"
                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                    />
                    <InputError :message="form.errors.note" />
                </div>

                <DialogFooter
                    v-if="canManage"
                    class="mt-2 gap-2 sm:justify-between"
                >
                    <Button
                        v-if="isEditing"
                        type="button"
                        variant="ghost"
                        class="text-destructive"
                        :disabled="form.processing"
                        @click="release"
                    >
                        <Trash2 /> Release
                    </Button>
                    <span v-else />
                    <div class="flex gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="draft = null"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ isEditing ? 'Save hold' : 'Hold room' }}
                        </Button>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
