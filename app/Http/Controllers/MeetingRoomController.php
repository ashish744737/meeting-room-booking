<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeetingRoom;
use Carbon\Carbon;

class MeetingRoomController extends Controller
{
    /**
     * Get all meeting rooms.
     */
    public function index()
    {
        $meetingRooms = MeetingRoom::all();
        return response()->json($meetingRooms, 200);
    }

    /**
     * Get available meeting rooms based on date, time, duration, and members.
     */

     public function availableRooms(Request $request)
     {
         // Validate request with `start_time`
         $request->validate([
             'start_time' => 'required|date|after_or_equal:today',
             'duration' => 'required|in:30,60,90',
             'members' => 'required|integer|min:1'
         ]);
     
         // Convert start_time to Carbon instance
         $startDateTime = Carbon::parse($request->start_time);
         $endDateTime = $startDateTime->copy()->addMinutes((int) $request->duration);
     
         // Get all rooms that meet the capacity requirement
         $rooms = MeetingRoom::where('capacity', '>=', $request->members)->get();
     
         $availableRooms = $rooms->filter(function ($room) use ($startDateTime, $endDateTime) {
             $isBooked = $room->bookings()
                 ->where(function ($query) use ($startDateTime, $endDateTime) {
                     $query->whereBetween('start_time', [$startDateTime, $endDateTime])
                         ->orWhereRaw('? BETWEEN start_time AND ADDTIME(start_time, SEC_TO_TIME(duration * 60))', [$startDateTime]);
                 })
                 ->exists();
     
             return !$isBooked;
         });
     
         return response()->json($availableRooms->values(), 200);
     }
    
}
