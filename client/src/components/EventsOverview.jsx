import React, { useState, useEffect, useRef } from 'react';
import { ListBox } from 'primereact/listbox';
import { Button } from 'primereact/button';
import { Toast } from 'primereact/toast';
import { useAuth } from './AuthContext';
import './styles/EventsOverview.css';

function EventsOverview() {
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [registering, setRegistering] = useState(false);
    const [user, setUser] = useState(null);
    const [isOrganizer, setIsOrganizer] = useState(false);
    const toast = useRef(null);
    const { token } = useAuth();

    useEffect(() => {
        fetchEvents();
    }, []);

    // verifica daca userul este logat
    useEffect(() => {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            try {
                const parsedUser = JSON.parse(storedUser);
                setUser(parsedUser);
                setIsOrganizer(parsedUser.is_organizer);
            } catch (error) {
                console.error('Failed to parse user data from localStorage:', error);
            }
        }
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await fetch('http://localhost:8000/events', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch events');
            }

            const data = await response.json();
            setEvents(data);
        } catch (error) {
            setError(error.message);
            console.error('Error fetching events:', error);
        } finally {
            setLoading(false);
        }
    };

    const registerForEvent = async (eventId) => {
        if (!user) {
            toast.current.show({
                severity: 'error',
                summary: 'Error',
                detail: 'You are not logged in!',
                life: 3000
            });
            return;
        }

        try {
            setRegistering(true);

            const response = await fetch('http://localhost:8000/events/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    event_id: eventId,
                    user_id: user.id
                })
            });

            const data = await response.json();

            if (!response.ok) {

                if (data.errors) {

                    const errorMessages = Object.values(data.errors).join(' ');
                    console.log(errorMessages);

                } else {
                    console.log(data.message || 'Failed to register for event');
                    
                    toast.current.show({
                        severity: 'error',
                        summary: 'Error',
                        detail: data.message || 'Failed to register for event',
                        life: 3000
                    });
                }

            } else {

                toast.current.show({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Successfully registered for event!',
                    life: 3000
                });

            }

        } catch (error) {
            toast.current.show({
                severity: 'error',
                summary: 'Error',
                detail: error.message || 'Failed to register for event',
                life: 3000
            });
            console.error('Error registering for event:', error);
        } finally {
            setRegistering(false);
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
                
                <Button 
                    label="Register for Event" 
                    icon="pi pi-check"
                    loading={registering} 
                    onClick={(e) => {
                        e.preventDefault();
                        if (selectedEvent) {
                            registerForEvent(selectedEvent.id);
                        } else {
                            toast.current.show({
                                severity: 'error',
                                summary: 'Error',
                                detail: 'No event selected',
                                life: 3000
                            });
                        }
                    }}
                    className="p-button-success mt-2"
                />

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
        return <div>Error: {error}</div>;
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

export default EventsOverview;
