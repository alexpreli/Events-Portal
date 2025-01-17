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

1. Run Migrations in Laravel:

First, ensure you have Laravel set up and configured. Then, run the migrations using the following command:
	php artisan migrate

2. Set Up the Frontend:

Navigate to the events_portal directory and execute the following command to install dependencies and run the development servers:
	npm run dev
