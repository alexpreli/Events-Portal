import React, {useState} from 'react'
import {Link} from 'react-router-dom'
import './styles/Navbar.css'
import {FaBars} from 'react-icons/fa'
import {ImCross} from 'react-icons/im'

function Navbar() {
    const [Mobile, setMobile]  = useState(false);

    return (
        <nav className="navbar">
            <div className='navbarContainer'>
                <h3 className='logo'>Events Portal</h3>

                <ul className={Mobile ? "nav-links-mobile" : "nav-links"} onClick={() => {setMobile(Mobile)}}>
                    <li><Link to='/'>Home</Link></li>
                    <li><Link to='/userTickets'>My Tickets</Link></li>
                    <li><Link to='/userEvents'>My Events</Link></li>
                    <li><Link to='/eventsOverview'>Events Overview</Link></li>
                    <li><Link to='/organizerRegister'>Organizer Register</Link></li>
                    <li><Link to='/organizerLogin'>Organizer Login</Link></li>
                    <li><Link to='/userRegister'>User Register</Link></li>
                    <li><Link to='/userLogin'>User Login</Link></li>
                    <li><Link to='/about'>About</Link></li>
                    <li><Link to='/contact'>Contact</Link></li>
                    <li><Link to='/organizerDashboard'>Organizer Dashboard</Link></li>
                    <li><Link to='/logout'>Log Out</Link></li>
                </ul>
                <button className='mobile-menu-icon' onClick={() => setMobile(!Mobile)}>
                    {Mobile ? <ImCross /> : <FaBars />}
                </button>

            </div>
        </nav>
    )
}

export default Navbar
