import type { HeldPeriod, StorefrontRoom } from '@/types/property';

/**
 * Dates travel as "YYYY-MM-DD" strings and times as "YYYY-MM-DDTHH:mm" in the
 * property's local time, so they compare correctly as plain strings and never
 * shift with the visitor's timezone.
 */

export function toDateKey(date: Date): string {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
}

export function fromDateKey(key: string): Date {
    const [year, month, day] = key.split('-').map(Number);

    return new Date(year, month - 1, day);
}

export function addDays(key: string, days: number): string {
    const date = fromDateKey(key);
    date.setDate(date.getDate() + days);

    return toDateKey(date);
}

export function nightsBetween(checkIn: string, checkOut: string): number {
    return Math.round(
        (fromDateKey(checkOut).getTime() - fromDateKey(checkIn).getTime()) /
            86_400_000,
    );
}

export function overlaps(
    hold: HeldPeriod,
    startsAt: string,
    endsAt: string,
): boolean {
    return hold.startsAt < endsAt && hold.endsAt > startsAt;
}

/**
 * Whether a room is free from check-in on the first date to check-out on the
 * second, using the property's check-in and check-out times.
 */
export function isRoomFree(
    room: StorefrontRoom,
    checkIn: string,
    checkOut: string,
    checkInTime: string,
    checkOutTime: string,
): boolean {
    const startsAt = `${checkIn}T${checkInTime}`;
    const endsAt = `${checkOut}T${checkOutTime}`;

    return !room.holds.some((hold) => overlaps(hold, startsAt, endsAt));
}

/**
 * Whether every room is held on the night that starts on the given date, so
 * that night cannot be requested at all.
 */
export function isNightFullyHeld(
    rooms: StorefrontRoom[],
    date: string,
    checkInTime: string,
    checkOutTime: string,
): boolean {
    if (rooms.length === 0) {
        return false;
    }

    return rooms.every(
        (room) =>
            !isRoomFree(
                room,
                date,
                addDays(date, 1),
                checkInTime,
                checkOutTime,
            ),
    );
}

const longDate = new Intl.DateTimeFormat('en-IN', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
});

const shortDate = new Intl.DateTimeFormat('en-IN', {
    day: 'numeric',
    month: 'short',
});

const dateTime = new Intl.DateTimeFormat('en-IN', {
    day: 'numeric',
    month: 'short',
    hour: 'numeric',
    minute: '2-digit',
});

export function formatLongDate(key: string): string {
    return longDate.format(fromDateKey(key));
}

export function formatShortDate(key: string): string {
    return shortDate.format(fromDateKey(key));
}

/** "2026-11-13T14:00" as "13 Nov, 2:00 pm". */
export function formatDateTime(value: string): string {
    const [date, time] = value.split('T');
    const [hours, minutes] = time.split(':').map(Number);
    const parsed = fromDateKey(date);
    parsed.setHours(hours, minutes);

    return dateTime.format(parsed);
}
