<!-- Add Link Modal -->
<div class="modal" id="addLinkModal">
  <div class="modal__overlay"></div>
  <div class="modal__content">
    <button class="modal__close" id="addLinkModalClose">
      <i class="lni lni-close"></i>
    </button>

    <!-- Upload overlay for loading and success states -->
    <div class="upload-overlay" id="uploadOverlay">
      <div class="upload-spinner" id="uploadSpinner"></div>
      <div class="upload-success" id="uploadSuccess">
        <i class="lni lni-checkmark"></i>
      </div>
      <div class="upload-text" id="uploadText">Uploading...</div>
    </div>

    <h2 class="modal__logo-text">crbntyp</h2>
    <form class="login-form" id="addLinkForm" enctype="multipart/form-data">
      <div id="addLinkError" class="error-message" style="display: none;"></div>

      <div class="form-group">
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="">Select Category</option>
          <option value="classic-portfolio">Classic Portfolio</option>
          <option value="recent-artwork">Recent Artwork</option>
          <option value="artwork-portfolio">Artwork Portfolio</option>
          <option value="vibes">Vibes</option>
        </select>
      </div>

      <!-- Classic Portfolio specific field -->
      <div class="form-group" id="imageUploadGroup" style="display: none;">
        <label>Project Image</label>
        <div class="file-input-wrapper">
          <input type="file" id="projectImage" name="projectImage" accept="image/*" class="file-input">
          <label for="projectImage" class="file-input-label">
            <i class="lni lni-cloud-upload"></i>
            <span class="file-input-text">Upload</span>
          </label>
          <span class="file-name" id="fileName">No file chosen</span>
        </div>
        <div id="imagePreview" class="image-preview"></div>
      </div>

      <div class="form-group">
        <label for="projectName">Project Name</label>
        <input type="text" id="projectName" name="projectName" required>
      </div>

      <div class="form-group">
        <label for="projectDescription">Project Description</label>
        <div id="projectDescription" class="quill-editor"></div>
        <input type="hidden" id="projectDescriptionInput" name="projectDescription">
      </div>

      <div class="form-group">
        <label for="projectUrl">URL</label>
        <input type="url" id="projectUrl" name="projectUrl" placeholder="Leave empty for no link">
      </div>

      <div class="form-group">
        <label for="projectUrlTwo">Development URL</label>
        <input type="url" id="projectUrlTwo" name="projectUrlTwo" placeholder="Optional development URL">
      </div>

      <div class="form-group">
        <label for="projectCTA">Call to Action Text</label>
        <input type="text" id="projectCTA" name="projectCTA" placeholder="e.g., View Project">
      </div>

      <button type="submit" class="btn-login">Add Project</button>
    </form>
  </div>
</div>
