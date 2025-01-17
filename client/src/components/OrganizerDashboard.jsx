import { useState, useEffect } from 'react';
import { InputText } from 'primereact/inputtext';
import { ListBox } from 'primereact/listbox';
import FormCard from './FormCard.jsx';
import './styles/OrganizerDashboard.css';
import { useAuth } from './AuthContext.jsx';
import { Button } from 'primereact/button';

function OrganizerDashboard() {
    const [id, setId] = useState('');
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [location, setLocation] = useState('');
    const [startDate, setStartDate] = useState('2025-01-01T12:00');
    const [endDate, setEndDate] = useState('2025-01-01T12:00');
    const [price, setPrice] = useState('');
    const [capacity, setCapacity] = useState('');
    const [organizerName, setOrganizerName] = useState('');
    const [addError, setAddError] = useState('');
    const [editError, setEditError] = useState('');
    const [deleteError, setDeleteError] = useState('');
    const [loading, setLoading] = useState(false);
    const [eventsWithParticipants, setEventsWithParticipants] = useState([]);
    const [selectedEvent, setSelectedEvent] = useState(null);

    const [user, setUser] = useState(null);
    const [isOrganizer, setIsOrganizer] = useState(false);

    const { token } = useAuth();

    const eventsFieldsStates = {
        id,
        name,
        description,
        location,
        startDate,
        endDate,
        price,
        capacity,
        organizerName,
    };

    const deletingEventsFieldsStates = { id };

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

    useEffect(() => {
        if (user?.id) {
            const fetchEventsWithParticipants = async () => {
                try {
                    const response = await fetch(`http://localhost:8000/events/participants?user_id=${user.id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        },
                        credentials: 'include',
                    });
    
                    if (!response.ok) {
                        throw new Error('Failed to fetch events with participants');
                    }
    
                    const data = await response.json();
                    setEventsWithParticipants(data);
                } catch (error) {
                    console.error('Error fetching events with participants:', error);
                }
            };
    
            fetchEventsWithParticipants();
        }
    }, [user, token]);
    


    if (!user) {
        return <h1>You are not logged in as an organizer</h1>;
    }

    if (!isOrganizer) {
        return <h1>You are not logged in as an organizer</h1>;
    }

    const fetchEventsWithParticipants = async () => {
        try {
            const response = await fetch(`http://localhost:8000/events/participants?user_id=${user?.id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                credentials: 'include',
            });
    
            if (!response.ok) {
                console.error('Failed to fetch events with participants:', response);
                throw new Error('Failed to fetch events with participants');
            }
    
            const data = await response.json();
            setEventsWithParticipants(data);
            
            

        } catch (error) {
            console.error('Error fetching events with participants:', error);
        }
    };
    


    const addEventSubmit = async (formData) => {
        try {
            setLoading(true);
            const response = await fetch('http://localhost:8000/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            });
    
            const data = await response.json();
            
            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).join(' ');
                    setAddError(errorMessages);
                } else {
                    setAddError(data.message || 'Failed to create event');
                }
            } else {
                setAddError('Event created successfully!');
                // Refresh the events list
                const fetchEventsWithParticipants = async () => {
                    const response = await fetch(`http://localhost:8000/events/participants?user_id=${user?.id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        },
                        credentials: 'include',
                    });
                    const data = await response.json();
                    setEventsWithParticipants(data);
                };
                fetchEventsWithParticipants();
            }
        } catch (error) {
            setAddError('Error creating event: ' + error.message);
        } finally {
            setLoading(false);
        }
    };
    
    const editEventSubmit = async (formData) => {
        try {
            setLoading(true);
            const response = await fetch('http://localhost:8000/events', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            });
    
            const data = await response.json();
            
            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).join(' ');
                    setEditError(errorMessages);
                } else {
                    setEditError(data.message || 'Failed to update event');
                }
            } else {
                setEditError('Event updated successfully!');

                // refresh list box with participants
                const fetchEventsWithParticipants = async () => {
                    const response = await fetch(`http://localhost:8000/events/participants?user_id=${user?.id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        },
                        credentials: 'include',
                    });
                    const data = await response.json();
                    setEventsWithParticipants(data);
                };
                fetchEventsWithParticipants();
            }
        } catch (error) {
            setEditError('Error updating event: ' + error.message);
        } finally {
            setLoading(false);
        }
    };
    
    const deleteEventSubmit = async (formData) => {
        try {
            setLoading(true);
            const response = await fetch('http://localhost:8000/events', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            });
    
            const data = await response.json();
            
            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).join(' ');
                    setDeleteError(errorMessages);
                } else {
                    setDeleteError(data.message || 'Failed to delete event');
                }
            } else {
                setDeleteError('Event deleted successfully!');

                const fetchEventsWithParticipants = async () => {
                    const response = await fetch(`http://localhost:8000/events/participants?user_id=${user?.id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        },
                        credentials: 'include',
                    });
                    const data = await response.json();
                    setEventsWithParticipants(data);
                };
                fetchEventsWithParticipants();
            }
        } catch (error) {
            setDeleteError('Error deleting event: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    const eventTemplate = (event) => {
        return (
            <div className="event-item p-3">
                <h3 className="text-xl font-bold mb-2">{event.event.name}</h3>
                <div className="event-details mb-3">
                    <div className="text-sm text-gray-600">Start: {event.event.startDate}</div>
                    <div className="text-sm text-gray-600">End: {event.event.endDate}</div>
                </div>
                <div className="participants">
                    <h4 className="text-lg font-semibold mb-2">Participants ({event.participants.length})</h4>
                    {event.participants.length > 0 ? (
                        <ul className="list-none p-0 m-0">
                            {event.participants.map(participant => (
                                <li key={participant.id} className="mb-1 text-sm">
                                    {participant.name} - {participant.email}
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-sm text-gray-500">No participants yet</p>
                    )}
                </div>
            </div>
        );
    };

    const eventsFields = (
        <>
            <span className="p-float-label">
                <InputText
                    type="text"
                    id="id"
                    value={id}
                    onChange={(e) => setId(e.target.value)}
                />
                <label htmlFor="id">ID</label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="text"
                    id="name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
                <label htmlFor="name">Event Name</label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="text"
                    id="description"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                />
                <label htmlFor="description">Description</label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="text"
                    id="location"
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                />
                <label htmlFor="location">Location</label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="datetime-local"
                    id="startDate"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                />
                <label htmlFor="startDate"></label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="datetime-local"
                    id="endDate"
                    value={endDate}
                    onChange={(e) => setEndDate(e.target.value)}
                />
                <label htmlFor="endDate"></label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="number"
                    id="price"
                    value={price}
                    onChange={(e) => setPrice(e.target.value)}
                />
                <label htmlFor="price">Price</label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="number"
                    id="capacity"
                    value={capacity}
                    onChange={(e) => setCapacity(e.target.value)}
                />
                <label htmlFor="capacity">Capacity</label>
            </span>
    
            <span className="p-float-label">
                <InputText
                    type="text"
                    id="organizerName"
                    value={organizerName}
                    onChange={(e) => setOrganizerName(e.target.value)}
                />
                <label htmlFor="organizerName">Organizer Name</label>
            </span>
        </>
    );

    const deletingEventsFields = (
        <>
            <span className="p-float-label">
                <InputText
                    type="tel"
                    pattern="[0-9]*"
                    inputMode="numeric"
                    id="id"
                    value={id}
                    onChange={(e) => setId(e.target.value)}
                />
                <label htmlFor="id">ID</label>
            </span>
        </>
    );

    return (
        <section className="organizerDashboard">
            <div className="registerContainer">
                <FormCard 
                    className='grid' 
                    onSubmit={addEventSubmit} 
                    title="Add Event" 
                    fields={eventsFields} 
                    fieldsState={eventsFieldsStates} 
                    error={addError} 
                    loading={loading} 
                    buttonText='Add' 
                />
                <FormCard 
                    className='grid' 
                    onSubmit={deleteEventSubmit} 
                    title="Delete Event" 
                    fields={deletingEventsFields} 
                    fieldsState={deletingEventsFieldsStates} 
                    error={deleteError} 
                    loading={loading} 
                    buttonText='Delete'
                />
                <FormCard 
                    className='grid' 
                    onSubmit={editEventSubmit} 
                    title="Edit Event" 
                    fields={eventsFields} 
                    fieldsState={eventsFieldsStates} 
                    error={editError} 
                    loading={loading} 
                    buttonText='Edit'
                />
                
                <FormCard
                    className="grid"
                    title="Events and Participants"
                    fields={
                        <ListBox
                            value={selectedEvent}
                            onChange={(e) => setSelectedEvent(e.value)}
                            options={eventsWithParticipants}
                            itemTemplate={eventTemplate}
                            className="w-full border-none"
                            listStyle={{ maxHeight: '600px' }}
                            optionLabel="event.name"
                        />
                    }
                    fieldsState={{}}
                    onSubmit={() => fetchEventsWithParticipants()}
                    buttonText="Show"
                />


            </div>
        </section>
    );
}

export default OrganizerDashboard;