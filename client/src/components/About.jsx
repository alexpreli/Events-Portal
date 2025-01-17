import React from 'react'
import './styles/About.css'

function About() {
    return (
        <section className="about">
            <div className="imageContainer">
                <h1>About Events Portal</h1>
                <div className='contentContainer'>
                    <h2 className='description'>Description: Application for organizing and participating in events.</h2>
                    <h2>Functionalities</h2>
                    <p>▷ Registration form for organizers (they require an invitation code and approval after registration)</p>
                    <p>▷ Registration form for participants</p>
                    <p>▷ Authentication form for organizers and participants</p>
                    <p>▷ Creation of events by organizers</p>
                    <p>▷ Modifying and deleting events by the organizers</p>
                    <p>▷ Registration of participants in events (users can register in events)</p>
                    <p>▷ Viewing the events to which the user is registered</p>
                    <p>▷ Generation of electronic tickets</p>
                    <p>▷ Sending electronic tickets by email</p>
                </div>
            </div>
        </section>
    )
}

export default About
