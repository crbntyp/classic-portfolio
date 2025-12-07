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
  const mobileTabItems = document.querySelectorAll('.admin-tabs-mobile__item[data-tab]');
  const mobileTabTrigger = document.getElementById('mobileTabTrigger');
  const mobileTabDropdown = document.getElementById('mobileTabDropdown');
  const mobileTabLabel = document.getElementById('mobileTabLabel');

  // Desktop tab switching
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetTab = this.dataset.tab;
      if (!targetTab) return;

      switchTab(targetTab, this.textContent);
    });
  });

  // Mobile dropdown toggle
  if (mobileTabTrigger) {
    mobileTabTrigger.addEventListener('click', function() {
      this.classList.toggle('open');
      mobileTabDropdown.classList.toggle('open');
    });
  }

  // Mobile tab switching
  mobileTabItems.forEach(item => {
    item.addEventListener('click', function() {
      const targetTab = this.dataset.tab;
      if (!targetTab) return;

      switchTab(targetTab, this.textContent);

      // Close dropdown
      mobileTabTrigger.classList.remove('open');
      mobileTabDropdown.classList.remove('open');
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (mobileTabTrigger && !mobileTabTrigger.contains(e.target) && !mobileTabDropdown.contains(e.target)) {
      mobileTabTrigger.classList.remove('open');
      mobileTabDropdown.classList.remove('open');
    }
  });

  function switchTab(targetTab, label) {
    // Remove active class from all tabs and content
    document.querySelectorAll('.admin-tab').forEach(tab => {
      tab.classList.remove('active');
    });
    document.querySelectorAll('.admin-tabs-mobile__item').forEach(item => {
      item.classList.remove('active');
    });
    document.querySelectorAll('.admin-tab-content').forEach(content => {
      content.classList.remove('active');
    });

    // Add active class to matching tabs
    document.querySelectorAll(`.admin-tab[data-tab="${targetTab}"]`).forEach(tab => {
      tab.classList.add('active');
    });
    document.querySelectorAll(`.admin-tabs-mobile__item[data-tab="${targetTab}"]`).forEach(item => {
      item.classList.add('active');
    });

    // Show content
    document.getElementById('tab-' + targetTab).classList.add('active');

    // Update mobile label
    if (mobileTabLabel) {
      mobileTabLabel.textContent = label;
    }
  }

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

          // Set category from project data
          if (editCategory) {
            editCategory.value = project.category || 'classic-portfolio';
          }
          // Always show image upload
          if (editImageUploadGroup) editImageUploadGroup.style.display = 'block';

          // Set Quill editor content
          if (editQuillEditor) {
            editQuillEditor.setText(project.projectDescription || '');
          }

          // Show current image if exists
          if (project.projectTeaser) {
            editImagePreview.innerHTML = `<img src="../uploads/${project.projectTeaser}" alt="${project.projectHeading}">`;
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
      // Remove active state from trigger
      const openAddProjectModal = document.getElementById('openAddProjectModal');
      if (openAddProjectModal) {
        openAddProjectModal.classList.remove('is-active');
      }
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

  // Setup add project trigger (sidebar button)
  const openAddProjectModal = document.getElementById('openAddProjectModal');
  if (openAddProjectModal && addProjectModal.modal) {
    openAddProjectModal.addEventListener('click', function() {
      addProjectModal.open();
      openAddProjectModal.classList.add('is-active');
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

  // ============================================================================
  // DRAG & DROP REORDERING
  // ============================================================================

  const projectsGrid = document.querySelector('.projects-grid');

  if (projectsGrid && typeof Sortable !== 'undefined') {
    const sortable = Sortable.create(projectsGrid, {
      animation: 150,
      ghostClass: 'project-item--ghost',
      chosenClass: 'project-item--chosen',
      dragClass: 'project-item--drag',
      handle: '.project-item',
      filter: '.project-item__edit, .project-item__delete',
      preventOnFilter: false,
      onEnd: async function(evt) {
        // Get new order of project IDs
        const items = projectsGrid.querySelectorAll('.project-item');
        const order = Array.from(items).map(item => item.dataset.projectId);

        // Save to database
        try {
          const response = await fetch('update-order.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order }),
            credentials: 'same-origin'
          });

          const data = await response.json();

          if (!data.success) {
            console.error('Failed to save order:', data.message);
          }
        } catch (error) {
          console.error('Error saving order:', error);
        }
      }
    });
  }

  // ============================================================================
  // SHRUG ADMIN
  // ============================================================================

  // Initialize Quill Editor for Shrug Content
  let shrugQuillEditor = null;
  const shrugQuillContainer = document.getElementById('shrugContentEditor');

  if (shrugQuillContainer) {
    shrugQuillEditor = new Quill('#shrugContentEditor', {
      theme: 'snow',
      placeholder: 'Write your shrug content here...',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
          ['link'],
          ['clean']
        ]
      }
    });
  }

  // Shrug form elements
  const shrugForm = document.getElementById('shrugForm');
  const shrugFormTitle = document.getElementById('shrugFormTitle');
  const shrugIdInput = document.getElementById('shrugId');
  const shrugTitleInput = document.getElementById('shrugTitle');
  const shrugSlugInput = document.getElementById('shrugSlug');
  const shrugTagsInput = document.getElementById('shrugTags');
  const shrugPublishedInput = document.getElementById('shrugPublished');
  const shrugNewBtn = document.getElementById('shrugNewBtn');
  const shrugSubmitBtn = document.getElementById('shrugSubmitBtn');
  const shrugListItems = document.querySelectorAll('.shrug-list-item');

  // Helper: Reset form to "Add New" state
  function resetShrugForm() {
    if (shrugForm) shrugForm.reset();
    if (shrugIdInput) shrugIdInput.value = '';
    if (shrugFormTitle) shrugFormTitle.textContent = 'Add New Shrug';
    if (shrugSubmitBtn) shrugSubmitBtn.textContent = 'Save Shrug';
    if (shrugQuillEditor) shrugQuillEditor.setText('');
    if (shrugPublishedInput) shrugPublishedInput.checked = true;

    // Remove active class from all list items
    document.querySelectorAll('.shrug-list-item').forEach(item => {
      item.classList.remove('active');
    });
  }

  // Helper: Populate form with shrug data
  function populateShrugForm(entry) {
    if (shrugIdInput) shrugIdInput.value = entry.id;
    if (shrugTitleInput) shrugTitleInput.value = entry.title || '';
    if (shrugSlugInput) shrugSlugInput.value = entry.slug || '';
    if (shrugTagsInput) shrugTagsInput.value = entry.tags || '';
    if (shrugPublishedInput) shrugPublishedInput.checked = entry.published == 1;
    if (shrugFormTitle) shrugFormTitle.textContent = 'Edit Shrug';
    if (shrugSubmitBtn) shrugSubmitBtn.textContent = 'Update Shrug';

    if (shrugQuillEditor) {
      // Set HTML content if it contains tags, otherwise set as text
      if (entry.content && entry.content.includes('<')) {
        shrugQuillEditor.root.innerHTML = entry.content;
      } else {
        shrugQuillEditor.setText(entry.content || '');
      }
    }

    // Highlight active list item
    document.querySelectorAll('.shrug-list-item').forEach(item => {
      item.classList.toggle('active', item.dataset.id === String(entry.id));
    });
  }

  // New button click - reset form
  if (shrugNewBtn) {
    shrugNewBtn.addEventListener('click', resetShrugForm);
  }

  // Edit button clicks
  document.querySelectorAll('.shrug-list-item__edit').forEach(button => {
    button.addEventListener('click', async function(e) {
      e.stopPropagation();
      const id = this.dataset.id;

      try {
        const response = await fetch(`get-shrug.php?id=${id}`, {
          credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success) {
          populateShrugForm(data.entry);
        } else {
          alert('Error loading shrug: ' + data.message);
        }
      } catch (error) {
        console.error('Error fetching shrug:', error);
        alert('Failed to load shrug data');
      }
    });
  });

  // List item clicks (also trigger edit)
  shrugListItems.forEach(item => {
    item.addEventListener('click', function(e) {
      // Don't trigger if clicking on action buttons
      if (e.target.closest('.shrug-list-item__actions')) return;

      const id = this.dataset.id;

      // Try to find in window.shrugData first (faster)
      if (window.shrugData) {
        const entry = window.shrugData.find(s => String(s.id) === String(id));
        if (entry) {
          populateShrugForm(entry);
          return;
        }
      }

      // Fallback to API fetch
      this.querySelector('.shrug-list-item__edit').click();
    });
  });

  // Delete button clicks
  document.querySelectorAll('.shrug-list-item__delete').forEach(button => {
    button.addEventListener('click', async function(e) {
      e.stopPropagation();

      const id = this.dataset.id;
      const title = this.dataset.title;

      if (!confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
        return;
      }

      this.disabled = true;
      this.style.opacity = '0.5';

      try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('shrug-delete.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
          // Remove from DOM with animation
          const listItem = this.closest('.shrug-list-item');
          listItem.style.transition = 'all 0.3s ease-out';
          listItem.style.opacity = '0';
          listItem.style.transform = 'translateX(20px)';

          setTimeout(() => {
            listItem.remove();

            // If we deleted the currently editing item, reset form
            if (shrugIdInput && shrugIdInput.value === id) {
              resetShrugForm();
            }

            // Update count in tab
            const shrugTab = document.querySelector('.admin-tab[data-tab="shrug"]');
            if (shrugTab) {
              const match = shrugTab.textContent.match(/\d+/);
              if (match) {
                const newCount = parseInt(match[0]) - 1;
                shrugTab.textContent = `Shrug (${newCount})`;
              }
            }
          }, 300);
        } else {
          alert('Error: ' + data.message);
          this.disabled = false;
          this.style.opacity = '1';
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting. Please try again.');
        this.disabled = false;
        this.style.opacity = '1';
      }
    });
  });

  // Form submission
  const shrugSuccessMessage = document.getElementById('shrugSuccessMessage');
  const shrugSuccessText = document.getElementById('shrugSuccessText');

  if (shrugForm) {
    shrugForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const isEditing = !!shrugIdInput.value;
      const editingId = shrugIdInput.value;

      // Add Quill content
      if (shrugQuillEditor) {
        const content = shrugQuillEditor.root.innerHTML;
        formData.set('content', content);
      }

      // Disable submit button
      if (shrugSubmitBtn) {
        shrugSubmitBtn.disabled = true;
        shrugSubmitBtn.textContent = 'Saving...';
      }

      // Hide any previous success message
      if (shrugSuccessMessage) {
        shrugSuccessMessage.style.display = 'none';
      }

      try {
        const response = await fetch('shrug-save.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
          const title = shrugTitleInput.value;
          const published = shrugPublishedInput.checked;
          const newId = data.id;

          if (isEditing) {
            // Update existing list item in DOM
            const listItem = document.querySelector(`.shrug-list-item[data-id="${editingId}"]`);
            if (listItem) {
              listItem.querySelector('.shrug-list-item__title').textContent = title;

              // Update draft status
              const metaSpan = listItem.querySelector('.shrug-list-item__meta');
              const draftSpan = metaSpan.querySelector('.shrug-list-item__draft');
              if (published && draftSpan) {
                draftSpan.remove();
              } else if (!published && !draftSpan) {
                metaSpan.insertAdjacentHTML('beforeend', '<span class="shrug-list-item__draft">Draft</span>');
              }
            }

            // Show success message
            if (shrugSuccessText) shrugSuccessText.textContent = 'Shrug updated successfully!';
          } else {
            // Add new list item to DOM
            const shrugListAdmin = document.querySelector('.shrug-list-admin');
            const emptyMessage = shrugListAdmin.querySelector('.shrug-list-empty');

            if (emptyMessage) {
              emptyMessage.remove();
            }

            const newItemHTML = `
              <div class="shrug-list-item" data-id="${newId}">
                <div class="shrug-list-item__info">
                  <span class="shrug-list-item__title">${title}</span>
                  <span class="shrug-list-item__meta">
                    ${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    ${!published ? '<span class="shrug-list-item__draft">Draft</span>' : ''}
                  </span>
                </div>
                <div class="shrug-list-item__actions">
                  <button type="button" class="shrug-list-item__edit" data-id="${newId}" title="Edit">
                    <i class="lni lni-pen-to-square"></i>
                  </button>
                  <button type="button" class="shrug-list-item__delete" data-id="${newId}" data-title="${title.replace(/"/g, '&quot;')}" title="Delete">
                    <i class="lni lni-trash-3"></i>
                  </button>
                </div>
              </div>
            `;

            shrugListAdmin.insertAdjacentHTML('afterbegin', newItemHTML);

            // Attach event listeners to new item
            const newItem = shrugListAdmin.querySelector(`.shrug-list-item[data-id="${newId}"]`);
            attachShrugItemListeners(newItem);

            // Update count in list title and tab
            updateShrugCount(1);

            // Show success message
            if (shrugSuccessText) shrugSuccessText.textContent = 'Shrug created successfully!';

            // Reset form for new entry
            resetShrugForm();
          }

          // Show success message
          if (shrugSuccessMessage) {
            shrugSuccessMessage.style.display = 'flex';
            setTimeout(() => {
              shrugSuccessMessage.style.display = 'none';
            }, 3000);
          }

          // Re-enable button
          if (shrugSubmitBtn) {
            shrugSubmitBtn.disabled = false;
            shrugSubmitBtn.textContent = isEditing ? 'Update Shrug' : 'Save Shrug';
          }

        } else {
          alert('Error: ' + data.message);
          if (shrugSubmitBtn) {
            shrugSubmitBtn.disabled = false;
            shrugSubmitBtn.textContent = shrugIdInput.value ? 'Update Shrug' : 'Save Shrug';
          }
        }
      } catch (error) {
        console.error('Save error:', error);
        alert('An error occurred while saving. Please try again.');
        if (shrugSubmitBtn) {
          shrugSubmitBtn.disabled = false;
          shrugSubmitBtn.textContent = shrugIdInput.value ? 'Update Shrug' : 'Save Shrug';
        }
      }
    });
  }

  // Helper: Attach event listeners to a shrug list item
  function attachShrugItemListeners(item) {
    // Edit button
    const editBtn = item.querySelector('.shrug-list-item__edit');
    if (editBtn) {
      editBtn.addEventListener('click', async function(e) {
        e.stopPropagation();
        const id = this.dataset.id;

        try {
          const response = await fetch(`get-shrug.php?id=${id}`, {
            credentials: 'same-origin'
          });
          const data = await response.json();

          if (data.success) {
            populateShrugForm(data.entry);
          } else {
            alert('Error loading shrug: ' + data.message);
          }
        } catch (error) {
          console.error('Error fetching shrug:', error);
          alert('Failed to load shrug data');
        }
      });
    }

    // Delete button
    const deleteBtn = item.querySelector('.shrug-list-item__delete');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', async function(e) {
        e.stopPropagation();

        const id = this.dataset.id;
        const title = this.dataset.title;

        if (!confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
          return;
        }

        this.disabled = true;
        this.style.opacity = '0.5';

        try {
          const formData = new FormData();
          formData.append('id', id);

          const response = await fetch('shrug-delete.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
          });

          const data = await response.json();

          if (data.success) {
            const listItem = this.closest('.shrug-list-item');
            listItem.style.transition = 'all 0.3s ease-out';
            listItem.style.opacity = '0';
            listItem.style.transform = 'translateX(20px)';

            setTimeout(() => {
              listItem.remove();

              if (shrugIdInput && shrugIdInput.value === id) {
                resetShrugForm();
              }

              updateShrugCount(-1);
            }, 300);
          } else {
            alert('Error: ' + data.message);
            this.disabled = false;
            this.style.opacity = '1';
          }
        } catch (error) {
          console.error('Delete error:', error);
          alert('An error occurred while deleting. Please try again.');
          this.disabled = false;
          this.style.opacity = '1';
        }
      });
    }

    // Item click (trigger edit)
    item.addEventListener('click', function(e) {
      if (e.target.closest('.shrug-list-item__actions')) return;
      this.querySelector('.shrug-list-item__edit').click();
    });
  }

  // Helper: Update shrug count in tab and list title
  function updateShrugCount(delta) {
    // Update tab count
    const shrugTab = document.querySelector('.admin-tab[data-tab="shrug"]');
    if (shrugTab) {
      const match = shrugTab.textContent.match(/\d+/);
      if (match) {
        const newCount = parseInt(match[0]) + delta;
        shrugTab.textContent = `Shrug (${newCount})`;
      }
    }

    // Update list title count
    const listTitle = document.querySelector('.shrug-admin__list-title');
    if (listTitle) {
      const match = listTitle.textContent.match(/\d+/);
      if (match) {
        const newCount = parseInt(match[0]) + delta;
        listTitle.textContent = `All Shrugs (${newCount})`;
      }
    }
  }

  // Auto-generate slug from title
  if (shrugTitleInput && shrugSlugInput) {
    shrugTitleInput.addEventListener('blur', function() {
      // Only auto-generate if slug is empty and this is a new entry
      if (!shrugSlugInput.value && !shrugIdInput.value) {
        const slug = this.value
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-|-$/g, '');
        shrugSlugInput.value = slug;
      }
    });
  }

  // ============================================================================
  // BOOKMARKS ADMIN
  // ============================================================================

  // Initialize Quill Editor for Bookmarks
  let bookmarksQuillEditor = null;
  const bookmarksQuillContainer = document.getElementById('bookmarksEditor');

  if (bookmarksQuillContainer) {
    bookmarksQuillEditor = new Quill('#bookmarksEditor', {
      theme: 'snow',
      placeholder: '[https://example.com] Example Site\n[https://github.com] GitHub',
      modules: {
        toolbar: false // No toolbar - just plain text entry
      }
    });

    // Load existing bookmarks content
    fetch('get-bookmarks.php', {
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.content) {
        bookmarksQuillEditor.root.innerHTML = data.content;
      }
    })
    .catch(error => {
      console.error('Error loading bookmarks:', error);
    });
  }

  // Bookmarks save button
  const bookmarksSaveBtn = document.getElementById('bookmarksSaveBtn');
  const bookmarksSuccessMessage = document.getElementById('bookmarksSuccessMessage');

  if (bookmarksSaveBtn && bookmarksQuillEditor) {
    bookmarksSaveBtn.addEventListener('click', async function() {
      // Disable button
      this.disabled = true;
      this.textContent = 'Saving...';

      // Hide previous success message
      if (bookmarksSuccessMessage) {
        bookmarksSuccessMessage.style.display = 'none';
      }

      try {
        const content = bookmarksQuillEditor.root.innerHTML;
        const formData = new FormData();
        formData.append('content', content);

        const response = await fetch('save-bookmarks.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
          // Show success message
          if (bookmarksSuccessMessage) {
            bookmarksSuccessMessage.style.display = 'flex';
            setTimeout(() => {
              bookmarksSuccessMessage.style.display = 'none';
            }, 3000);
          }
        } else {
          alert('Error: ' + data.message);
        }
      } catch (error) {
        console.error('Save error:', error);
        alert('An error occurred while saving. Please try again.');
      }

      // Re-enable button
      this.disabled = false;
      this.textContent = 'Save Bookmarks';
    });
  }
});
