<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\MeetingRoom;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Subscription;

class BookingController extends Controller
{
    /**
     * Display the user's bookings 
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'upcoming'); // Default: upcoming

        $query = Booking::where('user_id', $user->id);

        if ($filter === 'past') {
            $query->where('start_time', '<', now())->orderBy('start_time', 'desc');
        } else {
            $query->where('start_time', '>=', now())->orderBy('start_time', 'asc');
        }

        $bookings = $query->paginate(10);

        return response()->json($bookings, 200);
    }

    /**
     * Store a new meeting room booking.
    */
    public function store(Request $request)
    {
        $request->validate([
            'meeting_room_id' => 'required|exists:meeting_rooms,id',
            'meeting_name' => 'required|string|max:255',
            'start_time' => 'required|date|after_or_equal:now',
            'duration' => 'required|in:30,60,90', // Ensure duration is one of these values
            'members' => 'required|integer|min:1'
        ]);
    
        $user = Auth::user();
        $startTime = Carbon::parse($request->start_time);
        $duration = (int) $request->duration; 
        $endDateTime = $startTime->copy()->addMinutes($duration); 
    
        $todayBookings = Booking::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
    
        $subscription = Subscription::where('user_id', $user->id)
            ->where('expires_at', '>', Carbon::now()) // Ensure it's still active
            ->with('plan') // Load the associated plan details
            ->first();
        
        $maxBookings = $subscription && $subscription->plan ? $subscription->plan->bookings_per_day : 3;
    
        if ($todayBookings >= $maxBookings) {
            return response()->json(['message' => 'You have reached your daily booking limit.'], 403);
        }
        $isBooked = Booking::where('meeting_room_id', $request->meeting_room_id)
            ->where(function ($query) use ($startTime, $endDateTime) {
                $query->whereBetween('start_time', [$startTime, $endDateTime])
                    ->orWhereRaw('? BETWEEN start_time AND ADDTIME(start_time, SEC_TO_TIME(duration * 60))', [$startTime]);
            })
            ->exists();
    
        if ($isBooked) {
            return response()->json(['message' => 'This meeting room is already booked for the selected time.'], 400);
        }

        $booking = Booking::create([
            'user_id' => $user->id,
            'meeting_room_id' => $request->meeting_room_id,
            'meeting_name' => $request->meeting_name,
            'start_time' => $startTime,
            'duration' => $duration,
            'members' => $request->members
        ]);
    
        return response()->json(['message' => 'Booking successful!', 'booking' => $booking], 201);
    }

    public function cancelBooking($id)
    {
        $user = auth()->user();

        $booking = Booking::where('id', $id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found or unauthorized'], 404);
        }

        $booking->delete();

        return response()->json(['message' => 'Booking canceled successfully'], 200);
    }
}
