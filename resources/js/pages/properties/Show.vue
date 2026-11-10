<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Bath,
    BedDouble,
    Check,
    Clock,
    Grid2x2,
    LogIn,
    LogOut,
    MapPin,
    Minus,
    PartyPopper,
    Plus,
    ScrollText,
    Users,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import PhotoLightbox from '@/components/storefront/PhotoLightbox.vue';
import StayCalendar from '@/components/storefront/StayCalendar.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { amenityIcon } from '@/lib/amenities';
import {
    formatLongDate,
    formatShortDate,
    isNightFullyHeld,
    isRoomFree,
    nightsBetween,
} from '@/lib/stayDates';
import { store } from '@/routes/stay-requests';
import type { PropertyDetails, PropertyPhoto, StorefrontRoom } from '@/types';

const props = defineProps<{
    property: PropertyDetails;
    images: PropertyPhoto[];
    rooms: StorefrontRoom[];
    maxNights: number;
    today: string;
}>();

const page = usePage();

const lightboxIndex = ref<number | null>(null);
const checkIn = ref<string | null>(null);
const checkOut = ref<string | null>(null);
const guests = ref(Math.min(2, props.property.maxGuests));
const selectedRoomIds = ref<number[]>([]);
const isRequestOpen = ref(false);
const confirmation = ref<{ reference: string; name: string } | null>(null);

const hero = computed(() => props.images[0] ?? null);
const mosaic = computed(() => props.images.slice(0, 5));
const paragraphs = computed(() =>
    (props.property.description ?? '').split(/\n\s*\n/).filter(Boolean),
);
const hasDates = computed(
    () => checkIn.value !== null && checkOut.value !== null,
);
const nights = computed(() =>
    hasDates.value ? nightsBetween(checkIn.value!, checkOut.value!) : 0,
);
const selectedRooms = computed(() =>
    props.rooms.filter((room) => selectedRoomIds.value.includes(room.id)),
);
const roomCapacity = computed(() =>
    selectedRooms.value.reduce((total, room) => total + room.maxGuests, 0),
);
const canRequest = computed(
    () => hasDates.value && selectedRoomIds.value.length > 0,
);

const mapUrl = computed(() => {
    const { latitude, longitude } = props.property;

    if (latitude === null || longitude === null) {
        return null;
    }

    const pad = 0.012;

    return `https://www.openstreetmap.org/export/embed.html?bbox=${longitude - pad}%2C${latitude - pad}%2C${longitude + pad}%2C${latitude + pad}&layer=mapnik`;
});

function isNightBlocked(date: string): boolean {
    return isNightFullyHeld(
        props.rooms,
        date,
        props.property.checkInTime,
        props.property.checkOutTime,
    );
}

function isAvailable(room: StorefrontRoom): boolean {
    if (!hasDates.value) {
        return true;
    }

    return isRoomFree(
        room,
        checkIn.value!,
        checkOut.value!,
        props.property.checkInTime,
        props.property.checkOutTime,
    );
}

function toggleRoom(room: StorefrontRoom): void {
    if (!isAvailable(room)) {
        return;
    }

    selectedRoomIds.value = selectedRoomIds.value.includes(room.id)
        ? selectedRoomIds.value.filter((id) => id !== room.id)
        : [...selectedRoomIds.value, room.id];
}

function roomPhoto(room: StorefrontRoom): PropertyPhoto | null {
    return room.images[0] ?? null;
}

function openPhoto(photo: PropertyPhoto): void {
    lightboxIndex.value = props.images.findIndex(
        (image) => image.id === photo.id,
    );
}

// Drop rooms that are not free for newly picked dates.
watch([checkIn, checkOut], () => {
    selectedRoomIds.value = selectedRoomIds.value.filter((id) => {
        const room = props.rooms.find((candidate) => candidate.id === id);

        return room !== undefined && isAvailable(room);
    });
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    message: '',
});

function startRequest(): void {
    if (!canRequest.value) {
        document
            .getElementById('availability')
            ?.scrollIntoView({ behavior: 'smooth', block: 'start' });

        return;
    }

    form.clearErrors();
    isRequestOpen.value = true;
}

function submitRequest(): void {
    form.transform((data) => ({
        ...data,
        guests: guests.value,
        check_in: checkIn.value,
        check_out: checkOut.value,
        room_ids: selectedRoomIds.value,
    })).post(store.url(props.property.slug), {
        preserveScroll: true,
        onSuccess: () => {
            const flashed = page.flash?.stayRequest as
                | { reference: string; name: string }
                | undefined;

            isRequestOpen.value = false;
            confirmation.value = flashed ?? null;
            form.reset();
            selectedRoomIds.value = [];
            checkIn.value = null;
            checkOut.value = null;
        },
    });
}

const stayErrors = computed(() =>
    ['check_in', 'check_out', 'room_ids', 'guests']
        .map((key) => form.errors[key as keyof typeof form.errors])
        .filter(Boolean),
);
</script>

<template>
    <Head :title="property.name">
        <meta
            name="description"
            :content="
                property.tagline ?? `${property.name} in ${property.location}`
            "
        />
    </Head>
    <div>
        <!-- Hero -->
        <section
            class="relative isolate flex min-h-[88svh] items-end overflow-hidden bg-ink"
        >
            <img
                v-if="hero"
                :src="hero.url"
                :alt="hero.alt"
                class="sn-settle absolute inset-0 -z-10 size-full object-cover"
                fetchpriority="high"
            />
            <div
                class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/30 to-black/20"
            />

            <div
                class="mx-auto w-full max-w-7xl px-5 pt-32 pb-14 text-ivory sm:px-8 md:pb-20"
            >
                <p
                    class="sn-rise inline-flex items-center gap-2 text-xs tracking-[0.22em] text-honey uppercase"
                    style="animation-delay: 150ms"
                >
                    <MapPin class="size-3.5" />
                    {{ property.location }}
                </p>
                <h1
                    class="sn-rise mt-4 font-display text-[clamp(3rem,9vw,7.5rem)] leading-[0.92] font-light tracking-tight"
                    style="animation-delay: 280ms"
                >
                    {{ property.name }}
                </h1>
                <p
                    v-if="property.tagline"
                    class="sn-rise mt-4 max-w-2xl font-display text-xl text-ivory/80 italic sm:text-2xl"
                    style="animation-delay: 420ms"
                >
                    {{ property.tagline }}
                </p>
                <div
                    class="sn-rise mt-9 flex flex-wrap items-center gap-3 text-sm"
                    style="animation-delay: 560ms"
                >
                    <span
                        v-for="fact in [
                            {
                                icon: Users,
                                label: `Up to ${property.maxGuests} guests`,
                            },
                            {
                                icon: BedDouble,
                                label: `${property.bedrooms} bedrooms · ${property.beds} beds`,
                            },
                            {
                                icon: Bath,
                                label: `${property.bathrooms} bathrooms`,
                            },
                        ]"
                        :key="fact.label"
                        class="inline-flex items-center gap-2 rounded-full border border-ivory/20 bg-ivory/10 px-4 py-2 backdrop-blur-md"
                    >
                        <component :is="fact.icon" class="size-4 text-honey" />
                        {{ fact.label }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Gallery mosaic -->
        <section
            v-if="images.length > 1"
            class="mx-auto max-w-7xl px-5 pt-6 sm:px-8"
        >
            <div
                class="relative grid h-[26rem] grid-cols-2 gap-2 overflow-hidden rounded-[1.75rem] md:h-[34rem] md:grid-cols-4 md:grid-rows-2"
            >
                <button
                    v-for="(photo, index) in mosaic"
                    :key="photo.id"
                    type="button"
                    class="group relative overflow-hidden"
                    :class="
                        index === 0
                            ? 'col-span-2 md:row-span-2'
                            : index > 2
                              ? 'hidden md:block'
                              : ''
                    "
                    :aria-label="`Open photo: ${photo.caption ?? photo.alt}`"
                    @click="openPhoto(photo)"
                >
                    <img
                        :src="photo.url"
                        :alt="photo.alt"
                        loading="lazy"
                        class="size-full object-cover transition duration-[1.2s] group-hover:scale-105"
                    />
                    <span
                        class="absolute inset-0 bg-ink/0 transition duration-500 group-hover:bg-ink/15"
                    />
                    <span
                        v-if="photo.caption"
                        class="absolute bottom-3 left-3 rounded-full bg-ivory/90 px-3 py-1 text-xs font-medium text-ink opacity-0 backdrop-blur transition duration-300 group-hover:opacity-100"
                    >
                        {{ photo.caption }}
                    </span>
                </button>
                <button
                    type="button"
                    class="absolute right-4 bottom-4 inline-flex items-center gap-2 rounded-full bg-ivory px-4 py-2.5 text-sm font-medium text-ink shadow-xl transition hover:bg-white"
                    @click="lightboxIndex = 0"
                >
                    <Grid2x2 class="size-4" />
                    Show all {{ images.length }} photos
                </button>
            </div>
        </section>

        <div
            class="mx-auto grid max-w-7xl gap-14 px-5 py-16 sm:px-8 md:py-20 lg:grid-cols-[1fr_24rem] lg:gap-20"
        >
            <div class="min-w-0 space-y-20">
                <!-- About -->
                <section v-reveal>
                    <p class="text-xs tracking-[0.25em] text-clay uppercase">
                        {{ property.propertyType ?? 'The home' }}
                    </p>
                    <h2 class="mt-3 font-display text-4xl font-light">
                        About this space
                    </h2>
                    <div
                        class="mt-6 space-y-4 text-[1.05rem] leading-relaxed text-ink-soft"
                    >
                        <p
                            v-for="(paragraph, index) in paragraphs"
                            :key="index"
                        >
                            {{ paragraph }}
                        </p>
                    </div>
                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-linen p-5">
                            <LogIn class="size-5 text-clay" />
                            <p class="mt-3 text-xs text-ink-soft">
                                Check-in from
                            </p>
                            <p class="font-display text-2xl">
                                {{ property.checkInLabel }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-linen p-5">
                            <LogOut class="size-5 text-clay" />
                            <p class="mt-3 text-xs text-ink-soft">
                                Check-out by
                            </p>
                            <p class="font-display text-2xl">
                                {{ property.checkOutLabel }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-sage-soft p-5">
                            <Clock class="size-5 text-sage" />
                            <p class="mt-3 text-xs text-ink-soft">Reply time</p>
                            <p class="font-display text-2xl">Within hours</p>
                        </div>
                    </div>
                </section>

                <!-- Amenities -->
                <section v-if="property.amenities.length" v-reveal>
                    <h2 class="font-display text-4xl font-light">
                        What this place offers
                    </h2>
                    <ul class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <li
                            v-for="amenity in property.amenities"
                            :key="amenity.key"
                            class="group flex items-center gap-3 rounded-2xl border border-ink/8 bg-white/60 px-4 py-4 transition duration-300 hover:-translate-y-0.5 hover:border-clay/30 hover:bg-white hover:shadow-lg hover:shadow-ink/5"
                        >
                            <span
                                class="grid size-10 shrink-0 place-items-center rounded-xl bg-linen text-clay transition group-hover:bg-clay group-hover:text-ivory"
                            >
                                <component
                                    :is="amenityIcon(amenity.key)"
                                    class="size-5"
                                />
                            </span>
                            <span class="text-sm">{{ amenity.label }}</span>
                        </li>
                    </ul>
                </section>

                <!-- Availability -->
                <section id="availability" v-reveal class="scroll-mt-28">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-xs tracking-[0.25em] text-clay uppercase"
                            >
                                Step 1
                            </p>
                            <h2 class="mt-3 font-display text-4xl font-light">
                                <template v-if="hasDates">
                                    {{ nights }}
                                    {{ nights === 1 ? 'night' : 'nights' }} at
                                    {{ property.name }}
                                </template>
                                <template v-else
                                    >When would you like to stay?</template
                                >
                            </h2>
                            <p class="mt-2 text-sm text-ink-soft">
                                <template v-if="hasDates">
                                    {{ formatLongDate(checkIn!) }} →
                                    {{ formatLongDate(checkOut!) }}
                                </template>
                                <template v-else-if="checkIn"
                                    >Now pick your check-out date.</template
                                >
                                <template v-else
                                    >Pick your check-in date to see which rooms
                                    are free.</template
                                >
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 rounded-[1.75rem] border border-ink/8 bg-white/70 p-5 shadow-[0_20px_60px_-40px_rgba(29,26,22,0.4)] sm:p-8"
                    >
                        <StayCalendar
                            v-model:check-in="checkIn"
                            v-model:check-out="checkOut"
                            :today="today"
                            :max-nights="maxNights"
                            :is-night-blocked="isNightBlocked"
                        />
                    </div>
                </section>

                <!-- Rooms -->
                <section id="rooms" v-reveal class="scroll-mt-28">
                    <p class="text-xs tracking-[0.25em] text-clay uppercase">
                        Step 2
                    </p>
                    <h2 class="mt-3 font-display text-4xl font-light">
                        Choose your rooms
                    </h2>
                    <p class="mt-2 text-sm text-ink-soft">
                        Request one room or several. Rooms that are already held
                        for your dates are greyed out.
                    </p>

                    <div
                        v-if="rooms.length === 0"
                        class="mt-8 rounded-3xl border border-dashed border-ink/15 p-10 text-center text-ink-soft"
                    >
                        Rooms for this home are being prepared.
                    </div>

                    <div class="mt-8 grid gap-5 sm:grid-cols-2">
                        <button
                            v-for="room in rooms"
                            :key="room.id"
                            type="button"
                            class="group relative overflow-hidden rounded-[1.6rem] border bg-white text-left transition duration-300"
                            :class="[
                                selectedRoomIds.includes(room.id)
                                    ? 'border-clay shadow-[0_20px_50px_-25px_rgba(184,103,58,0.65)] ring-2 ring-clay'
                                    : 'border-ink/8 hover:-translate-y-1 hover:shadow-xl hover:shadow-ink/10',
                                !isAvailable(room)
                                    ? 'cursor-not-allowed opacity-55 grayscale hover:translate-y-0 hover:shadow-none'
                                    : '',
                            ]"
                            :disabled="!isAvailable(room)"
                            :aria-pressed="selectedRoomIds.includes(room.id)"
                            @click="toggleRoom(room)"
                        >
                            <div
                                class="relative aspect-[4/3] overflow-hidden bg-linen"
                            >
                                <img
                                    v-if="roomPhoto(room)"
                                    :src="roomPhoto(room)!.url"
                                    :alt="roomPhoto(room)!.alt"
                                    loading="lazy"
                                    class="size-full object-cover transition duration-[1.2s] group-hover:scale-105"
                                />
                                <BedDouble
                                    v-else
                                    class="absolute inset-0 m-auto size-10 text-ink/20"
                                />
                                <span
                                    class="absolute top-4 left-4 rounded-full px-3 py-1 text-xs font-medium backdrop-blur"
                                    :class="
                                        !hasDates
                                            ? 'bg-ivory/90 text-ink'
                                            : isAvailable(room)
                                              ? 'bg-sage text-ivory'
                                              : 'bg-ink/80 text-ivory'
                                    "
                                >
                                    {{
                                        !hasDates
                                            ? `Sleeps ${room.maxGuests}`
                                            : isAvailable(room)
                                              ? 'Available'
                                              : 'Held for these dates'
                                    }}
                                </span>
                                <span
                                    class="absolute top-4 right-4 grid size-9 place-items-center rounded-full border-2 transition"
                                    :class="
                                        selectedRoomIds.includes(room.id)
                                            ? 'scale-100 border-clay bg-clay text-ivory'
                                            : 'scale-90 border-ivory/90 bg-ink/20 text-transparent backdrop-blur'
                                    "
                                >
                                    <Check class="size-4" />
                                </span>
                            </div>
                            <div class="p-6">
                                <div
                                    class="flex items-baseline justify-between gap-4"
                                >
                                    <h3 class="font-display text-2xl">
                                        {{ room.name }}
                                    </h3>
                                    <span
                                        class="shrink-0 text-xs text-ink-soft"
                                        >{{ room.bedType }}</span
                                    >
                                </div>
                                <p
                                    v-if="room.description"
                                    class="mt-2 text-sm leading-relaxed text-ink-soft"
                                >
                                    {{ room.description }}
                                </p>
                                <ul
                                    v-if="room.features.length"
                                    class="mt-4 flex flex-wrap gap-2"
                                >
                                    <li
                                        v-for="feature in room.features"
                                        :key="feature"
                                        class="rounded-full bg-linen px-3 py-1 text-xs text-ink-soft"
                                    >
                                        {{ feature }}
                                    </li>
                                </ul>
                            </div>
                        </button>
                    </div>
                </section>

                <!-- House rules -->
                <section v-if="property.houseRules.length" v-reveal>
                    <h2 class="font-display text-4xl font-light">
                        Good to know
                    </h2>
                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        <li
                            v-for="rule in property.houseRules"
                            :key="rule"
                            class="flex items-start gap-3 rounded-2xl bg-linen/70 p-4 text-sm"
                        >
                            <ScrollText
                                class="mt-0.5 size-4 shrink-0 text-clay"
                            />
                            {{ rule }}
                        </li>
                    </ul>
                </section>

                <!-- Location -->
                <section v-if="mapUrl" v-reveal>
                    <h2 class="font-display text-4xl font-light">
                        Where you'll be
                    </h2>
                    <p
                        class="mt-2 flex items-center gap-2 text-sm text-ink-soft"
                    >
                        <MapPin class="size-4 text-clay" />
                        {{
                            [property.address, property.location]
                                .filter(Boolean)
                                .join(', ')
                        }}
                    </p>
                    <div
                        class="relative mt-8 overflow-hidden rounded-[1.75rem] border border-ink/8"
                    >
                        <iframe
                            :src="mapUrl"
                            title="Map of the area"
                            class="h-96 w-full grayscale-[35%] sepia-[15%]"
                            loading="lazy"
                        />
                        <div
                            class="pointer-events-none absolute inset-0 grid place-items-center"
                        >
                            <span
                                class="relative grid size-16 place-items-center"
                            >
                                <span
                                    class="absolute inset-0 animate-ping rounded-full bg-clay/30"
                                />
                                <span
                                    class="grid size-12 place-items-center rounded-full bg-clay text-ivory shadow-xl"
                                >
                                    <MapPin class="size-5" />
                                </span>
                            </span>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-ink-soft">
                        The exact address is shared once your stay is confirmed.
                    </p>
                </section>
            </div>

            <!-- Sticky request card -->
            <aside class="hidden lg:block">
                <div
                    class="sticky top-28 rounded-[1.75rem] border border-ink/8 bg-white p-7 shadow-[0_30px_80px_-40px_rgba(29,26,22,0.5)]"
                >
                    <p class="font-display text-2xl">Request your stay</p>
                    <p class="mt-1 text-sm text-ink-soft">
                        No payment needed. We reply personally.
                    </p>

                    <a
                        href="#availability"
                        class="mt-6 grid grid-cols-2 overflow-hidden rounded-2xl border border-ink/12 transition hover:border-ink/30"
                    >
                        <span class="border-r border-ink/12 p-3">
                            <span
                                class="block text-[0.65rem] font-semibold tracking-wider uppercase"
                                >Check-in</span
                            >
                            <span
                                class="text-sm"
                                :class="checkIn ? '' : 'text-ink-soft'"
                            >
                                {{
                                    checkIn
                                        ? formatShortDate(checkIn)
                                        : 'Add date'
                                }}
                            </span>
                        </span>
                        <span class="p-3">
                            <span
                                class="block text-[0.65rem] font-semibold tracking-wider uppercase"
                                >Check-out</span
                            >
                            <span
                                class="text-sm"
                                :class="checkOut ? '' : 'text-ink-soft'"
                            >
                                {{
                                    checkOut
                                        ? formatShortDate(checkOut)
                                        : 'Add date'
                                }}
                            </span>
                        </span>
                    </a>

                    <div
                        class="mt-3 flex items-center justify-between rounded-2xl border border-ink/12 p-3"
                    >
                        <span>
                            <span
                                class="block text-[0.65rem] font-semibold tracking-wider uppercase"
                                >Guests</span
                            >
                            <span class="text-sm"
                                >{{ guests }}
                                {{ guests === 1 ? 'guest' : 'guests' }}</span
                            >
                        </span>
                        <span class="flex items-center gap-2">
                            <button
                                type="button"
                                class="grid size-8 place-items-center rounded-full border border-ink/15 transition hover:border-ink disabled:opacity-30"
                                :disabled="guests <= 1"
                                aria-label="Fewer guests"
                                @click="guests--"
                            >
                                <Minus class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="grid size-8 place-items-center rounded-full border border-ink/15 transition hover:border-ink disabled:opacity-30"
                                :disabled="guests >= property.maxGuests"
                                aria-label="More guests"
                                @click="guests++"
                            >
                                <Plus class="size-3.5" />
                            </button>
                        </span>
                    </div>

                    <div class="mt-5">
                        <p
                            class="text-[0.65rem] font-semibold tracking-wider uppercase"
                        >
                            Rooms
                        </p>
                        <div
                            v-if="selectedRooms.length"
                            class="mt-2 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="room in selectedRooms"
                                :key="room.id"
                                class="inline-flex items-center gap-1.5 rounded-full bg-clay/10 px-3 py-1 text-xs font-medium text-clay-deep"
                            >
                                <Check class="size-3" /> {{ room.name }}
                            </span>
                        </div>
                        <a
                            v-else
                            href="#rooms"
                            class="mt-1 block text-sm text-ink-soft underline underline-offset-4 hover:text-ink"
                        >
                            Choose at least one room
                        </a>
                        <p
                            v-if="selectedRooms.length && guests > roomCapacity"
                            class="mt-2 text-xs text-clay-deep"
                        >
                            These rooms sleep {{ roomCapacity }}. You may want
                            to add another room.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="group mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-clay to-clay-deep px-6 py-4 text-sm font-medium text-ivory shadow-lg shadow-clay/30 transition hover:shadow-xl hover:shadow-clay/40"
                        @click="startRequest"
                    >
                        {{
                            canRequest
                                ? 'Request to stay'
                                : hasDates
                                  ? 'Choose your rooms'
                                  : 'Check availability'
                        }}
                        <ArrowRight
                            class="size-4 transition group-hover:translate-x-0.5"
                        />
                    </button>
                    <p class="mt-4 text-center text-xs text-ink-soft">
                        Your rooms are held once your host confirms.
                    </p>
                </div>
            </aside>
        </div>

        <!-- Mobile request bar -->
        <div
            class="fixed inset-x-0 bottom-0 z-40 border-t border-ink/8 bg-ivory/95 px-5 py-3 backdrop-blur-xl lg:hidden"
        >
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0 text-sm">
                    <p class="truncate font-medium">
                        {{
                            hasDates
                                ? `${formatShortDate(checkIn!)} – ${formatShortDate(checkOut!)}`
                                : 'Add your dates'
                        }}
                    </p>
                    <p class="truncate text-xs text-ink-soft">
                        {{
                            selectedRooms.length
                                ? selectedRooms
                                      .map((room) => room.name)
                                      .join(', ')
                                : 'No rooms chosen yet'
                        }}
                    </p>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded-full bg-clay px-5 py-3 text-sm font-medium text-ivory shadow-lg shadow-clay/30"
                    @click="startRequest"
                >
                    {{ canRequest ? 'Request' : 'Check dates' }}
                </button>
            </div>
        </div>
        <div class="h-20 lg:hidden" />

        <PhotoLightbox v-model="lightboxIndex" :photos="images" />

        <!-- Request form -->
        <Dialog v-model:open="isRequestOpen">
            <DialogContent
                class="max-h-[92svh] overflow-y-auto border-none bg-ivory p-0 text-ink sm:max-w-xl"
            >
                <div
                    class="relative overflow-hidden bg-ink px-7 pt-7 pb-6 text-ivory"
                >
                    <img
                        v-if="hero"
                        :src="hero.url"
                        alt=""
                        class="absolute inset-0 size-full object-cover opacity-30"
                    />
                    <div class="relative">
                        <DialogHeader class="text-left">
                            <DialogTitle
                                class="font-display text-3xl font-light"
                                >Almost there</DialogTitle
                            >
                            <DialogDescription class="text-ivory/75">
                                Tell us who is coming and we will confirm your
                                rooms.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="mt-5 flex flex-wrap gap-2 text-xs">
                            <span
                                class="rounded-full bg-ivory/15 px-3 py-1 backdrop-blur"
                                v-if="hasDates"
                            >
                                {{ formatShortDate(checkIn!) }} →
                                {{ formatShortDate(checkOut!) }} · {{ nights }}
                                {{ nights === 1 ? 'night' : 'nights' }}
                            </span>
                            <span
                                class="rounded-full bg-ivory/15 px-3 py-1 backdrop-blur"
                            >
                                {{ guests }}
                                {{ guests === 1 ? 'guest' : 'guests' }}
                            </span>
                            <span
                                class="rounded-full bg-ivory/15 px-3 py-1 backdrop-blur"
                            >
                                {{
                                    selectedRooms
                                        .map((room) => room.name)
                                        .join(', ')
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <form
                    class="space-y-5 px-7 pt-6 pb-7"
                    @submit.prevent="submitRequest"
                >
                    <div
                        v-if="stayErrors.length"
                        class="rounded-2xl border border-clay/30 bg-clay/10 p-4 text-sm text-clay-deep"
                    >
                        <p v-for="error in stayErrors" :key="error">
                            {{ error }}
                        </p>
                    </div>

                    <label class="block">
                        <span
                            class="text-xs font-semibold tracking-wider uppercase"
                            >Full name</span
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            class="mt-2 w-full rounded-xl border border-ink/15 bg-white px-4 py-3 text-sm transition outline-none focus:border-clay focus:ring-4 focus:ring-clay/15"
                            placeholder="Your name"
                        />
                        <span
                            v-if="form.errors.name"
                            class="mt-1 block text-xs text-clay-deep"
                            >{{ form.errors.name }}</span
                        >
                    </label>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span
                                class="text-xs font-semibold tracking-wider uppercase"
                                >Email</span
                            >
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="mt-2 w-full rounded-xl border border-ink/15 bg-white px-4 py-3 text-sm transition outline-none focus:border-clay focus:ring-4 focus:ring-clay/15"
                                placeholder="you@example.com"
                            />
                            <span
                                v-if="form.errors.email"
                                class="mt-1 block text-xs text-clay-deep"
                                >{{ form.errors.email }}</span
                            >
                        </label>
                        <label class="block">
                            <span
                                class="text-xs font-semibold tracking-wider uppercase"
                                >Phone</span
                            >
                            <input
                                v-model="form.phone"
                                type="tel"
                                autocomplete="tel"
                                required
                                class="mt-2 w-full rounded-xl border border-ink/15 bg-white px-4 py-3 text-sm transition outline-none focus:border-clay focus:ring-4 focus:ring-clay/15"
                                placeholder="+91 98765 43210"
                            />
                            <span
                                v-if="form.errors.phone"
                                class="mt-1 block text-xs text-clay-deep"
                                >{{ form.errors.phone }}</span
                            >
                        </label>
                    </div>
                    <label class="block">
                        <span
                            class="text-xs font-semibold tracking-wider uppercase"
                            >A note for your host
                            <span class="font-normal text-ink-soft normal-case"
                                >(optional)</span
                            ></span
                        >
                        <textarea
                            v-model="form.message"
                            rows="3"
                            class="mt-2 w-full resize-none rounded-xl border border-ink/15 bg-white px-4 py-3 text-sm transition outline-none focus:border-clay focus:ring-4 focus:ring-clay/15"
                            placeholder="Arrival time, special occasions, anything we should know…"
                        />
                        <span
                            v-if="form.errors.message"
                            class="mt-1 block text-xs text-clay-deep"
                            >{{ form.errors.message }}</span
                        >
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-clay to-clay-deep px-6 py-4 text-sm font-medium text-ivory shadow-lg shadow-clay/30 transition hover:shadow-xl disabled:opacity-60"
                    >
                        <span
                            v-if="form.processing"
                            class="size-4 animate-spin rounded-full border-2 border-ivory/40 border-t-ivory"
                        />
                        Send stay request
                    </button>
                    <p class="text-center text-xs text-ink-soft">
                        Sending a request does not charge you anything.
                    </p>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Confirmation -->
        <Dialog
            :open="confirmation !== null"
            @update:open="(open) => !open && (confirmation = null)"
        >
            <DialogContent
                class="border-none bg-ivory p-10 text-center text-ink sm:max-w-md"
            >
                <div
                    class="sn-rise mx-auto grid size-20 place-items-center rounded-full bg-gradient-to-br from-honey to-clay text-ivory shadow-xl shadow-clay/30"
                >
                    <PartyPopper class="size-9" />
                </div>
                <DialogHeader class="mt-2 text-center sm:text-center">
                    <DialogTitle class="font-display text-3xl font-light">
                        Thank you{{
                            confirmation
                                ? `, ${confirmation.name.split(' ')[0]}`
                                : ''
                        }}!
                    </DialogTitle>
                    <DialogDescription class="text-ink-soft">
                        Your request is with your host. We will be in touch
                        shortly to confirm your rooms.
                    </DialogDescription>
                </DialogHeader>
                <div
                    class="mx-auto rounded-2xl border border-dashed border-clay/40 bg-white px-6 py-4"
                >
                    <p
                        class="text-[0.65rem] font-semibold tracking-[0.2em] text-ink-soft uppercase"
                    >
                        Your reference
                    </p>
                    <p
                        class="mt-1 font-mono text-2xl tracking-widest text-clay-deep"
                    >
                        {{ confirmation?.reference }}
                    </p>
                </div>
                <button
                    type="button"
                    class="mx-auto mt-2 rounded-full bg-ink px-8 py-3 text-sm font-medium text-ivory transition hover:bg-clay"
                    @click="confirmation = null"
                >
                    Lovely, thanks
                </button>
            </DialogContent>
        </Dialog>
    </div>
</template>
