// Calendar functionality
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.events = window.events || []; // Use events from PHP
    }

    initializeCalendar() {
        // Get all required elements
        const elements = {
            calendarDays: document.getElementById('calendarDays'),
            currentMonth: document.getElementById('currentMonth'),
            prevMonth: document.getElementById('prevMonth'),
            nextMonth: document.getElementById('nextMonth'),
            eventModal: document.getElementById('eventModal'),
            closeButtons: document.getElementsByClassName('close')
        };

        // Check if all required elements exist
        const missingElements = Object.entries(elements)
            .filter(([key, element]) => !element)
            .map(([key]) => key);

        if (missingElements.length > 0) {
            console.error('Missing required elements:', missingElements);
            return;
        }

        // Store elements in the instance
        Object.assign(this, elements);

        // Setup event listeners and render calendar
        this.setupEventListeners();
        this.renderCalendar();
    }

    setupEventListeners() {
        // Month navigation
        this.prevMonth.addEventListener('click', () => this.changeMonth(-1));
        this.nextMonth.addEventListener('click', () => this.changeMonth(1));
        
        // Modal close buttons
        Array.from(this.closeButtons).forEach(button => {
            button.addEventListener('click', () => {
                this.eventModal.style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === this.eventModal) {
                this.eventModal.style.display = 'none';
            }
        });
    }

    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        // Update month and year display
        this.currentMonth.textContent = `${this.getMonthName(month)} ${year}`;
        
        // Clear previous calendar
        this.calendarDays.innerHTML = '';
        
        // Get first day of month and total days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const totalDays = lastDay.getDate();
        const startingDay = firstDay.getDay();
        
        // Add empty cells for days before first day of month
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'day empty';
            this.calendarDays.appendChild(emptyDay);
        }
        
        // Add days of the month
        for (let day = 1; day <= totalDays; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'day';
            dayElement.textContent = day;
            
            // Check if day has events
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayEvents = this.getEventsForDate(dateString);
            
            if (dayEvents.length > 0) {
                dayElement.classList.add('has-event');
                dayElement.addEventListener('click', () => this.showEventDetails(dayEvents[0]));
            }
            
            // Highlight today
            if (this.isToday(year, month, day)) {
                dayElement.classList.add('today');
            }
            
            this.calendarDays.appendChild(dayElement);
        }
    }

    changeMonth(delta) {
        this.currentDate.setMonth(this.currentDate.getMonth() + delta);
        this.renderCalendar();
    }

    getMonthName(month) {
        const months = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];
        return months[month];
    }

    isToday(year, month, day) {
        const today = new Date();
        return today.getFullYear() === year &&
               today.getMonth() === month &&
               today.getDate() === day;
    }

    getEventsForDate(date) {
        return this.events.filter(event => event.event_date === date);
    }

    showEventDetails(event) {
        const title = document.getElementById('eventTitle');
        const date = document.getElementById('eventDate');
        const time = document.getElementById('eventTime');
        const location = document.getElementById('eventLocation');
        const description = document.getElementById('eventDescription');
        
        title.textContent = event.title;
        date.textContent = `Date: ${this.formatDate(event.event_date)}`;
        time.textContent = `Heure: ${event.event_time}`;
        location.textContent = `Lieu: ${event.location}`;
        description.textContent = event.description || 'Aucune description disponible';
        
        this.eventModal.style.display = 'block';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }
}

// Wait for DOM to be loaded
document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
    calendar.initializeCalendar();
}); 