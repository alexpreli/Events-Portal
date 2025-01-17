import React, { useState, useEffect } from 'react';
import { Calendar } from 'primereact/calendar';
import './styles/Home.css';

const Home = () => {
    const [eventsPerDay, setEventsPerDay] = useState({});

    useEffect(() => {
        fetchEventsPerDay();
    }, []);

    const fetchEventsPerDay = async () => {
        try {
            const response = await fetch('http://localhost:8000/events/perDay');
            const data = await response.json();
            
            const counts = data.eventsPerDay;
            setEventsPerDay(counts);
        } catch (error) {
            console.error('Failed to fetch events per day');
        }
    };

    const renderDateCell = (dateParam) => {
        try {
            let date;
            if (dateParam && dateParam.year !== undefined && dateParam.month !== undefined && dateParam.day !== undefined) {
                date = new Date(dateParam.year, dateParam.month, dateParam.day);
            } else {
                return (
                    <div className="flex flex-col items-center">
                        <span className="text-lg">-</span>
                    </div>
                );
            }

            if (isNaN(date.getTime())) {
                return (
                    <div className="flex flex-col items-center">
                        <span className="text-lg">-</span>
                    </div>
                );
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const dateKey = `${year}-${month}-${day}`;
            const count = eventsPerDay[dateKey] || 0;

            return (
                <div className="flex flex-col items-center">
                    <span className="text-lg">{date.getDate()}</span>
                    <span className="text-xs mt-1">EV: {count}</span>
                </div>
            );
        } catch (error) {
            console.error('Error in renderDateCell:', error);
            return (
                <div className="flex flex-col items-center">
                    <span className="text-lg">-</span>
                </div>
            );
        }
    };
    
    return (
        <section className="home">
            <div className="presentationContainer">
                <div className='blur-overlay'></div>
                <h1>Welcome to Events Portal</h1>
            </div>
            
            <div className="w-full max-w-4xl mx-auto mt-8 px-4">
                <h2 className="calendarSubtitle">Events Calendar</h2>
                <Calendar
                    inline
                    dateTemplate={renderDateCell}
                    className="w-full"
                />
            </div>

            <div className='content'>
                <div className='featuresContainer'>
                    <h2>Main Features</h2>
                    <p>Explore events from various fields</p>
                    <p>Attend and register for events</p>
                    <p>Stay up to date with the latest events near you</p>
                    <p>Buy e-tickets in just a few steps</p>
                    <p>Purchase tickets directly from the app, and the app does the rest!</p>
                    <p>Tickets are generated electronically and sent directly to your email, ready to use</p>
                </div>
                <div className='benefitsContainer'>
                    <h2>User Benefits</h2>
                    <p>Easy management of your events</p>
                    <p>Respond to invitations quickly</p>
                    <p>Simple and intuitive interface</p>
                    <p>Quick access to events in your area</p>
                </div>
                <div className='reviewsContainer'>
                    <h2>Reviews</h2>
                    <p>"The app has completely changed the way I organize events – simple and fast!" – Maria, Organizer</p>
                    <p>"I always find the most interesting events in my area!" – Matei, Participant</p>
                    <p>"Thanks to the app, I found perfect events for my hobbies. It's very easy to use!" – Anca, Participant</p>
                    <p>"The app is great! I discovered cultural and sports events that I didn't know about. It completely changed my weekends!" – Vlad, Participant</p>
                    <p>"The best tool for organizers. It helps me promote my events and attract new participants." – Alex, Organizer</p>
                    <p>"The interface is very friendly. I recommend the app to everyone who wants to find interesting activities!" – Cristina, Participant</p>
                    <p>"Every week I find something new and exciting to do thanks to the app. It's just great!" – Robert, Participant</p>
                </div>
            </div>
        </section>
    );
};

export default Home;
