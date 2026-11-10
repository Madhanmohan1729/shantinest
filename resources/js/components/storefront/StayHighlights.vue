<script setup lang="ts">
import {
    AirVent,
    Archive,
    Building2,
    Car,
    CookingPot,
    Droplets,
    FlaskConical,
    MapPin,
    Refrigerator,
    Sofa,
    TrainFront,
    Trees,
    UtensilsCrossed,
} from '@lucide/vue';
import logoUrl from '@/assets/brand/santhi-nest-logo.svg';

/** Distances that make the location stand out, shown as large figures. */
const locationStats = [
    {
        icon: TrainFront,
        figure: '300 m',
        label: 'from Miyapur Metro Station',
        note: 'Ideally located, a short walk away',
    },
    {
        icon: MapPin,
        figure: 'Close',
        label: 'to KPHB',
        note: 'Shopping, food and daily needs nearby',
    },
    {
        icon: Building2,
        figure: '4–5 km',
        label: 'to HITEC City',
        note: 'An easy commute to the tech hub',
    },
];

const furnishings = [
    { icon: AirVent, label: 'Air-conditioned' },
    { icon: Refrigerator, label: 'Fridge' },
    { icon: Droplets, label: 'Water purifier' },
    { icon: CookingPot, label: 'Kitchen' },
    { icon: Sofa, label: 'Sofas' },
    { icon: Archive, label: 'Cupboards' },
    { icon: UtensilsCrossed, label: 'Dining table' },
];

const features = [
    {
        icon: Trees,
        title: 'Peaceful community',
        text: 'Calm, green surroundings and quiet neighbours, so every evening feels restful.',
    },
    {
        icon: FlaskConical,
        title: 'Pharma companies nearby',
        text: 'Several pharmaceutical companies are close by, ideal for professionals on assignment.',
    },
    {
        icon: Car,
        title: 'Assured car parking',
        text: 'A guaranteed parking spot close to the building for every resident.',
    },
];
</script>

<template>
    <section id="highlights" class="relative overflow-hidden bg-sage-soft/60">
        <div
            class="pointer-events-none absolute -top-32 -left-32 size-[28rem] rounded-full bg-honey/15 blur-3xl"
        />
        <div class="relative mx-auto max-w-7xl px-5 py-24 sm:px-8 md:py-32">
            <div
                v-reveal
                class="grid items-center gap-10 lg:grid-cols-[auto_1fr] lg:gap-16"
            >
                <div
                    class="mx-auto w-48 rounded-[2rem] bg-white p-5 shadow-[0_30px_70px_-35px_rgba(29,26,22,0.45)] ring-1 ring-ink/5 sm:w-56 lg:mx-0"
                >
                    <img
                        :src="logoUrl"
                        alt="Santhi Nest Service Apartments — Stay peacefully"
                        class="w-full"
                        loading="lazy"
                    />
                </div>
                <div class="text-center lg:text-left">
                    <p class="text-xs tracking-[0.25em] text-clay uppercase">
                        Why Santhi Nest
                    </p>
                    <h2
                        class="mt-4 font-display text-4xl leading-tight font-light text-balance sm:text-5xl"
                    >
                        Peaceful living, minutes from
                        <em class="text-clay">everything</em>.
                    </h2>
                    <p
                        class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-ink-soft lg:mx-0"
                    >
                        Fully furnished service apartments in a quiet Miyapur
                        neighbourhood, with the metro at your doorstep and
                        Hyderabad's business hubs close by.
                    </p>
                </div>
            </div>

            <!-- Location -->
            <ul class="mt-16 grid gap-5 md:grid-cols-3">
                <li
                    v-for="(stat, index) in locationStats"
                    :key="stat.label"
                    v-reveal="index * 120"
                    class="group relative overflow-hidden rounded-[1.75rem] bg-ink p-8 text-ivory shadow-[0_30px_70px_-40px_rgba(29,26,22,0.7)]"
                >
                    <component
                        :is="stat.icon"
                        class="absolute -right-4 -bottom-4 size-32 text-ivory/[0.06] transition-transform duration-700 group-hover:scale-110"
                    />
                    <span
                        class="grid size-11 place-items-center rounded-2xl bg-honey text-ink"
                    >
                        <component :is="stat.icon" class="size-5" />
                    </span>
                    <p
                        class="mt-8 font-display text-5xl leading-none font-light text-honey"
                    >
                        {{ stat.figure }}
                    </p>
                    <p class="mt-3 font-display text-xl">{{ stat.label }}</p>
                    <p class="mt-2 text-sm text-ivory/60">{{ stat.note }}</p>
                </li>
            </ul>

            <!-- Apartment & community -->
            <div class="mt-5 grid gap-5 lg:grid-cols-[1.4fr_1fr]">
                <div
                    v-reveal
                    class="rounded-[1.75rem] bg-white p-8 shadow-[0_30px_70px_-45px_rgba(29,26,22,0.45)] ring-1 ring-ink/5 sm:p-10"
                >
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-clay/10 px-3 py-1 text-xs font-medium tracking-wide text-clay uppercase"
                    >
                        <Sofa class="size-3.5" />
                        Every flat
                    </span>
                    <h3
                        class="mt-5 font-display text-3xl font-light text-balance"
                    >
                        Fully furnished &amp;
                        <em class="text-clay">air-conditioned</em>
                    </h3>
                    <p
                        class="mt-3 max-w-lg text-sm leading-relaxed text-ink-soft"
                    >
                        Move in with just your suitcase. Each apartment comes
                        ready with everything you need for a comfortable stay.
                    </p>
                    <ul class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <li
                            v-for="item in furnishings"
                            :key="item.label"
                            class="flex items-center gap-2.5 rounded-2xl bg-ivory px-3.5 py-3 text-sm ring-1 ring-ink/5"
                        >
                            <component
                                :is="item.icon"
                                class="size-4 shrink-0 text-sage"
                            />
                            {{ item.label }}
                        </li>
                    </ul>
                </div>

                <ul class="grid gap-5">
                    <li
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        v-reveal="index * 120"
                        class="flex gap-5 rounded-[1.75rem] bg-white p-6 shadow-[0_30px_70px_-45px_rgba(29,26,22,0.45)] ring-1 ring-ink/5"
                    >
                        <span
                            class="grid size-12 shrink-0 place-items-center rounded-2xl bg-sage text-ivory"
                        >
                            <component :is="feature.icon" class="size-5" />
                        </span>
                        <div>
                            <h3 class="font-display text-xl">
                                {{ feature.title }}
                            </h3>
                            <p
                                class="mt-1 text-sm leading-relaxed text-ink-soft"
                            >
                                {{ feature.text }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
