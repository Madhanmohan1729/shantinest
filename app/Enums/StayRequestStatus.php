<?php

namespace App\Enums;

enum StayRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';

    /**
     * The label shown to admins and guests.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Approved'),
            self::Declined => __('Declined'),
        };
    }
}
