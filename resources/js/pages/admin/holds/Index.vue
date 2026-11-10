<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import HoldFormDialog from '@/components/admin/HoldFormDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { addDays, formatDateTime, fromDateKey } from '@/lib/stayDates';
import { index } from '@/routes/admin/holds';
import type { AdminHold, HoldDraft } from '@/types';

type BoardProperty = {
    id: number;
    name: string;
    checkInTime: string;
    checkOutTime: string;
    rooms: {
        id: number;
        name: string;
        isActive: boolean;
        holds: AdminHold[];
    }[];
};

const props = defineProps<{
    properties: BoardProperty[];
    from: string;
    days: number;
    today: string;
    can: { manage: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Room holds', href: index() }],
    },
});

const draft = ref<HoldDraft | null>(null);
const dayKeys = computed(() =>
    Array.from({ length: props.days }, (_, offset) =>
        addDays(props.from, offset),
    ),
);
const windowStart = computed(() => fromDateKey(props.from).getTime());
const windowLength = computed(() => props.days * 86_400_000);
const dayFormatter = new Intl.DateTimeFormat('en-IN', { weekday: 'short' });
const rangeFormatter = new Intl.DateTimeFormat('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

const rangeLabel = computed(
    () =>
        `${rangeFormatter.format(fromDateKey(props.from))} – ${rangeFormatter.format(fromDateKey(addDays(props.from, props.days - 1)))}`,
);

const allHolds = computed(() =>
    props.properties
        .flatMap((property) =>
            property.rooms.flatMap((room) =>
                room.holds.map((hold) => ({
                    ...hold,
                    roomName: room.name,
                    propertyName: property.name,
                })),
            ),
        )
        .sort((a, b) => a.startsAt.localeCompare(b.startsAt)),
);

function toTime(value: string): number {
    const [date, time] = value.split('T');
    const [hours, minutes] = time.split(':').map(Number);
    const parsed = fromDateKey(date);
    parsed.setHours(hours, minutes);

    return parsed.getTime();
}

function barStyle(hold: AdminHold): Record<string, string> {
    const start = Math.max(toTime(hold.startsAt), windowStart.value);
    const end = Math.min(
        toTime(hold.endsAt),
        windowStart.value + windowLength.value,
    );

    return {
        left: `${((start - windowStart.value) / windowLength.value) * 100}%`,
        width: `${Math.max(((end - start) / windowLength.value) * 100, 1.2)}%`,
    };
}

function go(from: string): void {
    router.get(
        index.url({ query: { from } }),
        {},
        { preserveScroll: true, preserveState: true },
    );
}

function newHold(
    property: BoardProperty,
    roomId: number | null,
    date: string,
): void {
    if (!props.can.manage) {
        return;
    }

    draft.value = {
        hold: null,
        roomId: roomId ?? property.rooms[0]?.id ?? null,
        startsAt: `${date}T${property.checkInTime}`,
        endsAt: `${addDays(date, 1)}T${property.checkOutTime}`,
    };
}

function editHold(hold: AdminHold): void {
    draft.value = {
        hold,
        roomId: hold.roomId,
        startsAt: hold.startsAt,
        endsAt: hold.endsAt,
    };
}
</script>

<template>
    <Head title="Room holds" />

    <div class="flex min-w-0 flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl">Room holds</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Click an empty day to hold a room for any hours or days.
                    Click a hold to change or release it.
                </p>
            </div>
            <Button
                v-if="can.manage && properties.length"
                @click="
                    newHold(properties[0], null, from < today ? today : from)
                "
            >
                <Plus /> New hold
            </Button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                variant="outline"
                size="icon"
                aria-label="Previous two weeks"
                @click="go(addDays(from, -days))"
            >
                <ChevronLeft />
            </Button>
            <Button
                variant="outline"
                size="icon"
                aria-label="Next two weeks"
                @click="go(addDays(from, days))"
            >
                <ChevronRight />
            </Button>
            <Button variant="outline" @click="go(today)">Today</Button>
            <Input
                type="date"
                class="w-auto"
                :model-value="from"
                @update:model-value="(value) => value && go(String(value))"
            />
            <span class="ml-2 font-medium">{{ rangeLabel }}</span>
        </div>

        <div
            v-if="properties.length === 0"
            class="rounded-2xl border border-dashed p-16 text-center text-muted-foreground"
        >
            Add a property with rooms first.
        </div>

        <div
            v-for="property in properties"
            :key="property.id"
            class="overflow-hidden rounded-2xl border bg-card"
        >
            <div class="border-b px-5 py-3 font-medium">
                {{ property.name }}
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-[56rem]">
                    <div class="grid grid-cols-[10rem_1fr] border-b text-xs">
                        <div class="px-4 py-2 text-muted-foreground">Room</div>
                        <div
                            class="grid"
                            :style="{
                                gridTemplateColumns: `repeat(${days}, minmax(0, 1fr))`,
                            }"
                        >
                            <div
                                v-for="day in dayKeys"
                                :key="day"
                                class="border-l px-1 py-2 text-center"
                                :class="
                                    day === today
                                        ? 'bg-clay/10 font-semibold text-clay-deep dark:text-honey'
                                        : 'text-muted-foreground'
                                "
                            >
                                <div>
                                    {{ dayFormatter.format(fromDateKey(day)) }}
                                </div>
                                <div class="text-sm text-foreground">
                                    {{ Number(day.slice(8)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="room in property.rooms"
                        :key="room.id"
                        class="grid grid-cols-[10rem_1fr] border-b last:border-b-0"
                    >
                        <div class="flex items-center gap-2 px-4 py-3 text-sm">
                            <span class="truncate font-medium">{{
                                room.name
                            }}</span>
                            <span
                                v-if="!room.isActive"
                                class="rounded bg-muted px-1.5 text-[0.65rem] text-muted-foreground"
                                >Hidden</span
                            >
                        </div>
                        <div class="relative h-16">
                            <div
                                class="absolute inset-0 grid"
                                :style="{
                                    gridTemplateColumns: `repeat(${days}, minmax(0, 1fr))`,
                                }"
                            >
                                <button
                                    v-for="day in dayKeys"
                                    :key="day"
                                    type="button"
                                    class="border-l transition"
                                    :class="[
                                        day === today ? 'bg-clay/5' : '',
                                        can.manage
                                            ? 'hover:bg-muted'
                                            : 'cursor-default',
                                    ]"
                                    :aria-label="`Hold ${room.name} from ${day}`"
                                    @click="newHold(property, room.id, day)"
                                />
                            </div>
                            <button
                                v-for="hold in room.holds"
                                :key="hold.id"
                                type="button"
                                class="absolute top-2.5 bottom-2.5 overflow-hidden rounded-lg px-2.5 text-left text-xs shadow-sm transition hover:brightness-110"
                                :class="
                                    hold.stayRequestReference
                                        ? 'bg-gradient-to-r from-clay to-clay-deep text-ivory'
                                        : 'bg-sage text-ivory'
                                "
                                :style="barStyle(hold)"
                                :title="`${hold.guestName ?? 'Held'}: ${formatDateTime(hold.startsAt)} → ${formatDateTime(hold.endsAt)}`"
                                @click="editHold(hold)"
                            >
                                <span class="block truncate font-medium">{{
                                    hold.guestName ?? 'Held'
                                }}</span>
                                <span class="block truncate opacity-80">{{
                                    formatDateTime(hold.startsAt)
                                }}</span>
                            </button>
                        </div>
                    </div>
                    <p
                        v-if="property.rooms.length === 0"
                        class="px-5 py-6 text-sm text-muted-foreground"
                    >
                        This property has no rooms yet.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 text-xs text-muted-foreground">
            <span class="flex items-center gap-2"
                ><span class="size-3 rounded bg-clay" /> From an approved stay
                request</span
            >
            <span class="flex items-center gap-2"
                ><span class="size-3 rounded bg-sage" /> Placed by an
                admin</span
            >
        </div>

        <section v-if="allHolds.length" class="rounded-2xl border bg-card">
            <h2 class="border-b px-5 py-3 font-medium">Holds in this view</h2>
            <ul class="divide-y">
                <li v-for="hold in allHolds" :key="hold.id">
                    <button
                        type="button"
                        class="flex w-full flex-wrap items-center justify-between gap-2 px-5 py-3 text-left text-sm transition hover:bg-muted/50"
                        @click="editHold(hold)"
                    >
                        <span>
                            <span class="font-medium">{{ hold.roomName }}</span>
                            <span class="text-muted-foreground">
                                · {{ hold.propertyName }} ·
                                {{ hold.guestName ?? 'Held' }}</span
                            >
                        </span>
                        <span class="text-muted-foreground"
                            >{{ formatDateTime(hold.startsAt) }} →
                            {{ formatDateTime(hold.endsAt) }}</span
                        >
                    </button>
                </li>
            </ul>
        </section>

        <HoldFormDialog
            v-model="draft"
            :properties="properties"
            :can-manage="can.manage"
        />
    </div>
</template>
