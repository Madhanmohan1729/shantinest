<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    DoorOpen,
    ExternalLink,
    Image,
    Inbox,
    MapPin,
    Plus,
    Trash2,
} from '@lucide/vue';
import PropertyController from '@/actions/App/Http/Controllers/Admin/PropertyController';
import { Button } from '@/components/ui/button';
import { create, edit, index } from '@/routes/admin/properties';
import { show } from '@/routes/properties';

defineProps<{
    properties: {
        id: number;
        name: string;
        slug: string;
        location: string;
        isPublished: boolean;
        roomsCount: number;
        imagesCount: number;
        pendingRequestsCount: number;
        coverUrl: string | null;
    }[];
    can: { create: boolean; delete: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Properties', href: index() }],
    },
});

function remove(property: { slug: string; name: string }): void {
    if (
        confirm(
            `Delete ${property.name}? Its rooms, holds, requests and photos are deleted too. This cannot be undone.`,
        )
    ) {
        router.visit(PropertyController.destroy(property.slug));
    }
}
</script>

<template>
    <Head title="Properties" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl">Properties</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Details, photos and rooms shown on the website.
                </p>
            </div>
            <Button v-if="can.create" as-child>
                <Link :href="create()"><Plus /> New property</Link>
            </Button>
        </div>

        <div
            v-if="properties.length === 0"
            class="rounded-2xl border border-dashed p-16 text-center text-muted-foreground"
        >
            No properties yet.
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="property in properties"
                :key="property.id"
                class="group overflow-hidden rounded-2xl border bg-card"
            >
                <Link
                    :href="edit(property.slug)"
                    class="relative block aspect-[16/10] overflow-hidden bg-muted"
                >
                    <img
                        v-if="property.coverUrl"
                        :src="property.coverUrl"
                        :alt="property.name"
                        class="size-full object-cover transition duration-700 group-hover:scale-105"
                    />
                    <span
                        class="absolute top-3 left-3 rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="
                            property.isPublished
                                ? 'bg-sage text-ivory'
                                : 'bg-background/90 text-muted-foreground'
                        "
                    >
                        {{ property.isPublished ? 'Live' : 'Hidden' }}
                    </span>
                    <span
                        v-if="property.pendingRequestsCount"
                        class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-honey px-2.5 py-1 text-xs font-medium text-ink"
                    >
                        <Inbox class="size-3" />
                        {{ property.pendingRequestsCount }} pending
                    </span>
                </Link>
                <div class="p-5">
                    <Link
                        :href="edit(property.slug)"
                        class="font-display text-2xl hover:underline"
                        >{{ property.name }}</Link
                    >
                    <p
                        class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <MapPin class="size-3.5" /> {{ property.location }}
                    </p>
                    <div
                        class="mt-4 flex items-center gap-4 text-sm text-muted-foreground"
                    >
                        <span class="flex items-center gap-1.5"
                            ><DoorOpen class="size-4" />
                            {{ property.roomsCount }} rooms</span
                        >
                        <span class="flex items-center gap-1.5"
                            ><Image class="size-4" />
                            {{ property.imagesCount }} photos</span
                        >
                    </div>
                    <div class="mt-5 flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            as-child
                            class="flex-1"
                        >
                            <Link :href="edit(property.slug)">Manage</Link>
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <a
                                :href="show.url(property.slug)"
                                target="_blank"
                                rel="noopener"
                                ><ExternalLink /> View</a
                            >
                        </Button>
                        <Button
                            v-if="can.delete"
                            variant="ghost"
                            size="icon"
                            class="size-8 text-destructive"
                            aria-label="Delete property"
                            @click="remove(property)"
                        >
                            <Trash2 />
                        </Button>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>
