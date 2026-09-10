import { createIcons, icons } from 'lucide';

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Re-initialize after partial navigation / dynamic inserts
window.reinitLucideIcons = () => {
    createIcons({ icons });
};
