export type PropertyPhoto = {
    id: number;
    url: string;
    caption: string | null;
    alt: string;
    roomId: number | null;
};

export type PropertySummary = {
    id: number;
    name: string;
    slug: string;
    tagline: string | null;
    propertyType: string | null;
    location: string;
    city: string;
    maxGuests: number;
    bedrooms: number;
    beds: number;
    bathrooms: number;
};

export type PropertyCard = PropertySummary & {
    roomsCount: number;
    images: PropertyPhoto[];
};

export type Amenity = {
    key: string;
    label: string;
};

export type PropertyDetails = PropertySummary & {
    description: string | null;
    address: string | null;
    latitude: number | null;
    longitude: number | null;
    amenities: Amenity[];
    houseRules: string[];
    checkInTime: string;
    checkOutTime: string;
    checkInLabel: string;
    checkOutLabel: string;
    contactPhone: string | null;
    contactEmail: string | null;
};

/** Times are local wall-clock strings such as "2026-11-13T14:00". */
export type HeldPeriod = {
    startsAt: string;
    endsAt: string;
};

export type StorefrontRoom = {
    id: number;
    name: string;
    description: string | null;
    bedType: string | null;
    maxGuests: number;
    features: string[];
    images: PropertyPhoto[];
    holds: HeldPeriod[];
};

export type AdminHold = {
    id: number;
    roomId: number;
    startsAt: string;
    endsAt: string;
    guestName: string | null;
    note: string | null;
    stayRequestReference: string | null;
};

export type AdminRoom = {
    id: number;
    name: string;
    description: string | null;
    bedType: string | null;
    maxGuests: number;
    features: string[];
    isActive: boolean;
};

export type AdminPhoto = PropertyPhoto & {
    fileName: string;
};

export type StayRequestStatus = 'pending' | 'approved' | 'declined';

export type HoldDraft = {
    hold: AdminHold | null;
    roomId: number | null;
    startsAt: string;
    endsAt: string;
};

export type PropertyFormValues = {
    slug?: string;
    name: string;
    tagline: string | null;
    description: string | null;
    propertyType: string | null;
    address: string | null;
    city: string;
    state: string | null;
    country: string;
    latitude: string | number | null;
    longitude: string | number | null;
    maxGuests: number;
    bedrooms: number;
    beds: number;
    bathrooms: number;
    amenities: string[];
    houseRules: string[];
    checkInTime: string;
    checkOutTime: string;
    contactPhone: string | null;
    contactEmail: string | null;
    isPublished: boolean;
};
