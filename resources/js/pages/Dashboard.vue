<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BedDouble,
    CalendarRange,
    DoorOpen,
    Home,
    Inbox,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { formatDateTime, formatShortDate } from '@/lib/stayDates';
import { dashboard } from '@/routes';
import { index as holdsIndex } from '@/routes/admin/holds';
import { index as stayRequestsIndex } from '@/routes/admin/stay-requests';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    stats: {
        properties: number;
        rooms: number;
        heldNow: number;
        freeNow: number;
        pendingRequests: number;
    };
    pendingRequests: {
        id: number;
        reference: string;
        name: string;
        property: string;
        rooms: string;
        checkIn: string;
        checkOut: string;
        guests: number;
        createdAt: string | null;
    }[];
    upcomingHolds: {
        id: number;
        room: string;
        property: string;
        guestName: string | null;
        startsAt: string;
        endsAt: string;
        isActive: boolean;
    }[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="relative overflow-hidden rounded-2xl bg-ink p-6 text-ivory md:p-8"
        >
            <div
                class="pointer-events-none absolute -top-24 -right-16 size-72 rounded-full bg-clay/40 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-28 left-1/3 size-72 rounded-full bg-honey/20 blur-3xl"
            />
            <div
                class="relative flex flex-wrap items-end justify-between gap-6"
            >
                <div>
                    <p class="text-xs tracking-[0.25em] text-honey uppercase">
                        Today at Santhi Nest
                    </p>
                    <h1
                        class="mt-2 font-display text-3xl font-light md:text-4xl"
                    >
                        <template v-if="stats.pendingRequests > 0">
                            {{ stats.pendingRequests }}
                            {{
                                stats.pendingRequests === 1
                                    ? 'guest is'
                                    : 'guests are'
                            }}
                            waiting for your reply
                        </template>
                        <template v-else
                            >All requests answered. Lovely.</template
                        >
                    </h1>
                </div>
                <Link
                    :href="stayRequestsIndex()"
                    class="inline-flex items-center gap-2 rounded-full bg-ivory px-5 py-2.5 text-sm font-medium text-ink transition hover:bg-white"
                >
                    Review requests <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="stat in [
                    {
                        label: 'Pending requests',
                        value: stats.pendingRequests,
                        icon: Inbox,
                        tone: 'text-clay bg-clay/10',
                    },
                    {
                        label: 'Rooms held right now',
                        value: stats.heldNow,
                        icon: BedDouble,
                        tone: 'text-ink bg-ink/5 dark:text-ivory dark:bg-ivory/10',
                    },
                    {
                        label: 'Rooms free right now',
                        value: stats.freeNow,
                        icon: DoorOpen,
                        tone: 'text-sage bg-sage/10',
                    },
                    {
                        label: 'Properties',
                        value: stats.properties,
                        icon: Home,
                        tone: 'text-honey bg-honey/15',
                    },
                ]"
                :key="stat.label"
                class="rounded-2xl border bg-card p-5"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        {{ stat.label }}
                    </p>
                    <span
                        class="grid size-9 place-items-center rounded-xl"
                        :class="stat.tone"
                    >
                        <component :is="stat.icon" class="size-4" />
                    </span>
                </div>
                <p class="mt-3 font-display text-4xl">{{ stat.value }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border bg-card">
                <header
                    class="flex items-center justify-between border-b px-5 py-4"
                >
                    <h2 class="font-medium">Waiting for a reply</h2>
                    <Link
                        :href="stayRequestsIndex()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        See all
                    </Link>
                </header>
                <ul v-if="pendingRequests.length" class="divide-y">
                    <li v-for="request in pendingRequests" :key="request.id">
                        <Link
                            :href="stayRequestsIndex()"
                            class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-muted/50"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ request.name }}
                                </p>
                                <p
                                    class="truncate text-sm text-muted-foreground"
                                >
                                    {{ request.property }} ·
                                    {{ request.rooms }} ·
                                    {{ request.guests }} guests
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm">
                                    {{ formatShortDate(request.checkIn) }} →
                                    {{ formatShortDate(request.checkOut) }}
                                </p>
                                <p
                                    class="font-mono text-xs text-muted-foreground"
                                >
                                    {{ request.reference }}
                                </p>
                            </div>
                        </Link>
                    </li>
                </ul>
                <p
                    v-else
                    class="px-5 py-12 text-center text-sm text-muted-foreground"
                >
                    No requests waiting. New ones will appear here.
                </p>
            </section>

            <section class="rounded-2xl border bg-card">
                <header
                    class="flex items-center justify-between border-b px-5 py-4"
                >
                    <h2 class="font-medium">Holds in the next 7 days</h2>
                    <Link
                        :href="holdsIndex()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Open board
                    </Link>
                </header>
                <ul v-if="upcomingHolds.length" class="divide-y">
                    <li
                        v-for="hold in upcomingHolds"
                        :key="hold.id"
                        class="flex items-center justify-between gap-4 px-5 py-4"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid size-9 shrink-0 place-items-center rounded-xl bg-muted"
                            >
                                <CalendarRange
                                    class="size-4 text-muted-foreground"
                                />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ hold.room }}
                                    <span
                                        class="font-normal text-muted-foreground"
                                        >· {{ hold.property }}</span
                                    >
                                </p>
                                <p
                                    class="truncate text-sm text-muted-foreground"
                                >
                                    {{ hold.guestName ?? 'Held' }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right text-sm">
                            <Badge
                                v-if="hold.isActive"
                                class="mb-1 bg-sage text-ivory"
                                >In stay</Badge
                            >
                            <p class="text-muted-foreground">
                                {{ formatDateTime(hold.startsAt) }}
                            </p>
                            <p class="text-muted-foreground">
                                → {{ formatDateTime(hold.endsAt) }}
                            </p>
                        </div>
                    </li>
                </ul>
                <p
                    v-else
                    class="px-5 py-12 text-center text-sm text-muted-foreground"
                >
                    No rooms are held this week.
                </p>
            </section>
        </div>
    </div>
</template>
