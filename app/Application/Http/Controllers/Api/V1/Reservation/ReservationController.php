<?php

namespace App\Application\Http\Controllers\Api\V1\Reservation;

use App\Application\Http\Controllers\Controller;
use App\Domain\Reservation\Models\Reservation;
use App\Domain\Floor\Models\Table;
use App\Support\Enums\ReservationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * List reservations.
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $query = Reservation::query()
            ->where('branch_id', $branchId)
            ->when($request->has('date'), fn($q) => $q->whereDate('reserved_at', $request->input('date')))
            ->when($request->boolean('today'), fn($q) => $q->today())
            ->when($request->has('status'), fn($q) => $q->status(ReservationStatus::from($request->input('status'))))
            ->when($request->has('table_id'), fn($q) => $q->where('table_id', $request->input('table_id')))
            ->with(['table.hall', 'customer'])
            ->orderBy('reserved_at');

        $reservations = $request->boolean('paginate', true)
            ? $query->paginate($request->input('per_page', 20))
            : $query->get();

        return response()->json([
            'data' => $reservations,
        ]);
    }

    /**
     * Get single reservation.
     */
    public function show(Reservation $reservation): JsonResponse
    {
        $reservation->load(['table.hall', 'customer']);

        return response()->json([
            'data' => $reservation,
        ]);
    }

    /**
     * Create reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'table_id' => 'required|exists:tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'guest_name' => 'required_without:customer_id|string|max:255',
            'guest_phone' => 'required_without:customer_id|string|max:20',
            'guest_count' => 'required|integer|min:1',
            'reserved_at' => 'required|date|after:now',
            'duration' => 'nullable|integer|min:30',
            'notes' => 'nullable|string',
            'source' => 'nullable|string|max:50',
        ]);

        // Check table availability
        $table = Table::findOrFail($request->input('table_id'));

        if ($request->input('guest_count') > $table->capacity) {
            return response()->json([
                'message' => 'Количество гостей превышает вместимость стола.',
            ], 422);
        }

        // Check for overlapping reservations
        $reservedAt = $request->date('reserved_at');
        $duration = $request->input('duration', 120);
        $endTime = $reservedAt->copy()->addMinutes($duration);

        $hasOverlap = Reservation::where('table_id', $table->id)
            ->whereNotIn('status', ['cancelled', 'no_show', 'completed'])
            ->where(function ($q) use ($reservedAt, $endTime) {
                $q->whereBetween('reserved_at', [$reservedAt, $endTime])
                    ->orWhere(function ($sq) use ($reservedAt, $endTime) {
                        $sq->where('reserved_at', '<=', $reservedAt)
                            ->whereRaw('DATE_ADD(reserved_at, INTERVAL duration MINUTE) >= ?', [$reservedAt]);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            return response()->json([
                'message' => 'На это время уже есть бронь.',
            ], 422);
        }

        $reservation = Reservation::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $request->input('branch_id'),
            'table_id' => $request->input('table_id'),
            'customer_id' => $request->input('customer_id'),
            'guest_name' => $request->input('guest_name'),
            'guest_phone' => $request->input('guest_phone'),
            'guest_count' => $request->input('guest_count'),
            'reserved_at' => $reservedAt,
            'duration' => $duration,
            'status' => ReservationStatus::PENDING,
            'notes' => $request->input('notes'),
            'source' => $request->input('source', 'phone'),
        ]);

        $reservation->load(['table.hall', 'customer']);

        return response()->json([
            'message' => 'Бронь успешно создана.',
            'data' => $reservation,
        ], 201);
    }

    /**
     * Update reservation.
     */
    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $request->validate([
            'table_id' => 'sometimes|exists:tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'guest_name' => 'sometimes|string|max:255',
            'guest_phone' => 'sometimes|string|max:20',
            'guest_count' => 'sometimes|integer|min:1',
            'reserved_at' => 'sometimes|date',
            'duration' => 'nullable|integer|min:30',
            'notes' => 'nullable|string',
        ]);

        $reservation->update($request->only([
            'table_id', 'customer_id', 'guest_name', 'guest_phone',
            'guest_count', 'reserved_at', 'duration', 'notes'
        ]));

        return response()->json([
            'message' => 'Бронь успешно обновлена.',
            'data' => $reservation,
        ]);
    }

    /**
     * Update reservation status.
     */
    public function updateStatus(Request $request, Reservation $reservation): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,seated,completed,cancelled,no_show',
        ]);

        $status = ReservationStatus::from($request->input('status'));

        switch ($status) {
            case ReservationStatus::CONFIRMED:
                $reservation->confirm();
                break;
            case ReservationStatus::SEATED:
                $reservation->seat();
                break;
            case ReservationStatus::COMPLETED:
                $reservation->complete();
                break;
            case ReservationStatus::CANCELLED:
                $reservation->cancel();
                break;
            case ReservationStatus::NO_SHOW:
                $reservation->markNoShow();
                break;
            default:
                $reservation->update(['status' => $status]);
        }

        return response()->json([
            'message' => 'Статус брони обновлён.',
            'data' => $reservation,
        ]);
    }

    /**
     * Get available tables for reservation.
     */
    public function availableTables(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'reserved_at' => 'required|date|after:now',
            'duration' => 'nullable|integer|min:30',
            'guest_count' => 'required|integer|min:1',
        ]);

        $reservedAt = $request->date('reserved_at');
        $duration = $request->input('duration', 120);
        $endTime = $reservedAt->copy()->addMinutes($duration);
        $guestCount = $request->input('guest_count');

        // Get tables with enough capacity
        $tables = Table::where('branch_id', $request->input('branch_id'))
            ->where('is_active', true)
            ->where('capacity', '>=', $guestCount)
            ->with('hall')
            ->get();

        // Filter out tables with overlapping reservations
        $availableTables = $tables->filter(function ($table) use ($reservedAt, $endTime) {
            return !Reservation::where('table_id', $table->id)
                ->whereNotIn('status', ['cancelled', 'no_show', 'completed'])
                ->where(function ($q) use ($reservedAt, $endTime) {
                    $q->whereBetween('reserved_at', [$reservedAt, $endTime])
                        ->orWhere(function ($sq) use ($reservedAt, $endTime) {
                            $sq->where('reserved_at', '<=', $reservedAt)
                                ->whereRaw('DATE_ADD(reserved_at, INTERVAL duration MINUTE) >= ?', [$reservedAt]);
                        });
                })
                ->exists();
        });

        return response()->json([
            'data' => $availableTables->values(),
        ]);
    }

    /**
     * Delete reservation.
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        if ($reservation->status === ReservationStatus::SEATED) {
            return response()->json([
                'message' => 'Нельзя удалить бронь с посаженным гостем.',
            ], 422);
        }

        $reservation->delete();

        return response()->json([
            'message' => 'Бронь успешно удалена.',
        ]);
    }
}
