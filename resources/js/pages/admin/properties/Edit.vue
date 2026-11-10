<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import { setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import PropertyForm from '@/components/admin/PropertyForm.vue';
import PropertyGallery from '@/components/admin/PropertyGallery.vue';
import RoomsManager from '@/components/admin/RoomsManager.vue';
import { Button } from '@/components/ui/button';
import { edit, index } from '@/routes/admin/properties';
import { show } from '@/routes/properties';
import type {
    AdminPhoto,
    AdminRoom,
    Amenity,
    PropertyFormValues,
} from '@/types';

const props = defineProps<{
    property: PropertyFormValues & { id: number; slug: string };
    images: AdminPhoto[];
    rooms: AdminRoom[];
    amenityOptions: Amenity[];
    maxKilobytes: number;
    maxFiles: number;
    can: {
        update: boolean;
        delete: boolean;
        manageImages: boolean;
        manageRooms: boolean;
    };
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Properties', href: index() },
        { title: props.property.name, href: edit(props.property.slug) },
    ],
});

const tab = ref<'details' | 'photos' | 'rooms'>('photos');
</script>

<template>
    <Head :title="property.name" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl">{{ property.name }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        property.isPublished
                            ? 'Live on the website'
                            : 'Hidden from the website'
                    }}
                    · /stays/{{ property.slug }}
                </p>
            </div>
            <Button variant="outline" as-child>
                <a
                    :href="show.url(property.slug)"
                    target="_blank"
                    rel="noopener"
                    ><ExternalLink /> View on website</a
                >
            </Button>
        </div>

        <nav
            class="flex gap-1 rounded-full border bg-card p-1 text-sm sm:w-fit"
        >
            <button
                v-for="item in [
                    { value: 'photos', label: `Photos (${images.length})` },
                    { value: 'rooms', label: `Rooms (${rooms.length})` },
                    { value: 'details', label: 'Details' },
                ] as const"
                :key="item.value"
                type="button"
                class="flex-1 rounded-full px-4 py-2 transition sm:flex-none"
                :class="
                    tab === item.value
                        ? 'bg-foreground text-background'
                        : 'hover:bg-muted'
                "
                @click="tab = item.value"
            >
                {{ item.label }}
            </button>
        </nav>

        <PropertyGallery
            v-show="tab === 'photos'"
            :property-slug="property.slug"
            :images="images"
            :rooms="rooms"
            :max-kilobytes="maxKilobytes"
            :max-files="maxFiles"
            :can-manage="can.manageImages"
        />
        <RoomsManager
            v-show="tab === 'rooms'"
            :property-slug="property.slug"
            :rooms="rooms"
            :images="images"
            :can-manage="can.manageRooms"
        />
        <PropertyForm
            v-show="tab === 'details'"
            :key="property.slug"
            :property="property"
            :amenity-options="amenityOptions"
            :readonly="!can.update"
        />
    </div>
</template>
