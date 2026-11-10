<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import PropertyController from '@/actions/App/Http/Controllers/Admin/PropertyController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { Amenity, PropertyFormValues } from '@/types';

const props = defineProps<{
    property: PropertyFormValues;
    amenityOptions: Amenity[];
    readonly?: boolean;
}>();

const route = props.property.slug
    ? PropertyController.update.form(props.property.slug)
    : PropertyController.store.form();
const textareaClass =
    'w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30';
</script>

<template>
    <Form
        v-bind="route"
        :options="{ preserveScroll: true }"
        v-slot="{ errors, processing }"
        class="grid gap-6"
    >
        <fieldset :disabled="readonly" class="grid gap-6">
            <section class="grid gap-5 rounded-2xl border bg-card p-5 md:p-6">
                <h2 class="font-medium">The basics</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="property.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slug">Web address</Label>
                        <div
                            class="flex items-center rounded-md border border-input pl-3 text-sm text-muted-foreground shadow-xs"
                        >
                            /stays/
                            <input
                                id="slug"
                                name="slug"
                                :value="property.slug ?? ''"
                                class="h-9 min-w-0 flex-1 bg-transparent px-1 text-foreground outline-none"
                                placeholder="made from the name"
                            />
                        </div>
                        <InputError :message="errors.slug" />
                    </div>
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="tagline">Tagline</Label>
                        <Input
                            id="tagline"
                            name="tagline"
                            :default-value="property.tagline ?? ''"
                            placeholder="Bright, cute, cheerful and welcoming"
                        />
                        <InputError :message="errors.tagline" />
                    </div>
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            :class="textareaClass"
                            :value="property.description ?? ''"
                        />
                        <p class="text-xs text-muted-foreground">
                            Leave a blank line between paragraphs.
                        </p>
                        <InputError :message="errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="property_type">Type</Label>
                        <Input
                            id="property_type"
                            name="property_type"
                            :default-value="property.propertyType ?? ''"
                            placeholder="Entire rental unit"
                        />
                        <InputError :message="errors.property_type" />
                    </div>
                    <label
                        class="flex items-center gap-3 self-end rounded-md border px-3 py-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            :checked="property.isPublished"
                            class="size-4 accent-[var(--color-clay)]"
                        />
                        Show this property on the website
                    </label>
                </div>
            </section>

            <section class="grid gap-5 rounded-2xl border bg-card p-5 md:p-6">
                <h2 class="font-medium">Size & stay times</h2>
                <div class="grid grid-cols-2 gap-5 md:grid-cols-6">
                    <div
                        v-for="field in [
                            {
                                name: 'max_guests',
                                label: 'Max guests',
                                value: property.maxGuests,
                                min: 1,
                            },
                            {
                                name: 'bedrooms',
                                label: 'Bedrooms',
                                value: property.bedrooms,
                                min: 0,
                            },
                            {
                                name: 'beds',
                                label: 'Beds',
                                value: property.beds,
                                min: 0,
                            },
                            {
                                name: 'bathrooms',
                                label: 'Bathrooms',
                                value: property.bathrooms,
                                min: 0,
                            },
                        ]"
                        :key="field.name"
                        class="grid gap-2"
                    >
                        <Label :for="field.name">{{ field.label }}</Label>
                        <Input
                            :id="field.name"
                            :name="field.name"
                            type="number"
                            :min="field.min"
                            :default-value="field.value"
                            required
                        />
                        <InputError :message="errors[field.name]" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="check_in_time">Check-in</Label>
                        <Input
                            id="check_in_time"
                            name="check_in_time"
                            type="time"
                            :default-value="property.checkInTime"
                            required
                        />
                        <InputError :message="errors.check_in_time" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="check_out_time">Check-out</Label>
                        <Input
                            id="check_out_time"
                            name="check_out_time"
                            type="time"
                            :default-value="property.checkOutTime"
                            required
                        />
                        <InputError :message="errors.check_out_time" />
                    </div>
                </div>
            </section>

            <section class="grid gap-5 rounded-2xl border bg-card p-5 md:p-6">
                <h2 class="font-medium">Location</h2>
                <div class="grid gap-5 md:grid-cols-3">
                    <div class="grid gap-2 md:col-span-3">
                        <Label for="address">Area or street</Label>
                        <Input
                            id="address"
                            name="address"
                            :default-value="property.address ?? ''"
                        />
                        <InputError :message="errors.address" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="city">City</Label>
                        <Input
                            id="city"
                            name="city"
                            :default-value="property.city"
                            required
                        />
                        <InputError :message="errors.city" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="state">State</Label>
                        <Input
                            id="state"
                            name="state"
                            :default-value="property.state ?? ''"
                        />
                        <InputError :message="errors.state" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="country">Country</Label>
                        <Input
                            id="country"
                            name="country"
                            :default-value="property.country"
                            required
                        />
                        <InputError :message="errors.country" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="latitude">Latitude</Label>
                        <Input
                            id="latitude"
                            name="latitude"
                            :default-value="property.latitude ?? ''"
                            placeholder="17.4659"
                        />
                        <InputError :message="errors.latitude" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="longitude">Longitude</Label>
                        <Input
                            id="longitude"
                            name="longitude"
                            :default-value="property.longitude ?? ''"
                            placeholder="78.3668"
                        />
                        <InputError :message="errors.longitude" />
                    </div>
                    <p class="self-end pb-2 text-xs text-muted-foreground">
                        Used for the map. Guests only see the area.
                    </p>
                </div>
            </section>

            <section class="grid gap-5 rounded-2xl border bg-card p-5 md:p-6">
                <h2 class="font-medium">Amenities</h2>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                    <label
                        v-for="amenity in amenityOptions"
                        :key="amenity.key"
                        class="flex items-center gap-2.5 rounded-md border px-3 py-2 text-sm has-[:checked]:border-clay/50 has-[:checked]:bg-clay/5"
                    >
                        <input
                            type="checkbox"
                            name="amenities[]"
                            :value="amenity.key"
                            :checked="property.amenities.includes(amenity.key)"
                            class="size-4 accent-[var(--color-clay)]"
                        />
                        {{ amenity.label }}
                    </label>
                </div>
                <InputError :message="errors.amenities" />
            </section>

            <section
                class="grid gap-5 rounded-2xl border bg-card p-5 md:grid-cols-2 md:p-6"
            >
                <div class="grid gap-2">
                    <Label for="house_rules">House rules</Label>
                    <textarea
                        id="house_rules"
                        name="house_rules"
                        rows="5"
                        :class="textareaClass"
                        :value="property.houseRules.join('\n')"
                    />
                    <p class="text-xs text-muted-foreground">
                        One rule per line.
                    </p>
                    <InputError :message="errors.house_rules" />
                </div>
                <div class="grid content-start gap-5">
                    <div class="grid gap-2">
                        <Label for="contact_phone">Contact phone</Label>
                        <Input
                            id="contact_phone"
                            name="contact_phone"
                            :default-value="property.contactPhone ?? ''"
                        />
                        <InputError :message="errors.contact_phone" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="contact_email">Contact email</Label>
                        <Input
                            id="contact_email"
                            name="contact_email"
                            type="email"
                            :default-value="property.contactEmail ?? ''"
                        />
                        <InputError :message="errors.contact_email" />
                    </div>
                </div>
            </section>
        </fieldset>

        <div v-if="!readonly" class="sticky bottom-4 z-10 flex justify-end">
            <Button
                type="submit"
                size="lg"
                :disabled="processing"
                class="shadow-lg"
            >
                <Spinner v-if="processing" />
                {{ property.slug ? 'Save details' : 'Create property' }}
            </Button>
        </div>
    </Form>
</template>
