<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventController extends \Illuminate\Routing\Controller
{
    private function createEventsTableIfNotExists()
    {
        if (!DB::getPdo()->query("SHOW TABLES LIKE 'events'")->fetch()) {
            DB::getPdo()->exec("
                CREATE TABLE events (
                    id BIGINT UNSIGNED PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    location VARCHAR(255) NOT NULL,
                    start_date DATETIME NOT NULL,
                    end_date DATETIME NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    capacity INT NOT NULL,
                    organizer_name VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
        }
    }

    public function store(Request $request)
    {
        $this->createEventsTableIfNotExists();

        $validated = $request->validate([
            'id' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'organizerName' => 'required|string|max:255'
        ]);

        try {
            $eventId = DB::table('events')->insertGetId([
                'id' => $validated['id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'start_date' => $validated['startDate'],
                'end_date' => $validated['endDate'],
                'price' => $validated['price'],
                'capacity' => $validated['capacity'],
                'organizer_name' => $validated['organizerName']
            ]);

            return response()->json([
                'message' => 'Event created successfully',
                'eventId' => $eventId
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'organizerName' => 'required|string|max:255'
        ]);

        try {
            $updated = DB::table('events')
                ->where('id', $validated['id'])
                ->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'location' => $validated['location'],
                    'start_date' => $validated['startDate'],
                    'end_date' => $validated['endDate'],
                    'price' => $validated['price'],
                    'capacity' => $validated['capacity'],
                    'organizer_name' => $validated['organizerName']
                ]);

            if ($updated) {
                return response()->json([
                    'message' => 'Event updated successfully'
                ]);
            }

            return response()->json([
                'message' => 'Event not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:events,id'
        ]);

        try {
            $deleted = DB::table('events')
                ->where('id', $validated['id'])
                ->delete();

            if ($deleted) {
                return response()->json([
                    'message' => 'Event deleted successfully'
                ]);
            }

            return response()->json([
                'message' => 'Event not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $this->createEventsTableIfNotExists();
            
            $events = DB::table('events')
                ->select('id', 'name', 'description', 'location', 'start_date', 'end_date', 'price', 'capacity', 'organizer_name')
                ->orderBy('start_date')
                ->get();

            $groupedEvents = [];
            foreach ($events as $event) {
                $monthYear = date('F Y', strtotime($event->start_date));
                
                if (!isset($groupedEvents[$monthYear])) {
                    $groupedEvents[$monthYear] = [
                        'label' => $monthYear,
                        'code' => date('Ym', strtotime($event->start_date)),
                        'items' => []
                    ];
                }

                $groupedEvents[$monthYear]['items'][] = [
                    'label' => $event->name,
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'location' => $event->location,
                    'startDate' => date('d M Y h:i A', strtotime($event->start_date)),
                    'endDate' => date('d M Y h:i A', strtotime($event->end_date)),
                    'price' => $event->price,
                    'capacity' => $event->capacity,
                    'organizerName' => $event->organizer_name
                ];
            }

            $groupedEvents = array_values($groupedEvents);

            return response()->json($groupedEvents);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registerForEvent(Request $request)
    {
        try {
            // validate request
            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'user_id' => 'required|exists:users,id'
            ]);

            // obtinem userul pe baza id-ului trimis in request
            $user = DB::table('users')->where('id', $validated['user_id'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $userEvents = json_decode($user->user_events ?? '[]', true);

            // verificam daca userul este deja inregistrat la eveniment
            if (in_array($validated['event_id'], $userEvents)) {
                return response()->json([
                    'message' => 'Already registered for this event'
                ], 400);
            }

            $userEvents[] = $validated['event_id'];

            DB::table('users')
                ->where('id', $user->id)
                ->update(['user_events' => json_encode($userEvents)]);

            return response()->json([
                'message' => 'Successfully registered for event'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register for event',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getUserEvents(Request $request)
    {
        try {
            $this->createEventsTableIfNotExists();
            
            $userId = $request->user_id;
            
            $user = DB::table('users')->where('id', $userId)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $userEventIds = json_decode($user->user_events ?? '[]', true);
            
            if (empty($userEventIds)) {
                return response()->json([]);
            }

            // get events details
            $events = DB::table('events')
                ->select('id', 'name', 'description', 'location', 'start_date', 'end_date', 'price', 'capacity', 'organizer_name')
                ->whereIn('id', $userEventIds)
                ->orderBy('start_date')
                ->get();

            $groupedEvents = [];
            foreach ($events as $event) {
                $monthYear = date('F Y', strtotime($event->start_date));
                
                if (!isset($groupedEvents[$monthYear])) {
                    $groupedEvents[$monthYear] = [
                        'label' => $monthYear,
                        'code' => date('Ym', strtotime($event->start_date)),
                        'items' => []
                    ];
                }

                $groupedEvents[$monthYear]['items'][] = [
                    'label' => $event->name,
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'location' => $event->location,
                    'startDate' => date('d M Y h:i A', strtotime($event->start_date)),
                    'endDate' => date('d M Y h:i A', strtotime($event->end_date)),
                    'price' => $event->price,
                    'capacity' => $event->capacity,
                    'organizerName' => $event->organizer_name
                ];
            }

            return response()->json(array_values($groupedEvents));
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOrganizerEvents(Request $request)
    {
        try {
            $this->createEventsTableIfNotExists();
            
            $userId = $request->user_id;
            
            $user = DB::table('organizers')->where('id', $userId)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Organizer not found'
                ], 404);
            }

            $userEventIds = json_decode($user->user_events ?? '[]', true);
            
            if (empty($userEventIds)) {
                return response()->json([]);
            }

            // get events details
            $events = DB::table('events')
                ->select('id', 'name', 'description', 'location', 'start_date', 'end_date', 'price', 'capacity', 'organizer_name')
                ->whereIn('id', $userEventIds)
                ->orderBy('start_date')
                ->get();

            $groupedEvents = [];
            foreach ($events as $event) {
                $monthYear = date('F Y', strtotime($event->start_date));
                
                if (!isset($groupedEvents[$monthYear])) {
                    $groupedEvents[$monthYear] = [
                        'label' => $monthYear,
                        'code' => date('Ym', strtotime($event->start_date)),
                        'items' => []
                    ];
                }

                $groupedEvents[$monthYear]['items'][] = [
                    'label' => $event->name,
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'location' => $event->location,
                    'startDate' => date('d M Y h:i A', strtotime($event->start_date)),
                    'endDate' => date('d M Y h:i A', strtotime($event->end_date)),
                    'price' => $event->price,
                    'capacity' => $event->capacity,
                    'organizerName' => $event->organizer_name
                ];
            }

            return response()->json(array_values($groupedEvents));
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getEventsWithParticipants(Request $request)
    {
        try {
            $this->createEventsTableIfNotExists();
            
            $organizerId = $request->user_id;
            
            // Get all events created by this organizer
            $events = DB::table('events')
                ->select('id', 'name', 'description', 'location', 'start_date', 'end_date', 'price', 'capacity', 'organizer_name')
                ->where('organizer_name', $organizerId)
                ->orderBy('start_date')
                ->get();

            // Get all users
            $users = DB::table('users')->get();
            
            $eventsWithParticipants = [];
            
            foreach ($events as $event) {
                $participants = [];
                
                // Check each user's events array for this event ID
                foreach ($users as $user) {
                    $userEvents = json_decode($user->user_events ?? '[]', true);
                    if (in_array($event->id, $userEvents)) {
                        $participants[] = [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email
                        ];
                    }
                }
                
                $eventsWithParticipants[] = [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'startDate' => date('d M Y h:i A', strtotime($event->start_date)),
                        'endDate' => date('d M Y h:i A', strtotime($event->end_date)),
                    ],
                    'participants' => $participants
                ];
            }

            return response()->json($eventsWithParticipants);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch events with participants',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getEventsPerDay()
{
    try {
        $this->createEventsTableIfNotExists();

        $eventCount = DB::table('events')->count();
        
        if ($eventCount == 0) {
            return response()->json([
                'message' => 'No events found'
            ], 404);
        }

        $events = DB::table('events')
            ->select('id', 'name', 'start_date', 'end_date')
            ->orderBy('start_date')
            ->get();

        $eventsPerDay = [];

        foreach ($events as $event) {
            $startDate = strtotime($event->start_date);
            $endDate = strtotime($event->end_date);

            while ($startDate <= $endDate) {
                $dateKey = date('Y-m-d', $startDate);  // Format: YYYY-MM-DD
                if (!isset($eventsPerDay[$dateKey])) {
                    $eventsPerDay[$dateKey] = 0;
                }
                $eventsPerDay[$dateKey]++;
                $startDate = strtotime("+1 day", $startDate);
            }
        }

        return response()->json(['eventsPerDay' => $eventsPerDay]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to fetch events per day',
            'error' => $e->getMessage()
        ], 500);
    }
}



}