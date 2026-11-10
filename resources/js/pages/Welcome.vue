<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowRight,
    BedDouble,
    CalendarHeart,
    DoorOpen,
    HeartHandshake,
    MapPin,
    Send,
    Sparkles,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import StayHighlights from '@/components/storefront/StayHighlights.vue';
import { show } from '@/routes/properties';
import type { PropertyCard } from '@/types';

const props = defineProps<{
    properties: PropertyCard[];
}>();

const featured = computed(() => props.properties[0] ?? null);
const heroImage = computed(() => featured.value?.images[0] ?? null);
const photoBand = computed(() =>
    props.properties.flatMap((property) =>
        property.images.map((image) => ({ ...image, property })),
    ),
);

const promises = [
    {
        icon: Sparkles,
        title: 'Thoughtfully designed',
        text: 'Warm light, soft linen and spotless spaces, prepared before every arrival.',
    },
    {
        icon: HeartHandshake,
        title: 'Personally confirmed',
        text: 'Every request is read by a real host, who confirms your rooms directly.',
    },
    {
        icon: DoorOpen,
        title: 'A room or the whole home',
        text: 'Pick the rooms you need for your dates. Bring the family or come alone.',
    },
];

const steps = [
    {
        icon: CalendarHeart,
        title: 'Pick your dates & rooms',
        text: 'See which rooms are free for your dates and choose the ones you love.',
    },
    {
        icon: Send,
        title: 'Send a stay request',
        text: 'Tell us who is coming. No payment and no account needed to ask.',
    },
    {
        icon: BedDouble,
        title: 'We hold your rooms',
        text: 'Your host confirms and holds the rooms for you, then shares arrival details.',
    },
];
</script>

<template>
    <Head title="Calm stays, personally hosted" />
    <div>
        <!-- Hero -->
        <section
            class="relative isolate flex min-h-[100svh] items-end overflow-hidden bg-ink"
        >
            <img
                v-if="heroImage"
                :src="heroImage.url"
                :alt="heroImage.alt"
                class="sn-settle absolute inset-0 -z-10 size-full object-cover"
                fetchpriority="high"
            />
            <div
                class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,transparent_20%,rgba(29,26,22,0.55)_75%)]"
            />
            <div
                class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/35 to-transparent"
            />

            <div
                class="mx-auto grid w-full max-w-7xl items-end gap-10 px-5 pt-32 pb-16 sm:px-8 md:pb-24 lg:grid-cols-[1fr_auto]"
            >
                <div class="max-w-3xl text-ivory">
                    <p
                        class="sn-rise inline-flex items-center gap-2 rounded-full border border-ivory/25 bg-ivory/10 px-4 py-1.5 text-xs tracking-[0.18em] uppercase backdrop-blur-md"
                        style="animation-delay: 150ms"
                    >
                        <MapPin class="size-3.5 text-honey" />
                        {{
                            featured
                                ? `Stays in ${featured.city}`
                                : 'Stays coming soon'
                        }}
                    </p>
                    <h1
                        class="sn-rise mt-6 font-display text-[clamp(3rem,8vw,6.75rem)] leading-[0.95] font-light tracking-tight text-balance"
                        style="animation-delay: 300ms"
                    >
                        Come home
                        <br />
                        to <em class="font-normal text-honey">calm.</em>
                    </h1>
                    <p
                        class="sn-rise mt-6 max-w-xl text-base leading-relaxed text-ivory/80 sm:text-lg"
                        style="animation-delay: 450ms"
                    >
                        Lamp-lit living rooms, crisp cotton sheets and quiet
                        mornings. Choose your rooms, send a request, and we will
                        keep them ready for you.
                    </p>
                    <div
                        class="sn-rise mt-9 flex flex-wrap items-center gap-4"
                        style="animation-delay: 600ms"
                    >
                        <Link
                            v-if="featured"
                            :href="show(featured.slug)"
                            class="group inline-flex items-center gap-3 rounded-full bg-ivory py-2 pr-2 pl-6 text-sm font-medium text-ink shadow-2xl shadow-black/30 transition hover:bg-white"
                        >
                            Explore {{ featured.name }}
                            <span
                                class="grid size-9 place-items-center rounded-full bg-clay text-ivory transition-transform duration-300 group-hover:translate-x-0.5"
                            >
                                <ArrowRight class="size-4" />
                            </span>
                        </Link>
                        <a
                            href="#how-it-works"
                            class="rounded-full px-5 py-3 text-sm text-ivory/85 transition hover:text-ivory"
                        >
                            How requests work
                        </a>
                    </div>
                </div>

                <Link
                    v-if="featured"
                    :href="show(featured.slug)"
                    class="sn-rise group hidden w-72 overflow-hidden rounded-3xl border border-ivory/20 bg-ivory/10 p-3 text-ivory shadow-2xl backdrop-blur-xl transition hover:bg-ivory/15 lg:block"
                    style="animation-delay: 800ms"
                >
                    <div class="overflow-hidden rounded-2xl">
                        <img
                            v-if="featured.images[1]"
                            :src="featured.images[1].url"
                            :alt="featured.images[1].alt"
                            class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105"
                        />
                    </div>
                    <div class="px-2 pt-4 pb-2">
                        <p
                            class="text-[0.7rem] tracking-[0.2em] text-honey uppercase"
                        >
                            Featured home
                        </p>
                        <p class="mt-1 font-display text-2xl">
                            {{ featured.name }}
                        </p>
                        <p class="mt-1 text-xs text-ivory/70">
                            {{ featured.bedrooms }} bedrooms ·
                            {{ featured.bathrooms }} baths · up to
                            {{ featured.maxGuests }} guests
                        </p>
                    </div>
                </Link>
            </div>

            <a
                href="#highlights"
                class="sn-float absolute bottom-6 left-1/2 hidden -translate-x-1/2 text-ivory/60 transition hover:text-ivory md:block"
                aria-label="Scroll down"
            >
                <ArrowDown class="size-5" />
            </a>
        </section>

        <StayHighlights />

        <!-- Promise -->
        <section id="promise" class="relative bg-ivory">
            <div class="mx-auto max-w-7xl px-5 py-24 sm:px-8 md:py-32">
                <div class="grid gap-14 lg:grid-cols-[1fr_1.3fr] lg:gap-24">
                    <div v-reveal>
                        <p
                            class="text-xs tracking-[0.25em] text-clay uppercase"
                        >
                            Our promise
                        </p>
                        <h2
                            class="mt-4 font-display text-4xl leading-tight font-light text-balance sm:text-5xl"
                        >
                            A quiet nest, kept with
                            <em class="text-clay">care</em>, for every kind of
                            stay.
                        </h2>
                    </div>
                    <div class="grid gap-8 sm:grid-cols-3 lg:gap-10">
                        <div
                            v-for="(promise, index) in promises"
                            :key="promise.title"
                            v-reveal="index * 120"
                            class="border-t border-ink/10 pt-6"
                        >
                            <component
                                :is="promise.icon"
                                class="size-6 text-clay"
                            />
                            <h3 class="mt-5 font-display text-xl">
                                {{ promise.title }}
                            </h3>
                            <p
                                class="mt-2 text-sm leading-relaxed text-ink-soft"
                            >
                                {{ promise.text }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Homes -->
        <section id="homes" class="bg-linen/70">
            <div class="mx-auto max-w-7xl px-5 py-24 sm:px-8 md:py-32">
                <div
                    v-reveal
                    class="flex flex-wrap items-end justify-between gap-6"
                >
                    <div>
                        <p
                            class="text-xs tracking-[0.25em] text-clay uppercase"
                        >
                            Our homes
                        </p>
                        <h2
                            class="mt-4 font-display text-4xl font-light sm:text-5xl"
                        >
                            Find your <em>nest</em>
                        </h2>
                    </div>
                    <p class="max-w-md text-sm leading-relaxed text-ink-soft">
                        Each home is hand-picked and hosted by us. Open one to
                        see every room, check your dates and send a request.
                    </p>
                </div>

                <div
                    v-if="properties.length === 0"
                    class="mt-14 rounded-3xl border border-dashed border-ink/15 p-16 text-center text-ink-soft"
                >
                    New homes are being prepared. Please check back soon.
                </div>

                <div class="mt-14 space-y-10">
                    <Link
                        v-for="(property, index) in properties"
                        :key="property.id"
                        v-reveal
                        :href="show(property.slug)"
                        class="group grid overflow-hidden rounded-[2rem] bg-ivory shadow-[0_30px_80px_-40px_rgba(29,26,22,0.45)] ring-1 ring-ink/5 transition duration-500 hover:shadow-[0_40px_90px_-40px_rgba(29,26,22,0.55)] lg:grid-cols-[1.35fr_1fr]"
                        :class="
                            index % 2 === 1
                                ? 'lg:[&>*:first-child]:order-2'
                                : ''
                        "
                    >
                        <div
                            class="grid h-80 grid-cols-3 grid-rows-2 gap-1.5 p-1.5 sm:h-[28rem]"
                        >
                            <div
                                v-for="(
                                    image, imageIndex
                                ) in property.images.slice(0, 3)"
                                :key="image.id"
                                class="overflow-hidden rounded-[1.6rem]"
                                :class="
                                    imageIndex === 0
                                        ? 'col-span-2 row-span-2'
                                        : 'rounded-[1.2rem]'
                                "
                            >
                                <img
                                    :src="image.url"
                                    :alt="image.alt"
                                    loading="lazy"
                                    class="size-full object-cover transition duration-[1.2s] group-hover:scale-105"
                                />
                            </div>
                            <div
                                v-if="property.images.length === 0"
                                class="col-span-3 row-span-2 rounded-[1.6rem] bg-sand"
                            />
                        </div>
                        <div
                            class="flex flex-col justify-between gap-10 p-8 sm:p-12"
                        >
                            <div>
                                <p
                                    class="flex items-center gap-2 text-xs tracking-[0.2em] text-sage uppercase"
                                >
                                    <MapPin class="size-3.5" />
                                    {{ property.location }}
                                </p>
                                <h3
                                    class="mt-4 font-display text-4xl font-light sm:text-5xl"
                                >
                                    {{ property.name }}
                                </h3>
                                <p
                                    v-if="property.tagline"
                                    class="mt-3 font-display text-lg text-ink-soft italic"
                                >
                                    {{ property.tagline }}
                                </p>
                            </div>
                            <div>
                                <dl
                                    class="grid grid-cols-3 gap-4 border-y border-ink/10 py-6"
                                >
                                    <div>
                                        <dt class="text-xs text-ink-soft">
                                            Guests
                                        </dt>
                                        <dd
                                            class="mt-1 flex items-center gap-1.5 font-display text-2xl"
                                        >
                                            <Users class="size-4 text-clay" />
                                            {{ property.maxGuests }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-ink-soft">
                                            Bedrooms
                                        </dt>
                                        <dd
                                            class="mt-1 flex items-center gap-1.5 font-display text-2xl"
                                        >
                                            <BedDouble
                                                class="size-4 text-clay"
                                            />
                                            {{ property.bedrooms }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-ink-soft">
                                            Rooms to request
                                        </dt>
                                        <dd
                                            class="mt-1 flex items-center gap-1.5 font-display text-2xl"
                                        >
                                            <DoorOpen
                                                class="size-4 text-clay"
                                            />
                                            {{ property.roomsCount }}
                                        </dd>
                                    </div>
                                </dl>
                                <span
                                    class="mt-8 inline-flex items-center gap-3 text-sm font-medium"
                                >
                                    View home & check dates
                                    <span
                                        class="grid size-10 place-items-center rounded-full bg-ink text-ivory transition duration-300 group-hover:translate-x-1 group-hover:bg-clay"
                                    >
                                        <ArrowRight class="size-4" />
                                    </span>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section
            id="how-it-works"
            class="relative overflow-hidden bg-sage text-ivory"
        >
            <div
                class="sn-grain pointer-events-none absolute inset-0 opacity-10"
            />
            <div
                class="pointer-events-none absolute -top-40 -right-40 size-[32rem] rounded-full bg-honey/20 blur-3xl"
            />
            <div class="relative mx-auto max-w-7xl px-5 py-24 sm:px-8 md:py-32">
                <div v-reveal class="max-w-2xl">
                    <p class="text-xs tracking-[0.25em] text-honey uppercase">
                        How it works
                    </p>
                    <h2
                        class="mt-4 font-display text-4xl font-light sm:text-5xl"
                    >
                        Three gentle steps to your stay
                    </h2>
                </div>
                <ol class="mt-16 grid gap-6 md:grid-cols-3">
                    <li
                        v-for="(step, index) in steps"
                        :key="step.title"
                        v-reveal="index * 140"
                        class="group relative rounded-[1.75rem] border border-ivory/15 bg-ivory/[0.06] p-8 backdrop-blur-sm transition duration-500 hover:-translate-y-1 hover:bg-ivory/10"
                    >
                        <span
                            class="absolute top-6 right-7 font-display text-6xl font-light text-ivory/15 italic"
                        >
                            0{{ index + 1 }}
                        </span>
                        <span
                            class="grid size-12 place-items-center rounded-2xl bg-honey text-ink"
                        >
                            <component :is="step.icon" class="size-5" />
                        </span>
                        <h3 class="mt-8 font-display text-2xl">
                            {{ step.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-ivory/75">
                            {{ step.text }}
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Photo band -->
        <section v-if="photoBand.length > 0" class="bg-ivory py-24 md:py-28">
            <div v-reveal class="mx-auto max-w-7xl px-5 sm:px-8">
                <h2 class="font-display text-3xl font-light sm:text-4xl">
                    A glimpse <em class="text-clay">inside</em>
                </h2>
            </div>
            <div
                class="mt-10 flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto px-5 pb-4 sm:px-8 lg:px-[max(2rem,calc((100vw-80rem)/2+2rem))]"
            >
                <Link
                    v-for="photo in photoBand"
                    :key="photo.id"
                    :href="show(photo.property.slug)"
                    class="group relative w-72 shrink-0 snap-start overflow-hidden rounded-[1.5rem] sm:w-80"
                >
                    <img
                        :src="photo.url"
                        :alt="photo.alt"
                        loading="lazy"
                        class="aspect-[3/4] w-full object-cover transition duration-[1.2s] group-hover:scale-105"
                    />
                    <div
                        class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/80 to-transparent p-5 pt-16 text-ivory"
                    >
                        <p class="font-display text-lg">
                            {{ photo.caption ?? photo.property.name }}
                        </p>
                        <p class="text-xs text-ivory/70">
                            {{ photo.property.name }}
                        </p>
                    </div>
                </Link>
            </div>
        </section>
    </div>
</template>
