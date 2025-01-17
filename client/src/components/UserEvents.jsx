import React, { useState, useEffect, useRef } from 'react';
import { ListBox } from 'primereact/listbox';
import { Toast } from 'primereact/toast';
import { useAuth } from './AuthContext';
import './styles/EventsOverview.css';

function UserEvents() {
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const toast = useRef(null);
    const { token } = useAuth();
    const [isOrganizer, setIsOrganizer] = useState(false);


    let url = '/events/userEvents';
    useEffect(() => {
        const storedUser = localStorage.getItem('user');
        
        if (storedUser) {
            try {
                const parsedUser = JSON.parse(storedUser);
                setIsOrganizer(parsedUser.is_organizer);

                if (isOrganizer)
                    url = '/events/organizerEvents';
                fetchUserEvents(parsedUser);
            } catch (error) {
                console.error('Failed to parse user data from localStorage:', error);
            }
        }
    }, []);

    const fetchUserEvents = async (user) => {
        // clear listbox
        setEvents([]);
        setSelectedEvent(null);

        try {
            const response = await fetch(`http://localhost:8000` + url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    user_id: user.id
                })
            });

            if (!response.ok) {
                throw new Error('Failed to fetch events');
            }

            const userEvents = await response.json();
            setEvents(userEvents);
        } catch (error) {
            setError(error.message);
            console.error('Error fetching user events:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to load your events',
                life: 3000
            });
        } finally {
            setLoading(false);
        }
    };

    const itemTemplate = (item) => {
        return (
            <div className="flex flex-column gap-2 p-3">
                <div className="id">ID: {item.id}</div>
                <div className="font-bold">{item.name}</div>
                <div className="text-sm">Description: {item.description}</div>
                <div className="text-sm">Location: {item.location}</div>
                <div className="text-sm">Start Date: {item.startDate}</div>
                <div className="text-sm">End Date: {item.endDate}</div>
                <div className="text-sm">Price: ${item.price}</div>
                <div className="text-sm">Capacity: {item.capacity}</div>
                <div className="text-sm">Organizer: {item.organizerName}</div>
            </div>
        );
    };

    const groupTemplate = (option) => {
        return (
            <div className="flex align-items-center gap-2 p-3 bg-primary text-white">
                <i className="pi pi-calendar mr-2" />
                <div>{option.label}</div>
            </div>
        );
    };

    if (loading) {
        return <div>Loading events...</div>;
    }

    if (error) {
        return <div>{error}</div>;
    }

    if (events.length === 0) {
        return <div>You haven't registered for any events yet.</div>;
    }

    return (
        <section className="eventsOverview">
            <Toast ref={toast} />
            <div className="card flex justify-content-center">
                <ListBox
                    value={selectedEvent}
                    onChange={(e) => setSelectedEvent(e.value)}
                    options={events}
                    optionLabel="label"
                    optionGroupLabel="label"
                    optionGroupChildren="items"
                    optionGroupTemplate={groupTemplate}
                    itemTemplate={itemTemplate}
                    className="w-full md:w-30rem"
                    listStyle={{ maxHeight: '500px' }}
                />
            </div>
        </section>
    );
}

export default UserEvents;