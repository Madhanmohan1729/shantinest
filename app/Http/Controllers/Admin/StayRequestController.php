<?php

namespace App\Http\Controllers\Admin;

use App\Actions\StayRequests\ApproveStayRequest;
use App\Enums\StayRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\StayRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StayRequestController extends Controller
{
    /**
     * List stay requests, newest first, optionally by status.
     */
    public function index(Request $request): Response
    {
        $status = StayRequestStatus::tryFrom((string) $request->query('status', 'pending'));
        $property = $request->query('property');

        $stayRequests = StayRequest::query()
            ->with(['property', 'rooms', 'responder'])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($property !== null, fn ($query) => $query->where('property_id', (int) $property))
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (StayRequest $stayRequest): array => [
                'id' => $stayRequest->id,
                'reference' => $stayRequest->reference,
                'name' => $stayRequest->name,
                'email' => $stayRequest->email,
                'phone' => $stayRequest->phone,
                'guests' => $stayRequest->guests,
                'checkIn' => $stayRequest->check_in->format('Y-m-d'),
                'checkOut' => $stayRequest->check_out->format('Y-m-d'),
                'nights' => $stayRequest->nights(),
                'message' => $stayRequest->message,
                'status' => $stayRequest->status->value,
                'statusLabel' => $stayRequest->status->label(),
                'adminNote' => $stayRequest->admin_note,
                'respondedBy' => $stayRequest->responder?->name,
                'respondedAt' => $stayRequest->responded_at?->toIso8601String(),
                'createdAt' => $stayRequest->created_at?->toIso8601String(),
                'property' => ['id' => $stayRequest->property->id, 'name' => $stayRequest->property->name, 'slug' => $stayRequest->property->slug],
                'rooms' => $stayRequest->rooms->map(fn (Room $room): array => ['id' => $room->id, 'name' => $room->name])->all(),
            ]);

        return Inertia::render('admin/stay-requests/Index', [
            'stayRequests' => $stayRequests,
            'filters' => ['status' => $status?->value ?? 'all'],
            'counts' => StayRequest::query()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'can' => [
                'manage' => $request->user()->can('manage stay requests'),
                'viewHolds' => $request->user()->can('view holds'),
            ],
        ]);
    }

    /**
     * Approve a request and hold its rooms for the stay.
     */
    public function approve(Request $request, StayRequest $stayRequest, ApproveStayRequest $approveStayRequest): RedirectResponse
    {
        $validated = $request->validate(['admin_note' => ['nullable', 'string', 'max:2000']]);

        $approveStayRequest->handle($stayRequest, $request->user(), $validated['admin_note'] ?? null);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':reference approved. The rooms are now held.', ['reference' => $stayRequest->reference])]);

        return back();
    }

    /**
     * Decline a pending request.
     */
    public function decline(Request $request, StayRequest $stayRequest): RedirectResponse
    {
        $validated = $request->validate(['admin_note' => ['nullable', 'string', 'max:2000']]);

        if ($stayRequest->status !== StayRequestStatus::Pending) {
            return back()->withErrors(['status' => __('This request has already been answered.')]);
        }

        $stayRequest->update([
            'status' => StayRequestStatus::Declined,
            'admin_note' => $validated['admin_note'] ?? null,
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':reference declined.', ['reference' => $stayRequest->reference])]);

        return back();
    }
}
