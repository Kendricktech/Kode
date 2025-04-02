import React, { useState } from 'react';
import { createIcons } from 'lucide-react';

const Header = () => {
    const [isMobileMenuVisible, setIsMobileMenuVisible] = useState(false);
    const [isDarkMode, setIsDarkMode] = useState(false);

    const toggleMobileMenu = () => setIsMobileMenuVisible(!isMobileMenuVisible);
    const toggleDarkMode = () => setIsDarkMode(!isDarkMode);

    React.useEffect(() => {
        createIcons();
    }, []);

    return (
        <header className={`bg-primary p-4 ${isDarkMode ? 'dark' : ''}`}>
            <nav className="flex justify-between items-center">
                {/* Logo */}
                <div className="text-white text-2xl font-mono font-extrabold">Kode</div>

                {/* Mobile Menu Button */}
                <button 
                    id="menu-btn" 
                    className="md:hidden font-extrabold font-mono text-secondary-light"
                    onClick={toggleMobileMenu}
                >
                    <i data-lucide="menu"></i>
                </button>

                {/* Mobile Menu (Hidden by Default) */}
                <div 
                    id="mobile-menu" 
                    className={`absolute top-16 left-0 w-full bg-primary text-white text-xl font-mono font-extrabold p-4 space-y-2 md:relative md:flex md:space-y-0 md:space-x-4 md:bg-transparent ${isMobileMenuVisible ? 'block' : 'hidden'}`}
                >
                    <ul>
                        <li><a href="#" className="block py-2">Home</a></li>
                        <li><a href="#" className="block py-2">About</a></li>
                        <li><a href="#" className="block py-2">Services</a></li>
                        <li><a href="#" className="block py-2">Featured Projects</a></li>
                        <li><a href="#" className="block py-2">Contact Us</a></li>
                        <li><a href="#" className="block py-2">Blog</a></li>
                        <li><a href="#" className="block py-2">Testimonials</a></li>
                    </ul>
                </div>

                {/* Dark Mode Toggle */}
                <button 
                    id="dark-mode-toggle" 
                    className="text-white text-xl ml-4"
                    onClick={toggleDarkMode}
                >
                    🌙
                </button>
            </nav>
        </header>
    );
};

export default Header;
