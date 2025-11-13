<?php
require_once 'auth-check.php';
require_once '../includes/init.php';

// Get stats
$projectsCount = $mysqli->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
$blobCount = $mysqli->query("SELECT COUNT(*) as count FROM projects WHERE blobEntry = 1")->fetch_assoc()['count'];
$classicCount = $projectsCount - $blobCount;

// Fetch all projects (non-blob first, then blob, ordered by ID within each type)
$projectsQuery = "SELECT projectID, projectHeading, projectTeaser, blobEntry FROM projects ORDER BY blobEntry ASC, projectID ASC";
$projectsResult = $mysqli->query($projectsQuery);

// Check if email column exists, if not add it
$columnsCheck = $mysqli->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($columnsCheck->num_rows == 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL DEFAULT NULL AFTER username");
}

// Fetch all users
$usersQuery = "SELECT userID, username, email FROM users ORDER BY username ASC";
$usersResult = $mysqli->query($usersQuery);

// Set page variables
$pageTitle = 'Admin Dashboard - Carbontype';
$bodyClass = 'admin-body';
$pathPrefix = '../';
?>
<?php include '../includes/html-head.php'; ?>

    <div id="bgRotator" class="bg-rotator"></div>

    <?php include '../includes/user-nav.php'; ?>

    <div class="admin-container">
        <div class="admin-brand">
            <img src="../logo.png" class="admin-brand__logo" alt="Carbontype Logo" />
            <h2 class="admin-brand__title">crbntyp // admin</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Projects</h3>
                <p class="stat-number"><?php echo $projectsCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Classic Portfolio</h3>
                <p class="stat-number"><?php echo $classicCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>App &amp; Artwork Showcase</h3>
                <p class="stat-number"><?php echo $blobCount; ?></p>
            </div>
        </div>

        <div class="admin-dashboard">
            <!-- Column 1: Projects Management (Tabs) -->
            <div class="admin-column admin-column--projects">
                <div class="admin-section">
                    <!-- Tabs Navigation -->
                    <div class="admin-tabs">
                        <button class="admin-tab active" data-tab="projects-list">
                            <i class="las la-box"></i>
                            <span>All Projects (<?php echo $projectsCount; ?>)</span>
                        </button>
                        <button class="admin-add-btn" id="addProjectTrigger">
                            <i class="las la-plus"></i>
                            <span>Add Project</span>
                        </button>
                    </div>

                    <!-- Tab Content: Projects List -->
                    <div class="admin-tab-content active" id="projects-list">
                        <div class="projects-grid">
                            <?php while ($project = $projectsResult->fetch_assoc()): ?>
                                <div class="project-item <?php echo $project['blobEntry'] == 1 ? 'project-item--blob' : ''; ?>" data-project-id="<?php echo $project['projectID']; ?>">
                                    <?php if ($project['blobEntry'] != 1): ?>
                                        <div class="project-item__image">
                                            <?php if ($project['projectTeaser']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($project['projectTeaser']); ?>"
                                                     alt="<?php echo htmlspecialchars($project['projectHeading']); ?>">
                                            <?php else: ?>
                                                <div class="project-item__placeholder">
                                                    <i class="las la-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="project-item__info <?php echo $project['blobEntry'] == 1 ? 'project-item__info--blob' : ''; ?>">
                                        <?php if ($project['blobEntry'] == 1): ?>
                                            <span class="project-item__badge-inline">App or Artwork Showcase</span>
                                        <?php endif; ?>
                                        <div class="project-item__details">
                                            <h4 class="project-item__title"><?php echo htmlspecialchars($project['projectHeading']); ?></h4>
                                            <span class="project-item__id">ID: <?php echo $project['projectID']; ?></span>
                                        </div>
                                        <div class="project-item__actions">
                                            <button class="project-item__action project-item__edit"
                                                    data-project-id="<?php echo $project['projectID']; ?>"
                                                    title="Edit project">
                                                <i class="las la-edit"></i>
                                            </button>
                                            <button class="project-item__action project-item__delete"
                                                    data-project-id="<?php echo $project['projectID']; ?>"
                                                    data-project-name="<?php echo htmlspecialchars($project['projectHeading']); ?>"
                                                    title="Delete project">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Users List (Hidden for now) -->
            <?php if (false): ?>
            <div class="admin-column admin-column--users">
                <div class="admin-section">
                    <h3 class="admin-section__title">Users (<?php echo $usersResult->num_rows; ?>)</h3>
                    <div class="users-list">
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <div class="user-item">
                                <div class="user-item__icon">
                                    <i class="las la-user-circle"></i>
                                </div>
                                <div class="user-item__info">
                                    <h4 class="user-item__name"><?php echo htmlspecialchars($user['username']); ?></h4>
                                    <span class="user-item__email">
                                        <?php echo $user['email'] ? htmlspecialchars($user['email']) : 'No email set'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal" id="editProjectModal">
        <div class="modal__overlay"></div>
        <div class="modal__content modal__content--wide">
            <button class="modal__close" id="editProjectModalClose">
                <i class="las la-times"></i>
            </button>

            <!-- Upload overlay for loading and success states -->
            <div class="upload-overlay" id="editUploadOverlay">
                <div class="upload-spinner" id="editUploadSpinner"></div>
                <div class="upload-success" id="editUploadSuccess">
                    <i class="las la-check"></i>
                </div>
                <div class="upload-text" id="editUploadText">Updating...</div>
            </div>

            <img src="../logo.png" alt="Carbontype Logo" class="modal__logo" />

            <div class="add-project-layout">
                <!-- Left: Form Fields -->
                <div class="add-project-form">
                    <form class="login-form" id="editProjectForm" enctype="multipart/form-data">
                        <div id="editProjectError" class="error-message" style="display: none;"></div>

                        <input type="hidden" id="editProjectID" name="projectID">

                        <div class="form-group">
                            <label for="editCategory">Category</label>
                            <select id="editCategory" name="category" required>
                                <option value="">Select Category</option>
                                <option value="classic-portfolio">Classic Portfolio</option>
                                <option value="recent-artwork">Recent Artwork</option>
                                <option value="artwork-portfolio">Artwork Portfolio</option>
                                <option value="vibes">Vibes</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editProjectName">Project Name</label>
                            <input type="text" id="editProjectName" name="projectName" required>
                        </div>

                        <div class="form-group">
                            <label for="editProjectDescription">Project Description</label>
                            <div id="editProjectDescription" class="quill-editor"></div>
                            <input type="hidden" id="editProjectDescriptionInput" name="projectDescription">
                        </div>

                        <div class="form-group">
                            <label for="editProjectUrl">URL</label>
                            <input type="url" id="editProjectUrl" name="projectUrl" placeholder="Leave empty for no link">
                        </div>

                        <div class="form-group">
                            <label for="editProjectUrlTwo">Development URL</label>
                            <input type="url" id="editProjectUrlTwo" name="projectUrlTwo" placeholder="Optional development URL">
                        </div>

                        <div class="form-group">
                            <label for="editProjectCTA">Call to Action Text</label>
                            <input type="text" id="editProjectCTA" name="projectCTA" placeholder="e.g., View Project">
                        </div>

                        <div class="form-group" id="editImageUploadGroup">
                            <label>Change Project Image (Optional)</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="editProjectImage" name="projectImage" accept="image/*" class="file-input">
                                <label for="editProjectImage" class="file-input-label">
                                    <i class="las la-cloud-upload-alt"></i>
                                    <span class="file-input-text">Upload New Image</span>
                                </label>
                                <span class="file-name" id="editFileName">No file chosen</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-login">Update Project</button>
                    </form>
                </div>

                <!-- Right: Image Preview -->
                <div class="add-project-preview">
                    <div id="editImagePreview" class="image-preview-large">
                        <div class="image-preview-placeholder">
                            <i class="las la-image"></i>
                            <span>Current Image</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal" id="addProjectModal">
        <div class="modal__overlay"></div>
        <div class="modal__content modal__content--wide">
            <button class="modal__close" id="addProjectModalClose">
                <i class="las la-times"></i>
            </button>

            <!-- Upload overlay for loading and success states -->
            <div class="upload-overlay" id="addUploadOverlay">
                <div class="upload-spinner" id="addUploadSpinner"></div>
                <div class="upload-success" id="addUploadSuccess">
                    <i class="las la-check"></i>
                </div>
                <div class="upload-text" id="addUploadText">Uploading...</div>
            </div>

            <img src="../logo.png" alt="Carbontype Logo" class="modal__logo" />

            <div class="add-project-layout">
                <!-- Left: Form Fields -->
                <div class="add-project-form">
                    <form class="login-form" id="addProjectForm" enctype="multipart/form-data">
                        <div id="addProjectError" class="error-message" style="display: none;"></div>

                        <div class="form-group">
                            <label for="addCategory">Category</label>
                            <select id="addCategory" name="category" required>
                                <option value="">Select Category</option>
                                <option value="classic-portfolio">Classic Portfolio</option>
                                <option value="recent-artwork">Recent Artwork</option>
                                <option value="artwork-portfolio">Artwork Portfolio</option>
                                <option value="vibes">Vibes</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="addProjectName">Project Name</label>
                            <input type="text" id="addProjectName" name="projectName" required>
                        </div>

                        <div class="form-group">
                            <label for="addProjectDescription">Project Description</label>
                            <div id="addProjectDescription" class="quill-editor"></div>
                            <input type="hidden" id="addProjectDescriptionInput" name="projectDescription">
                        </div>

                        <div class="form-group">
                            <label for="addProjectUrl">URL</label>
                            <input type="url" id="addProjectUrl" name="projectUrl" placeholder="Leave empty for no link">
                        </div>

                        <div class="form-group">
                            <label for="addProjectUrlTwo">Development URL</label>
                            <input type="url" id="addProjectUrlTwo" name="projectUrlTwo" placeholder="Optional development URL">
                        </div>

                        <div class="form-group">
                            <label for="addProjectCTA">Call to Action Text</label>
                            <input type="text" id="addProjectCTA" name="projectCTA" placeholder="e.g., View Project">
                        </div>

                        <div class="form-group" id="addImageUploadGroup" style="display: none;">
                            <label>Project Image</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="addProjectImage" name="projectImage" accept="image/*" class="file-input">
                                <label for="addProjectImage" class="file-input-label">
                                    <i class="las la-cloud-upload-alt"></i>
                                    <span class="file-input-text">Upload Image</span>
                                </label>
                                <span class="file-name" id="addFileName">No file chosen</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-login">Add Project</button>
                    </form>
                </div>

                <!-- Right: Image Preview -->
                <div class="add-project-preview">
                    <div id="addImagePreview" class="image-preview-large">
                        <div class="image-preview-placeholder">
                            <i class="las la-image"></i>
                            <span>Image Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/html-close.php'; ?>
