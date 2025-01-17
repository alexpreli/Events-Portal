Event Management Portal

Description:
An application for organizing and participating in events.

Features:
    - Registration form for organizers (they require an invitation code and approval after registration).
    - Registration form for participants.
    - Login form for both organizers and participants.
    - Event creation by organizers.
    - Event modification and deletion by organizers.
    - Each user can register for events and view the events they have registered for.
    - Participant registration for events (users can sign up for events).
    - Calendar on the main page showing the number of scheduled events for each day.

Not Implemented:

    - Electronic ticket generation.
    - Sending electronic tickets via email.
    - Organizers can view users who have signed up for the events they created.
	- Listing of participants registered for each event created by the connected organizer.

Technologies Used:

    Frontend: React, PrimeReact, Bootstrap
    Backend: Laravel

Setup Instructions:

1. Install Server Dependencies (Laravel):

First, navigate to the Laravel backend directory and install the necessary PHP dependencies by running:

- cd Events-Portal/server
- composer install

After that, run the migrations to set up the database:

- php artisan migrate

2. Install Client Dependencies (React):

Navigate to the client directory and install the necessary JavaScript dependencies by running:

- cd Events-Portal/client
- npm install

3. Run Both Servers:

In the Events-Portal directory, run the following command to start the development servers:

- npm run dev

This will launch both servers: the backend server for the Laravel API and the frontend React development server.
