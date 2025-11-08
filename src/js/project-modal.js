/**
 * Project Modal
 * Handles fullscreen modal for project descriptions
 */

class ProjectModal {
    constructor() {
        this.modal = document.getElementById('projectModal');
        if (!this.modal) return;

        this.overlay = this.modal.querySelector('.project-modal__overlay');
        this.closeBtn = this.modal.querySelector('.project-modal__close');
        this.titleEl = this.modal.querySelector('.project-modal__title');
        this.descriptionEl = this.modal.querySelector('.project-modal__description');

        this.init();
    }

    init() {
        // Add click handlers to all project links
        const projectLinks = document.querySelectorAll('.project-link');
        projectLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const heading = link.dataset.projectHeading;
                const description = link.dataset.projectDescription;

                if (description && description.trim() !== '') {
                    this.open(heading, description);
                }
            });
        });

        // Close button
        this.closeBtn.addEventListener('click', () => this.close());

        // Close on overlay click
        this.overlay.addEventListener('click', () => this.close());

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.close();
            }
        });
    }

    open(title, description) {
        this.titleEl.textContent = title;
        this.descriptionEl.textContent = description;
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ProjectModal();
});
