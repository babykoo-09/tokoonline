document.addEventListener('scroll', function() {
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        const scrollPosition = window.pageYOffset;
        heroSection.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // No AJAX submission for customization forms anymore, they will submit normally to keranjang.php
});

function showNotification(message, type = 'success') {
    const notificationContainer = document.createElement('div');
    notificationContainer.className = 'notification-container';

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;

    const icon = document.createElement('span');
    icon.className = 'notification-icon';
    if (type === 'success') {
        icon.innerHTML = '&#10003;'; // Checkmark
    } else {
        icon.innerHTML = '&#10007;'; // Cross mark
    }

    const text = document.createElement('span');
    text.textContent = message;

    notification.appendChild(icon);
    notification.appendChild(text);
    notificationContainer.appendChild(notification);

    document.body.appendChild(notificationContainer);

    // Trigger the animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    // Hide and remove the notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notificationContainer);
        }, 500); // Match CSS transition duration
    }, 3000);
}
