<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    CalendarDays,
    Check,
    Mail,
    MessageSquareQuote,
    Phone,
    Users,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import StayRequestController from '@/actions/App/Http/Controllers/Admin/StayRequestController';
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
import { Spinner } from '@/components/ui/spinner';
import { formatLongDate } from '@/lib/stayDates';
import { index as holdsIndex } from '@/routes/admin/holds';
import { index } from '@/routes/admin/stay-requests';
import type { StayRequestStatus } from '@/types';

type StayRequestRow = {
    id: number;
    reference: string;
    name: string;
    email: string;
    phone: string;
    guests: number;
    checkIn: string;
    checkOut: string;
    nights: number;
    message: string | null;
    status: StayRequestStatus;
    statusLabel: string;
    adminNote: string | null;
    respondedBy: string | null;
    respondedAt: string | null;
    createdAt: string | null;
    property: { id: number; name: string; slug: string };
    rooms: { id: number; name: string }[];
};

const props = defineProps<{
    stayRequests: {
        data: StayRequestRow[];
        prev_page_url: string | null;
        next_page_url: string | null;
        total: number;
    };
    filters: { status: StayRequestStatus | 'all' };
    counts: Partial<Record<StayRequestStatus, number>>;
    can: { manage: boolean; viewHolds: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Stay requests', href: index() }],
    },
});

const tabs = computed(() => [
    { value: 'pending', label: 'Pending', count: props.counts.pending ?? 0 },
    { value: 'approved', label: 'Approved', count: props.counts.approved ?? 0 },
    { value: 'declined', label: 'Declined', count: props.counts.declined ?? 0 },
    { value: 'all', label: 'All', count: null },
]);

const answering = ref<{
    request: StayRequestRow;
    action: 'approve' | 'decline';
} | null>(null);
const form = useForm({ admin_note: '' });

const statusStyles: Record<StayRequestStatus, string> = {
    pending: 'bg-honey/15 text-clay-deep dark:text-honey',
    approved: 'bg-sage/15 text-sage dark:text-sage-soft',
    declined: 'bg-muted text-muted-foreground',
};

const relative = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

function receivedAgo(iso: string | null): string {
    if (!iso) {
        return '';
    }

    const minutes = Math.round((new Date(iso).getTime() - Date.now()) / 60_000);

    if (Math.abs(minutes) < 60) {
        return relative.format(minutes, 'minute');
    }

    if (Math.abs(minutes) < 1440) {
        return relative.format(Math.round(minutes / 60), 'hour');
    }

    return relative.format(Math.round(minutes / 1440), 'day');
}

function open(request: StayRequestRow, action: 'approve' | 'decline'): void {
    form.reset();
    form.clearErrors();
    answering.value = { request, action };
}

function submit(): void {
    if (!answering.value) {
        return;
    }

    const { request, action } = answering.value;
    const route =
        action === 'approve'
            ? StayRequestController.approve(request.id)
            : StayRequestController.decline(request.id);

    form.submit(route, {
        preserveScroll: true,
        onSuccess: () => (answering.value = null),
    });
}

const answerErrors = computed(() => Object.values(form.errors));
</script>

<template>
    <Head title="Stay requests" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl">Stay requests</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Approving a request holds its rooms for the stay, so nobody
                    else can request them.
                </p>
            </div>
        </div>

        <nav class="flex flex-wrap gap-2">
            <Link
                v-for="tab in tabs"
                :key="tab.value"
                :href="index({ query: { status: tab.value } })"
                preserve-scroll
                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm transition"
                :class="
                    filters.status === tab.value
                        ? 'border-transparent bg-foreground text-background'
                        : 'hover:bg-muted'
                "
            >
                {{ tab.label }}
                <span
                    v-if="tab.count !== null"
                    class="rounded-full px-2 py-0.5 text-xs"
                    :class="
                        filters.status === tab.value
                            ? 'bg-background/20'
                            : 'bg-muted'
                    "
                >
                    {{ tab.count }}
                </span>
            </Link>
        </nav>

        <div
            v-if="stayRequests.data.length === 0"
            class="rounded-2xl border border-dashed p-16 text-center text-muted-foreground"
        >
            Nothing here yet.
        </div>

        <div class="grid gap-4">
            <article
                v-for="request in stayRequests.data"
                :key="request.id"
                class="grid gap-5 rounded-2xl border bg-card p-5 md:grid-cols-[1fr_auto] md:p-6"
            >
                <div class="min-w-0 space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-lg font-semibold">
                            {{ request.name }}
                        </h2>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="statusStyles[request.status]"
                        >
                            {{ request.statusLabel }}
                        </span>
                        <span class="font-mono text-xs text-muted-foreground">{{
                            request.reference
                        }}</span>
                        <span class="text-xs text-muted-foreground"
                            >· {{ receivedAgo(request.createdAt) }}</span
                        >
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                        <span class="flex items-center gap-2">
                            <CalendarDays
                                class="size-4 text-muted-foreground"
                            />
                            {{ formatLongDate(request.checkIn) }} →
                            {{ formatLongDate(request.checkOut) }}
                            <span class="text-muted-foreground"
                                >({{ request.nights }}
                                {{
                                    request.nights === 1 ? 'night' : 'nights'
                                }})</span
                            >
                        </span>
                        <span class="flex items-center gap-2">
                            <Users class="size-4 text-muted-foreground" />
                            {{ request.guests }} guests
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-muted-foreground"
                            >{{ request.property.name }}:</span
                        >
                        <span
                            v-for="room in request.rooms"
                            :key="room.id"
                            class="rounded-full bg-clay/10 px-3 py-1 text-xs font-medium text-clay-deep dark:text-honey"
                        >
                            {{ room.name }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                        <a
                            :href="`mailto:${request.email}`"
                            class="flex items-center gap-2 hover:underline"
                        >
                            <Mail class="size-4 text-muted-foreground" />
                            {{ request.email }}
                        </a>
                        <a
                            :href="`tel:${request.phone}`"
                            class="flex items-center gap-2 hover:underline"
                        >
                            <Phone class="size-4 text-muted-foreground" />
                            {{ request.phone }}
                        </a>
                    </div>

                    <blockquote
                        v-if="request.message"
                        class="flex gap-3 rounded-xl bg-muted/60 p-4 text-sm"
                    >
                        <MessageSquareQuote
                            class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                        />
                        <span class="whitespace-pre-line">{{
                            request.message
                        }}</span>
                    </blockquote>

                    <p
                        v-if="request.status !== 'pending'"
                        class="text-xs text-muted-foreground"
                    >
                        {{ request.statusLabel }} by
                        {{ request.respondedBy ?? 'an admin' }}
                        {{ receivedAgo(request.respondedAt) }}
                        <template v-if="request.adminNote"
                            >· “{{ request.adminNote }}”</template
                        >
                    </p>
                </div>

                <div
                    class="flex items-start gap-2 md:flex-col md:items-stretch"
                >
                    <template v-if="request.status === 'pending' && can.manage">
                        <Button
                            class="bg-sage text-ivory hover:bg-sage/90"
                            @click="open(request, 'approve')"
                        >
                            <Check /> Approve & hold
                        </Button>
                        <Button
                            variant="outline"
                            @click="open(request, 'decline')"
                        >
                            <X /> Decline
                        </Button>
                    </template>
                    <Button
                        v-else-if="
                            request.status === 'approved' && can.viewHolds
                        "
                        variant="outline"
                        as-child
                    >
                        <Link
                            :href="
                                holdsIndex({ query: { from: request.checkIn } })
                            "
                            >View holds</Link
                        >
                    </Button>
                </div>
            </article>
        </div>

        <div
            v-if="stayRequests.prev_page_url || stayRequests.next_page_url"
            class="flex justify-between"
        >
            <Button
                variant="outline"
                :disabled="!stayRequests.prev_page_url"
                @click="router.visit(stayRequests.prev_page_url!)"
                >Previous</Button
            >
            <Button
                variant="outline"
                :disabled="!stayRequests.next_page_url"
                @click="router.visit(stayRequests.next_page_url!)"
                >Next</Button
            >
        </div>

        <Dialog
            :open="answering !== null"
            @update:open="(value) => !value && (answering = null)"
        >
            <DialogContent v-if="answering">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            answering.action === 'approve'
                                ? 'Approve'
                                : 'Decline'
                        }}
                        {{ answering.request.name }}'s request?
                    </DialogTitle>
                    <DialogDescription>
                        <template v-if="answering.action === 'approve'">
                            {{
                                answering.request.rooms
                                    .map((room) => room.name)
                                    .join(', ')
                            }}
                            will be held from
                            {{ formatLongDate(answering.request.checkIn) }} to
                            {{ formatLongDate(answering.request.checkOut) }}.
                            Contact the guest to share arrival details.
                        </template>
                        <template v-else
                            >The rooms stay open for other guests.</template
                        >
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-2" @submit.prevent="submit">
                    <label for="admin-note" class="text-sm font-medium"
                        >Private note (optional)</label
                    >
                    <textarea
                        id="admin-note"
                        v-model="form.admin_note"
                        rows="3"
                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        placeholder="Visible to admins only"
                    />
                    <InputError
                        v-for="error in answerErrors"
                        :key="error"
                        :message="error"
                    />

                    <DialogFooter class="mt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="answering = null"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            :variant="
                                answering.action === 'approve'
                                    ? 'default'
                                    : 'destructive'
                            "
                        >
                            <Spinner v-if="form.processing" />
                            {{
                                answering.action === 'approve'
                                    ? 'Approve & hold rooms'
                                    : 'Decline request'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
