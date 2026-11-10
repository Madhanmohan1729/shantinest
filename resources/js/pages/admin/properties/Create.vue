<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PropertyForm from '@/components/admin/PropertyForm.vue';
import { create, index } from '@/routes/admin/properties';
import type { Amenity } from '@/types';

defineProps<{
    amenityOptions: Amenity[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Properties', href: index() },
            { title: 'New property', href: create() },
        ],
    },
});

const blank = {
    name: '',
    tagline: null,
    description: null,
    propertyType: 'Entire home',
    address: null,
    city: '',
    state: null,
    country: 'India',
    latitude: null,
    longitude: null,
    maxGuests: 4,
    bedrooms: 2,
    beds: 2,
    bathrooms: 2,
    amenities: ['wifi'],
    houseRules: [],
    checkInTime: '14:00',
    checkOutTime: '11:00',
    contactPhone: null,
    contactEmail: null,
    isPublished: false,
};
</script>

<template>
    <Head title="New property" />

    <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="font-display text-3xl">New property</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                You can add rooms and photos once it is created.
            </p>
        </div>
        <PropertyForm :property="blank" :amenity-options="amenityOptions" />
    </div>
</template>
