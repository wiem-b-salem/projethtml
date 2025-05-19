// Sample data (in a real application, this would come from a server)
const sampleActivities = [
    {
        id: 1,
        date: '2024-02-20',
        title: 'Peinture à l\'eau',
        description: 'Les enfants ont créé de belles peintures avec des couleurs vives.',
        category: 'art'
    },
    {
        id: 2,
        date: '2024-02-20',
        title: 'Jeux en plein air',
        description: 'Activités physiques et jeux de groupe dans le jardin.',
        category: 'sports'
    },
    {
        id: 3,
        date: '2024-02-19',
        title: 'Lecture d\'histoire',
        description: 'Les enfants ont écouté une histoire et ont participé à une discussion.',
        category: 'activities'
    }
];

const samplePhotos = [
    {
        id: 1,
        url: '../images/activities/art1.jpg',
        title: 'Peinture à l\'eau',
        category: 'art',
        date: '2024-02-20'
    },
    {
        id: 2,
        url: '../images/activities/sports1.jpg',
        title: 'Jeux en plein air',
        category: 'sports',
        date: '2024-02-20'
    },
    {
        id: 3,
        url: '../images/activities/activity1.jpg',
        title: 'Lecture d\'histoire',
        category: 'activities',
        date: '2024-02-19'
    }
];

const sampleMessages = [
    {
        id: 1,
        from: 'Mme. Smith',
        subject: 'Progrès en peinture',
        message: 'Votre enfant a fait de grands progrès en peinture cette semaine!',
        date: '2024-02-19',
        read: true
    },
    {
        id: 2,
        from: 'M. Johnson',
        subject: 'Activité sportive',
        message: 'Nous avons remarqué que votre enfant aime particulièrement les jeux de ballon.',
        date: '2024-02-18',
        read: false
    }
];

// Dashboard functionality
class Dashboard {
    constructor() {
        // Check if user is authenticated
        const userEmail = sessionStorage.getItem('userEmail');
        if (!userEmail) {
            window.location.href = 'signin.html';
            return;
        }

        // Update user info in the header
        document.getElementById('parentName').textContent = userEmail;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadActivities();
        this.loadPhotos();
        this.loadMessages();
        this.setupFAQ();
    }

    setupEventListeners() {
        // Activity filters
        document.querySelectorAll('.activity-filters .filter-btn').forEach(btn => {
            btn.addEventListener('click', () => this.filterActivities(btn.dataset.filter));
        });

        // Gallery filters
        document.querySelectorAll('.gallery-filters .filter-btn').forEach(btn => {
            btn.addEventListener('click', () => this.filterPhotos(btn.dataset.filter));
        });

        // Message form
        document.getElementById('messageForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleMessageSubmit(e.target);
        });

        // Add logout functionality
        document.querySelector('nav a').addEventListener('click', (e) => {
            if (e.target.textContent.includes('Retour')) {
                e.preventDefault();
                sessionStorage.removeItem('userEmail');
                window.location.href = 'homepage.html';
            }
        });
    }

    loadActivities() {
        const activityList = document.querySelector('.activity-list');
        activityList.innerHTML = '';

        sampleActivities.forEach(activity => {
            const activityElement = document.createElement('div');
            activityElement.className = 'activity-item';
            activityElement.innerHTML = `
                <h3>${activity.title}</h3>
                <p>${activity.description}</p>
                <small>${this.formatDate(activity.date)}</small>
            `;
            activityList.appendChild(activityElement);
        });
    }

    loadPhotos() {
        const photoGallery = document.querySelector('.photo-gallery');
        photoGallery.innerHTML = '';

        samplePhotos.forEach(photo => {
            const photoElement = document.createElement('div');
            photoElement.className = 'photo-item';
            photoElement.innerHTML = `
                <img src="${photo.url}" alt="${photo.title}" onclick="dashboard.showFullImage('${photo.url}')">
                <div class="photo-info">
                    <h4>${photo.title}</h4>
                    <small>${this.formatDate(photo.date)}</small>
                </div>
            `;
            photoGallery.appendChild(photoElement);
        });
    }

    loadMessages() {
        const messageList = document.querySelector('.message-list');
        messageList.innerHTML = '';

        sampleMessages.forEach(message => {
            const messageElement = document.createElement('div');
            messageElement.className = `message-item ${message.read ? 'read' : 'unread'}`;
            messageElement.innerHTML = `
                <div class="message-header">
                    <h4>${message.subject}</h4>
                    <small>${this.formatDate(message.date)}</small>
                </div>
                <p>De: ${message.from}</p>
                <p>${message.message}</p>
            `;
            messageList.appendChild(messageElement);
        });
    }

    setupFAQ() {
        document.querySelectorAll('.faq-item').forEach(item => {
            const question = item.querySelector('.faq-question');
            question.addEventListener('click', () => {
                item.classList.toggle('active');
            });
        });
    }

    filterActivities(filter) {
        const buttons = document.querySelectorAll('.activity-filters .filter-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        const activityList = document.querySelector('.activity-list');
        activityList.innerHTML = '';

        let filteredActivities = sampleActivities;
        if (filter !== 'all') {
            const today = new Date().toISOString().split('T')[0];
            const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            filteredActivities = sampleActivities.filter(activity => {
                switch (filter) {
                    case 'today':
                        return activity.date === today;
                    case 'week':
                        return activity.date >= weekAgo;
                    case 'month':
                        return activity.date >= monthAgo;
                    default:
                        return true;
                }
            });
        }

        filteredActivities.forEach(activity => {
            const activityElement = document.createElement('div');
            activityElement.className = 'activity-item';
            activityElement.innerHTML = `
                <h3>${activity.title}</h3>
                <p>${activity.description}</p>
                <small>${this.formatDate(activity.date)}</small>
            `;
            activityList.appendChild(activityElement);
        });
    }

    filterPhotos(filter) {
        const buttons = document.querySelectorAll('.gallery-filters .filter-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        const photoGallery = document.querySelector('.photo-gallery');
        photoGallery.innerHTML = '';

        const filteredPhotos = filter === 'all' 
            ? samplePhotos 
            : samplePhotos.filter(photo => photo.category === filter);

        filteredPhotos.forEach(photo => {
            const photoElement = document.createElement('div');
            photoElement.className = 'photo-item';
            photoElement.innerHTML = `
                <img src="${photo.url}" alt="${photo.title}" onclick="dashboard.showFullImage('${photo.url}')">
                <div class="photo-info">
                    <h4>${photo.title}</h4>
                    <small>${this.formatDate(photo.date)}</small>
                </div>
            `;
            photoGallery.appendChild(photoElement);
        });
    }

    handleMessageSubmit(form) {
        const formData = new FormData(form);
        const message = {
            id: sampleMessages.length + 1,
            from: 'Vous',
            subject: formData.get('subject'),
            message: formData.get('message'),
            date: new Date().toISOString().split('T')[0],
            read: true
        };

        sampleMessages.unshift(message);
        this.loadMessages();
        form.reset();

        // In a real application, this would send the message to a server
        console.log('Message sent:', message);
    }

    showFullImage(url) {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <img src="${url}" style="width: 100%; max-height: 80vh; object-fit: contain;">
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            modal.remove();
        });
    }

    formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('fr-FR', options);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new Dashboard();
}); 