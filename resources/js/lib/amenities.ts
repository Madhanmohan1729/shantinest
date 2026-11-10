import {
    AirVent,
    Armchair,
    ArrowUpDown,
    BatteryCharging,
    Car,
    CookingPot,
    Flame,
    KeyRound,
    Laptop,
    Refrigerator,
    ShowerHead,
    Sparkles,
    Tv,
    Users,
    Video,
    WashingMachine,
    Wifi,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';

const icons: Record<string, LucideIcon> = {
    wifi: Wifi,
    kitchen: CookingPot,
    air_conditioning: AirVent,
    tv: Tv,
    washing_machine: WashingMachine,
    fridge: Refrigerator,
    hot_water: ShowerHead,
    self_check_in: KeyRound,
    building_staff: Users,
    essentials: Sparkles,
    workspace: Laptop,
    parking: Car,
    lift: ArrowUpDown,
    power_backup: BatteryCharging,
    security_cameras: Video,
    smoke_alarm: Flame,
};

export function amenityIcon(key: string): LucideIcon {
    return icons[key] ?? Armchair;
}
