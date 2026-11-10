<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    addDays,
    fromDateKey,
    nightsBetween,
    toDateKey,
} from '@/lib/stayDates';

const props = defineProps<{
    today: string;
    maxNights: number;
    /** Whether the night that starts on this date cannot be requested. */
    isNightBlocked: (date: string) => boolean;
    months?: number;
}>();

const checkIn = defineModel<string | null>('checkIn', { required: true });
const checkOut = defineModel<string | null>('checkOut', { required: true });

const hovered = ref<string | null>(null);
const monthFormatter = new Intl.DateTimeFormat('en-IN', {
    month: 'long',
    year: 'numeric',
});
const weekdays = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];

const startMonth = ref(firstOfMonth(checkIn.value ?? props.today));
const canGoBack = computed(() => startMonth.value > firstOfMonth(props.today));

function firstOfMonth(key: string): string {
    return `${key.slice(0, 7)}-01`;
}

function shiftMonth(key: string, months: number): string {
    const date = fromDateKey(key);
    date.setMonth(date.getMonth() + months);

    return toDateKey(date);
}

const visibleMonths = computed(() =>
    Array.from({ length: props.months ?? 2 }, (_, index) => {
        const first = shiftMonth(startMonth.value, index);
        const firstDate = fromDateKey(first);
        const leadingBlanks = (firstDate.getDay() + 6) % 7;
        const daysInMonth = new Date(
            firstDate.getFullYear(),
            firstDate.getMonth() + 1,
            0,
        ).getDate();

        return {
            key: first,
            label: monthFormatter.format(firstDate),
            cells: [
                ...Array.from({ length: leadingBlanks }, () => null),
                ...Array.from({ length: daysInMonth }, (_, day) =>
                    addDays(first, day),
                ),
            ],
        };
    }),
);

/** The first blocked night on or after check-in, which caps the check-out date. */
const firstBlockedAfterCheckIn = computed(() => {
    if (!checkIn.value || checkOut.value) {
        return null;
    }

    for (let offset = 0; offset <= props.maxNights; offset++) {
        const date = addDays(checkIn.value, offset);

        if (props.isNightBlocked(date)) {
            return date;
        }
    }

    return null;
});

function isPast(date: string): boolean {
    return date < props.today;
}

function canBeCheckOut(date: string): boolean {
    if (!checkIn.value || checkOut.value || date <= checkIn.value) {
        return false;
    }

    if (nightsBetween(checkIn.value, date) > props.maxNights) {
        return false;
    }

    return (
        firstBlockedAfterCheckIn.value === null ||
        date <= firstBlockedAfterCheckIn.value
    );
}

function isDisabled(date: string): boolean {
    if (isPast(date)) {
        return true;
    }

    if (canBeCheckOut(date)) {
        return false;
    }

    return props.isNightBlocked(date);
}

function select(date: string): void {
    if (isDisabled(date)) {
        return;
    }

    if (canBeCheckOut(date)) {
        checkOut.value = date;

        return;
    }

    checkIn.value = date;
    checkOut.value = null;
}

function rangeEnd(): string | null {
    return (
        checkOut.value ??
        (hovered.value && canBeCheckOut(hovered.value) ? hovered.value : null)
    );
}

function isInRange(date: string): boolean {
    const end = rangeEnd();

    return (
        checkIn.value !== null &&
        end !== null &&
        date > checkIn.value &&
        date < end
    );
}

function isEdge(date: string): boolean {
    return date === checkIn.value || date === rangeEnd();
}

function clear(): void {
    checkIn.value = null;
    checkOut.value = null;
}

defineExpose({ clear });
</script>

<template>
    <div class="select-none" @mouseleave="hovered = null">
        <div class="flex items-center justify-between">
            <button
                type="button"
                class="grid size-10 place-items-center rounded-full border border-ink/10 transition hover:border-ink/30 hover:bg-white disabled:pointer-events-none disabled:opacity-30"
                :disabled="!canGoBack"
                aria-label="Previous month"
                @click="startMonth = shiftMonth(startMonth, -1)"
            >
                <ChevronLeft class="size-4" />
            </button>
            <button
                type="button"
                class="grid size-10 place-items-center rounded-full border border-ink/10 transition hover:border-ink/30 hover:bg-white"
                aria-label="Next month"
                @click="startMonth = shiftMonth(startMonth, 1)"
            >
                <ChevronRight class="size-4" />
            </button>
        </div>

        <div
            class="-mt-8 grid gap-10"
            :class="(months ?? 2) > 1 ? 'md:grid-cols-2' : ''"
        >
            <div
                v-for="(month, monthIndex) in visibleMonths"
                :key="month.key"
                :class="monthIndex > 0 ? 'hidden md:block' : ''"
            >
                <p class="text-center font-display text-lg">
                    {{ month.label }}
                </p>
                <div
                    class="mt-5 grid grid-cols-7 text-center text-[0.7rem] tracking-wider text-ink-soft uppercase"
                >
                    <span
                        v-for="weekday in weekdays"
                        :key="weekday"
                        class="pb-2"
                        >{{ weekday }}</span
                    >
                </div>
                <div class="grid grid-cols-7 gap-y-1">
                    <template
                        v-for="(date, index) in month.cells"
                        :key="date ?? `blank-${index}`"
                    >
                        <span v-if="date === null" />
                        <div
                            v-else
                            class="relative h-11"
                            :class="[
                                isInRange(date) ? 'bg-clay/10' : '',
                                date === checkIn && rangeEnd()
                                    ? 'bg-gradient-to-r from-transparent from-50% to-clay/10 to-50%'
                                    : '',
                                date === rangeEnd() && checkIn
                                    ? 'bg-gradient-to-r from-clay/10 from-50% to-transparent to-50%'
                                    : '',
                            ]"
                        >
                            <button
                                type="button"
                                class="relative mx-auto grid size-11 place-items-center rounded-full text-sm transition"
                                :class="[
                                    isEdge(date)
                                        ? 'bg-ink font-medium text-ivory shadow-lg shadow-ink/20'
                                        : isDisabled(date)
                                          ? 'cursor-not-allowed text-ink/25'
                                          : 'hover:ring-1 hover:ring-ink/60',
                                    !isPast(date) &&
                                    isNightBlocked(date) &&
                                    !isEdge(date)
                                        ? 'line-through decoration-ink/30'
                                        : '',
                                    date === today && !isEdge(date)
                                        ? 'font-semibold text-clay'
                                        : '',
                                ]"
                                :disabled="isDisabled(date)"
                                :aria-label="date"
                                :aria-pressed="isEdge(date)"
                                @click="select(date)"
                                @mouseenter="hovered = date"
                            >
                                {{ Number(date.slice(8)) }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div
            class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-ink-soft"
        >
            <span class="flex items-center gap-2">
                <span class="size-3 rounded-full bg-ink" /> Your stay
            </span>
            <span class="flex items-center gap-2">
                <span class="text-ink/30 line-through">12</span> Fully held
            </span>
            <button
                v-if="checkIn"
                type="button"
                class="ml-auto font-medium text-ink underline underline-offset-4 hover:text-clay"
                @click="clear"
            >
                Clear dates
            </button>
        </div>
    </div>
</template>
