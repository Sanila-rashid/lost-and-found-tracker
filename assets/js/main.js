// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // Initial card animation staggered
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px) scale(0.95)';
        card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0) scale(1)';
        }, index * 100);
    });

    // Dark Mode Initialization
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark');
        if(toggleBtn) toggleBtn.innerHTML = '☀️';
    }

    if(toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark');
            if (body.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                toggleBtn.innerHTML = '☀️';
            } else {
                localStorage.setItem('theme', 'light');
                toggleBtn.innerHTML = '🌙';
            }
        });
    }

    // Check for session flash messages set by PHP
    const flashMsg = document.getElementById('flash-message');
    if (flashMsg && flashMsg.value) {
        showToast(flashMsg.value, flashMsg.dataset.type || 'success');
        if (flashMsg.dataset.type === 'success') {
            confetti();
        }
    }
});

// Confetti Effect
function confetti() {
    for (let i = 0; i < 50; i++) {
        let c = document.createElement("div");
        c.className = "confetti";
        c.style.left = Math.random() * 100 + "vw";
        // Random shapes and colors
        c.style.background = ["#ff6fa3", "#7f7fff", "#ffd700", "#ff4d8d", "#5c5cff"][Math.floor(Math.random() * 5)];
        c.style.animationDuration = (Math.random() * 2 + 2) + "s";
        c.style.animationDelay = (Math.random() * 0.5) + "s";
        
        // Randomly make some circles
        if (Math.random() > 0.5) {
            c.style.borderRadius = "50%";
        }
        
        document.body.appendChild(c);
        setTimeout(() => c.remove(), 4000);
    }
}

// Toast Notifications
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    
    const icon = type === 'success' ? '✅' : '⚠️';
    toast.innerHTML = `<span class="toast-icon">${icon}</span> <span>${message}</span>`;
    
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400); // Wait for transition to finish
    }, 3000);
}
