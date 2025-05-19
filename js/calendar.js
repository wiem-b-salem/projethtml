// Sample events data (in a real application, this would come from a database)
const currentYear = new Date().getFullYear();
const events = [
    {
        id: 1,
        title: "Journée Portes Ouvertes",
        date: `${currentYear}-03-15`,
        time: "09:00",
        description: "Venez découvrir notre école et rencontrer notre équipe pédagogique!",
        images: [
            "../images/events/open-day-1.jpg",
            "../images/events/open-day-2.jpg"
        ]
    },
    {
        id: 2,
        title: "Spectacle de Printemps",
        date: `${currentYear}-03-20`,
        time: "14:00",
        description: "Les enfants présenteront leur spectacle de printemps dans la salle polyvalente.",
        images: [
            "../images/events/spring-show-1.jpg",
            "../images/events/spring-show-2.jpg"
        ]
    },
    {
        id: 3,
        title: "Réunion Parents-Professeurs",
        date: `${currentYear}-03-25`,
        time: "16:00",
        description: "Réunion trimestrielle pour discuter du progrès des enfants.",
        images: []
    }
];

// Calendar functionality
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.events = events;
        this.init();
    }

    init() {
        this.setupCalendar();
        this.setupEventListeners();
        this.renderCalendar();
    }

    setupCalendar() {
        this.calendarDays = document.getElementById('calendarDays');
        this.currentMonthElement = document.getElementById('currentMonth');
        this.prevMonthBtn = document.getElementById('prevMonth');
        this.nextMonthBtn = document.getElementById('nextMonth');
    }

    setupEventListeners() {
        this.prevMonthBtn.addEventListener('click', () => this.changeMonth(-1));
        this.nextMonthBtn.addEventListener('click', () => this.changeMonth(1));
        
        // Modal close buttons
        document.querySelectorAll('.close').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('show');
                });
            });
        });

        // Meeting form submission
        document.getElementById('meetingForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleMeetingSubmission();
        });

        // Reminder form submission
        document.getElementById('reminderForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleReminderSubmission();
        });
    }

    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        // Update month and year display
        this.currentMonthElement.textContent = `${this.getMonthName(month)} ${year}`;
        
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
        console.log('Current events array:', this.events);
        console.log('Looking for events on date:', date);
        const events = this.events.filter(event => {
            console.log('Comparing event date:', event.date, 'with:', date);
            return event.date === date;
        });
        console.log('Found events:', events);
        return events;
    }

    showEventDetails(event) {
        const modal = document.getElementById('eventModal');
        const title = document.getElementById('eventTitle');
        const date = document.getElementById('eventDate');
        const time = document.getElementById('eventTime');
        const description = document.getElementById('eventDescription');
        const gallery = document.getElementById('eventGallery');
        
        title.textContent = event.title;
        date.textContent = `Date: ${this.formatDate(event.date)}`;
        time.textContent = `Heure: ${event.time}`;
        description.textContent = event.description;
        
        // Clear and populate gallery
        gallery.innerHTML = '';
        if (event.images && event.images.length > 0) {
            event.images.forEach(image => {
                const img = document.createElement('img');
                img.src = image;
                img.alt = event.title;
                img.addEventListener('click', () => this.showFullImage(image));
                gallery.appendChild(img);
            });
        }
        
        modal.classList.add('show');
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    showFullImage(imageSrc) {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <img src="${imageSrc}" style="width: 100%; max-height: 80vh; object-fit: contain;">
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            modal.remove();
        });
    }

    handleMeetingSubmission() {
        const form = document.getElementById('meetingForm');
        const formData = new FormData(form);
        
        // In a real application, this would send the data to a server
        console.log('Meeting scheduled:', Object.fromEntries(formData));
        
        alert('Rendez-vous confirmé! Vous recevrez un email de confirmation.');
        document.getElementById('meetingModal').classList.remove('show');
        form.reset();
    }

    handleReminderSubmission() {
        const form = document.getElementById('reminderForm');
        const formData = new FormData(form);
        
        // In a real application, this would send the data to a server
        console.log('Reminder set:', Object.fromEntries(formData));
        
        alert('Rappel défini! Vous recevrez une notification.');
        document.getElementById('reminderModal').classList.remove('show');
        form.reset();
    }
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Calendar();
}); 