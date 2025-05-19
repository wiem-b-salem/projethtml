document.addEventListener('DOMContentLoaded', function() {
    // Handle activity card clicks
    const activities = document.querySelectorAll('.activity');
    activities.forEach(activity => {
        activity.addEventListener('click', function() {
            // Remove active class from all activities
            activities.forEach(a => a.classList.remove('active'));
            // Add active class to clicked activity
            this.classList.add('active');
        });
    });

    // Handle PDF download
    document.getElementById('downloadPDF').addEventListener('click', function() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        
        // Get the schedule content
        const scheduleContent = document.querySelector('.timeline').innerHTML;
        
        // Create the print-friendly HTML
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Emploi du Temps - École des Petits Génies</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                    }
                    .activity {
                        margin-bottom: 20px;
                        padding: 10px;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                    }
                    .activity h3 {
                        color: #ff6b6b;
                        margin: 5px 0;
                    }
                    .activity-details {
                        display: block !important;
                        margin-top: 10px;
                        padding-top: 10px;
                        border-top: 1px dashed #ddd;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .header h1 {
                        color: #ff6b6b;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Emploi du Temps - École des Petits Génies</h1>
                    <p>Une journée typique à l'école</p>
                </div>
                <div class="schedule">
                    ${scheduleContent}
                </div>
            </body>
            </html>
        `;
        
        // Write the content to the new window
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load then print
        printWindow.onload = function() {
            printWindow.print();
            // Close the window after printing
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        };
    });

    // Handle print button
    document.getElementById('printSchedule').addEventListener('click', function() {
        window.print();
    });

    // Add scroll animation for activities
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Set initial styles for animation
    activities.forEach(activity => {
        activity.style.opacity = '0';
        activity.style.transform = 'translateY(20px)';
        activity.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(activity);
    });
}); 