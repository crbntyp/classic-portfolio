/**
 * Admin Dashboard JavaScript
 * Handles project deletion, tabs, and other admin interactions
 * Refactored to use ModalManager and FormHandler classes
 */

document.addEventListener('DOMContentLoaded', function() {
  // ============================================================================
  // QUILL EDITORS
  // ============================================================================

  // Initialize Quill Editor for Add Modal
  let addQuillEditor = null;
  const addQuillContainer = document.getElementById('addProjectDescription');

  if (addQuillContainer) {
    addQuillEditor = createProjectEditor('addProjectDescription');
  }

  // Initialize Quill Editor for Edit Modal
  let editQuillEditor = null;
  const editQuillContainer = document.getElementById('editProjectDescription');

  if (editQuillContainer) {
    editQuillEditor = createProjectEditor('editProjectDescription');
  }

  // ============================================================================
  // TAB SWITCHING
  // ============================================================================

  const tabButtons = document.querySelectorAll('.admin-tab');

  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetTab = this.dataset.tab;

      // Remove active class from all tabs and content
      document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.classList.remove('active');
      });
      document.querySelectorAll('.admin-tab-content').forEach(content => {
        content.classList.remove('active');
      });

      // Add active class to clicked tab and corresponding content
      this.classList.add('active');
      document.getElementById(targetTab).classList.add('active');
    });
  });

  // ============================================================================
  // PROJECT DELETION
  // ============================================================================

  const deleteButtons = document.querySelectorAll('.project-item__delete');

  deleteButtons.forEach(button => {
    button.addEventListener('click', async function(e) {
      e.stopPropagation();

      const projectId = this.dataset.projectId;
      const projectName = this.dataset.projectName;

      // Confirm deletion
      if (!confirm(`Are you sure you want to delete "${projectName}"?\n\nThis action cannot be undone.`)) {
        return;
      }

      // Disable button during request
      this.disabled = true;
      this.style.opacity = '0.5';

      try {
        const formData = new FormData();
        formData.append('projectID', projectId);

        const response = await fetch('delete-project.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
          // Remove the project item from DOM with animation
          const projectItem = this.closest('.project-item');
          projectItem.style.transition = 'all 0.3s ease-out';
          projectItem.style.opacity = '0';
          projectItem.style.transform = 'scale(0.8)';

          setTimeout(() => {
            projectItem.remove();

            // Update project count
            const countElement = document.querySelector('.admin-section__title');
            if (countElement) {
              const currentCount = parseInt(countElement.textContent.match(/\d+/)[0]);
              countElement.textContent = `Projects (${currentCount - 1})`;
            }

            // Update stats grid
            const totalProjectsCard = document.querySelector('.stat-card:first-child .stat-number');
            if (totalProjectsCard) {
              const currentTotal = parseInt(totalProjectsCard.textContent);
              totalProjectsCard.textContent = currentTotal - 1;
            }
          }, 300);

        } else {
          alert('Error: ' + data.message);
          this.disabled = false;
          this.style.opacity = '1';
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the project. Please try again.');
        this.disabled = false;
        this.style.opacity = '1';
      }
    });
  });

  // ============================================================================
  // EDIT PROJECT MODAL & FORM
  // ============================================================================

  const editProjectModal = new ModalManager('editProjectModal');
  const editCategory = document.getElementById('editCategory');
  const editImageUploadGroup = document.getElementById('editImageUploadGroup');
  const editProjectImage = document.getElementById('editProjectImage');
  const editImagePreview = document.getElementById('editImagePreview');
  const editFileName = document.getElementById('editFileName');

  // Handle edit button clicks
  const editButtons = document.querySelectorAll('.project-item__edit');
  editButtons.forEach(button => {
    button.addEventListener('click', async function(e) {
      e.stopPropagation();
      const projectId = this.dataset.projectId;

      // Open modal
      editProjectModal.open();

      // Fetch project data
      try {
        const response = await fetch(`get-project.php?id=${projectId}`, {
          credentials: 'same-origin'
        });
        const responseText = await response.text();

        let data;
        try {
          data = JSON.parse(responseText);
        } catch (e) {
          console.error('Failed to parse JSON:', e);
          console.error('Response text:', responseText);
          alert('Server returned invalid response. Check console for details.');
          return;
        }

        if (data.success) {
          const project = data.project;

          // Populate form fields
          document.getElementById('editProjectID').value = project.projectID;
          document.getElementById('editProjectName').value = project.projectHeading || '';
          document.getElementById('editProjectUrl').value = project.url || '';
          document.getElementById('editProjectUrlTwo').value = project.url_two || '';
          document.getElementById('editProjectCTA').value = project.projectCTA || '';

          // Set category based on blobEntry
          if (editCategory) {
            if (project.blobEntry == 0) {
              editCategory.value = 'classic-portfolio';
              if (editImageUploadGroup) editImageUploadGroup.style.display = 'block';
            } else {
              editCategory.value = 'recent-artwork';
              if (editImageUploadGroup) editImageUploadGroup.style.display = 'none';
            }
          }

          // Set Quill editor content
          if (editQuillEditor) {
            editQuillEditor.setText(project.projectDescription || '');
          }

          // Show current image if exists
          if (project.projectTeaser && project.blobEntry != 1) {
            editImagePreview.innerHTML = `<img src="../portfolio/uploads/${project.projectTeaser}" alt="${project.projectHeading}">`;
          } else {
            editImagePreview.innerHTML = `
              <div class="image-preview-placeholder">
                <i class="las la-image"></i>
                <span>No Image</span>
              </div>
            `;
          }
        } else {
          alert('Error loading project: ' + data.message);
        }
      } catch (error) {
        console.error('Error fetching project:', error);
        alert('Failed to load project data');
      }
    });
  });

  // Setup category toggle for edit modal
  setupCategoryToggle({
    categorySelectId: 'editCategory',
    imageUploadGroupId: 'editImageUploadGroup',
    imageInputId: 'editProjectImage',
    imagePreviewId: 'editImagePreview',
    fileNameDisplayId: 'editFileName'
  });

  // Setup image preview for edit modal
  setupImagePreview({
    imageInputId: 'editProjectImage',
    imagePreviewId: 'editImagePreview',
    fileNameDisplayId: 'editFileName'
  });

  // Edit project form handler
  const editProjectForm = document.getElementById('editProjectForm');
  if (editProjectForm) {
    const editFormHandler = new FormHandler('editProjectForm', {
      endpoint: 'update-project.php',
      errorElementId: 'editProjectError',
      loadingType: 'overlay',
      loadingText: 'Updating...',
      successDelay: 1500,
      overlayElements: {
        overlay: 'editUploadOverlay',
        spinner: 'editUploadSpinner',
        success: 'editUploadSuccess',
        text: 'editUploadText'
      },
      beforeSubmit: (formData) => {
        // Add Quill editor content
        if (editQuillEditor) {
          const description = editQuillEditor.getText().trim();
          formData.set('projectDescription', description);
        }
      },
      onSuccess: (data) => {
        // Reload page to show updated project
        window.location.reload();
      }
    });
  }

  // ============================================================================
  // ADD PROJECT MODAL & FORM
  // ============================================================================

  const addProjectModal = new ModalManager('addProjectModal', {
    onClose: () => {
      // Reset form and clear Quill editor on close
      const addProjectForm = document.getElementById('addProjectForm');
      if (addProjectForm) {
        addProjectForm.reset();
      }
      if (addQuillEditor) {
        addQuillEditor.setText('');
      }
      const addFileName = document.getElementById('addFileName');
      if (addFileName) {
        addFileName.textContent = 'No file chosen';
      }
      const addImageUploadGroup = document.getElementById('addImageUploadGroup');
      if (addImageUploadGroup) {
        addImageUploadGroup.style.display = 'none';
      }
      const addImagePreview = document.getElementById('addImagePreview');
      if (addImagePreview) {
        addImagePreview.innerHTML = `
          <div class="image-preview-placeholder">
            <i class="las la-image"></i>
            <span>Image Preview</span>
          </div>
        `;
      }
    }
  });

  // Setup add project trigger
  const addProjectTrigger = document.getElementById('addProjectTrigger');
  if (addProjectTrigger && addProjectModal.modal) {
    addProjectTrigger.addEventListener('click', function() {
      addProjectModal.open();
    });
  }

  // Setup category toggle for add modal
  setupCategoryToggle({
    categorySelectId: 'addCategory',
    imageUploadGroupId: 'addImageUploadGroup',
    imageInputId: 'addProjectImage',
    imagePreviewId: 'addImagePreview',
    fileNameDisplayId: 'addFileName'
  });

  // Setup image preview for add modal
  setupImagePreview({
    imageInputId: 'addProjectImage',
    imagePreviewId: 'addImagePreview',
    fileNameDisplayId: 'addFileName'
  });

  // Add project form handler
  const addProjectForm = document.getElementById('addProjectForm');
  if (addProjectForm) {
    const addFormHandler = new FormHandler('addProjectForm', {
      endpoint: 'add-link.php',
      errorElementId: 'addProjectError',
      loadingType: 'overlay',
      loadingText: 'Uploading...',
      successDelay: 1500,
      overlayElements: {
        overlay: 'addUploadOverlay',
        spinner: 'addUploadSpinner',
        success: 'addUploadSuccess',
        text: 'addUploadText'
      },
      beforeSubmit: (formData) => {
        // Add Quill editor content
        if (addQuillEditor) {
          const description = addQuillEditor.getText().trim();
          formData.set('projectDescription', description);
        }
      },
      onSuccess: (data) => {
        // Reload page to show new project
        window.location.reload();
      }
    });
  }
});
