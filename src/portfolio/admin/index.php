<?php
require_once 'auth-check.php';
require_once '../includes/init.php';

// Check if email column exists, if not add it
$columnsCheck = $mysqli->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($columnsCheck->num_rows == 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL DEFAULT NULL AFTER username");
}

// Check if sort_order column exists, if not add it and initialize values
$sortOrderCheck = $mysqli->query("SHOW COLUMNS FROM projects LIKE 'sort_order'");
if ($sortOrderCheck->num_rows == 0) {
    $mysqli->query("ALTER TABLE projects ADD COLUMN sort_order INT NOT NULL DEFAULT 0");
    // Initialize sort_order based on current projectID order
    $mysqli->query("SET @row_number = 0");
    $mysqli->query("UPDATE projects SET sort_order = (@row_number := @row_number + 1) ORDER BY projectID ASC");
}

// Check if category column exists, if not add it and migrate from blobEntry
$categoryCheck = $mysqli->query("SHOW COLUMNS FROM projects LIKE 'category'");
if ($categoryCheck->num_rows == 0) {
    $mysqli->query("ALTER TABLE projects ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'classic-portfolio'");
    // Migrate blobEntry values to category
    $mysqli->query("UPDATE projects SET category = 'recent-artwork' WHERE blobEntry = 1");
    $mysqli->query("UPDATE projects SET category = 'classic-portfolio' WHERE blobEntry = 0 OR blobEntry IS NULL");
}

// Get stats
$projectsCount = $mysqli->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
$shrugCount = $mysqli->query("SELECT COUNT(*) as count FROM shrug_entries")->fetch_assoc()['count'];

// Fetch all shrug entries for admin
$shrugQuery = "SELECT id, title, slug, content, tags, published, sort_order, created_at FROM shrug_entries ORDER BY sort_order ASC, created_at DESC";
$shrugResult = $mysqli->query($shrugQuery);
$shrugEntries = [];
if ($shrugResult) {
    while ($row = $shrugResult->fetch_assoc()) {
        $shrugEntries[] = $row;
    }
}

// Fetch all projects ordered by sort_order (user-defined drag order)
$projectsQuery = "SELECT projectID, projectHeading, projectTeaser, category FROM projects ORDER BY sort_order ASC";
$projectsResult = $mysqli->query($projectsQuery);

// Category display labels
$categoryLabels = [
    'classic-portfolio' => 'Classic',
    'recent-artwork' => 'Artwork',
    'artwork-portfolio' => 'Art Portfolio',
    'vibes' => 'Vibes'
];

// Fetch all users
$usersQuery = "SELECT userID, username, email FROM users ORDER BY username ASC";
$usersResult = $mysqli->query($usersQuery);

// Set page variables
$pageTitle = 'Admin Dashboard - Carbontype';
$bodyClass = 'admin-body';
$pathPrefix = '../';
?>
<?php include '../includes/html-head.php'; ?>

    <!-- Voice AI Blob Background -->
    <div class="voice-blob-container" id="voice-blob">
        <div class="voice-blob voice-blob--primary"></div>
        <div class="voice-blob voice-blob--secondary"></div>
        <div class="voice-blob voice-blob--tertiary"></div>
        <div class="voice-blob voice-blob--core"></div>
    </div>

    <?php include '../includes/about-us-modal.php'; ?>

    <div class="admin-container">
        <div class="admin-brand">
            <h1 class="admin-brand__logo-text">crbntyp</h1>
            <h3 class="admin-brand__subtitle">Custom portfolio CMS system, tailored for crbntyp...</h3>
        </div>

        <div class="admin-main">
            <!-- Tab Navigation -->
            <div class="admin-tabs">
                <div class="admin-tabs__left">
                    <button class="admin-tab active" data-tab="projects">Projects (<?php echo $projectsCount; ?>)</button>
                    <button class="admin-tab" data-tab="shrug">Shrug (<?php echo $shrugCount; ?>)</button>
                    <button class="admin-tab" data-tab="bookmarks">Bookmarks</button>
                </div>
                <div class="admin-tabs__right">
                    <a href="/" class="admin-tab">Home</a>
                    <a href="<?php echo $pathPrefix; ?>logout.php" class="admin-tab">Logout</a>
                </div>
            </div>

            <!-- Mobile Tab Dropdown -->
            <div class="admin-tabs-mobile">
                <div class="admin-tabs-mobile__left">
                    <button class="admin-tabs-mobile__trigger" id="mobileTabTrigger">
                        <span id="mobileTabLabel">Projects (<?php echo $projectsCount; ?>)</span>
                        <i class="lni lni-chevron-down"></i>
                    </button>
                    <div class="admin-tabs-mobile__dropdown" id="mobileTabDropdown">
                        <button class="admin-tabs-mobile__item active" data-tab="projects">Projects (<?php echo $projectsCount; ?>)</button>
                        <button class="admin-tabs-mobile__item" data-tab="shrug">Shrug (<?php echo $shrugCount; ?>)</button>
                        <button class="admin-tabs-mobile__item" data-tab="bookmarks">Bookmarks</button>
                    </div>
                </div>
                <div class="admin-tabs-mobile__right">
                    <a href="/" class="admin-tabs-mobile__link">Home</a>
                    <a href="<?php echo $pathPrefix; ?>logout.php" class="admin-tabs-mobile__link">Logout</a>
                </div>
            </div>

            <!-- Projects Tab -->
            <div class="admin-tab-content active" id="tab-projects">
                <div class="projects-tab-header">
                    <span class="projects-tab-header__title">All Projects</span>
                    <button type="button" class="project-add-btn" id="openAddProjectModal">Project +</button>
                </div>
                <div class="projects-grid">
                <?php while ($project = $projectsResult->fetch_assoc()): ?>
                    <?php $category = $project['category'] ?? 'classic-portfolio'; ?>
                    <div class="project-item" data-project-id="<?php echo $project['projectID']; ?>">
                        <div class="project-item__image">
                            <?php if ($project['projectTeaser']): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($project['projectTeaser']); ?>"
                                     alt="<?php echo htmlspecialchars($project['projectHeading']); ?>">
                            <?php else: ?>
                                <div class="project-item__placeholder">
                                    <i class="lni lni-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="project-item__info">
                            <div class="project-item__details">
                                <h4 class="project-item__title"><?php echo htmlspecialchars($project['projectHeading']); ?></h4>
                                <span class="project-item__id">ID: <?php echo $project['projectID']; ?></span>
                                <span class="project-item__category project-item__category--<?php echo $category; ?>">
                                    <?php echo $categoryLabels[$category] ?? ucfirst($category); ?>
                                </span>
                            </div>
                            <div class="project-item__actions">
                                <button class="project-item__action project-item__edit"
                                        data-project-id="<?php echo $project['projectID']; ?>"
                                        title="Edit project">
                                    <i class="lni lni-pen-to-square"></i>
                                </button>
                                <button class="project-item__action project-item__delete"
                                        data-project-id="<?php echo $project['projectID']; ?>"
                                        data-project-name="<?php echo htmlspecialchars($project['projectHeading']); ?>"
                                        title="Delete project">
                                    <i class="lni lni-trash-3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            </div>

            <!-- Shrug Tab -->
            <div class="admin-tab-content" id="tab-shrug">
                <div class="shrug-admin">
                    <!-- Left: Form -->
                    <div class="shrug-admin__form">
                        <div class="shrug-admin__form-header">
                            <span class="shrug-admin__form-title" id="shrugFormTitle">Add New Shrug</span>
                            <button type="button" class="shrug-new-btn" id="shrugNewBtn">Shrug +</button>
                        </div>
                        <form id="shrugForm" class="shrug-form">
                            <input type="hidden" id="shrugId" name="id" value="">

                            <div class="form-group">
                                <label for="shrugTitle">Title</label>
                                <input type="text" id="shrugTitle" name="title" required>
                            </div>

                            <div class="form-group">
                                <label for="shrugSlug">Slug</label>
                                <input type="text" id="shrugSlug" name="slug" required>
                            </div>

                            <div class="form-group">
                                <label for="shrugContent">Content</label>
                                <div id="shrugContentEditor" class="quill-editor"></div>
                                <input type="hidden" id="shrugContent" name="content">
                            </div>

                            <div class="form-group">
                                <label for="shrugTags">Tags (comma separated)</label>
                                <input type="text" id="shrugTags" name="tags" placeholder="tag1, tag2, tag3">
                            </div>

                            <div class="form-group form-group--checkbox">
                                <label>
                                    <input type="checkbox" id="shrugPublished" name="published" value="1" checked>
                                    <span>Published</span>
                                </label>
                            </div>

                            <div class="shrug-form__actions">
                                <button type="submit" class="btn-login" id="shrugSubmitBtn">Save Shrug</button>
                            </div>
                            <div class="shrug-success-message" id="shrugSuccessMessage" style="display: none;">
                                <i class="lni lni-checkmark-circle"></i>
                                <span id="shrugSuccessText">Saved successfully!</span>
                            </div>
                        </form>
                    </div>

                    <!-- Right: List -->
                    <div class="shrug-admin__list">
                        <span class="shrug-admin__list-title">All Shrugs (<?php echo count($shrugEntries); ?>)</span>
                        <div class="shrug-list-admin">
                            <?php foreach ($shrugEntries as $shrug): ?>
                            <div class="shrug-list-item" data-id="<?php echo $shrug['id']; ?>">
                                <div class="shrug-list-item__info">
                                    <span class="shrug-list-item__title"><?php echo htmlspecialchars($shrug['title']); ?></span>
                                    <span class="shrug-list-item__meta">
                                        <?php echo date('M j, Y', strtotime($shrug['created_at'])); ?>
                                        <?php if (!$shrug['published']): ?>
                                            <span class="shrug-list-item__draft">Draft</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="shrug-list-item__actions">
                                    <button type="button" class="shrug-list-item__edit" data-id="<?php echo $shrug['id']; ?>" title="Edit">
                                        <i class="lni lni-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="shrug-list-item__delete" data-id="<?php echo $shrug['id']; ?>" data-title="<?php echo htmlspecialchars($shrug['title']); ?>" title="Delete">
                                        <i class="lni lni-trash-3"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($shrugEntries)): ?>
                            <div class="shrug-list-empty">No shrug posts yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shrug data for JS -->
            <script>
                window.shrugData = <?php echo json_encode($shrugEntries); ?>;
            </script>

            <!-- Bookmarks Tab -->
            <div class="admin-tab-content" id="tab-bookmarks">
                <div class="bookmarks-admin">
                    <div class="bookmarks-admin__header">
                        <span class="bookmarks-admin__title">Edit Bookmarks</span>
                    </div>
                    <p class="bookmarks-admin__hint">Format: <code>[https://url.com] Display Text</code> — one per line</p>
                    <div id="bookmarksEditor" class="quill-editor"></div>
                    <div class="bookmarks-admin__actions">
                        <button type="button" class="btn-login" id="bookmarksSaveBtn">Save Bookmarks</button>
                    </div>
                    <div class="shrug-success-message" id="bookmarksSuccessMessage" style="display: none;">
                        <i class="lni lni-checkmark-circle"></i>
                        <span>Bookmarks saved successfully!</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal" id="editProjectModal">
        <div class="modal__overlay"></div>
        <button class="modal__close" id="editProjectModalClose">close window</button>
        <div class="modal__content modal__content--project">
            <!-- Upload overlay for loading and success states -->
            <div class="upload-overlay" id="editUploadOverlay">
                <div class="upload-spinner" id="editUploadSpinner">
                    <i class="lni lni-spinner-3"></i>
                </div>
                <div class="upload-success" id="editUploadSuccess">
                    <i class="lni lni-check-circle-1"></i>
                </div>
                <div class="upload-text" id="editUploadText">Updating...</div>
            </div>

            <h2 class="modal__logo-text">crbntyp</h2>
            <p class="modal__subtitle">Edit project details</p>

            <form class="login-form" id="editProjectForm" enctype="multipart/form-data">
                <div id="editProjectError" class="error-message" style="display: none;"></div>
                <input type="hidden" id="editProjectID" name="projectID">

                <div class="form-grid">
                    <div class="form-column">
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
                    </div>

                    <div class="form-column">
                        <div class="form-group" id="editImageUploadGroup">
                            <label>Project Image</label>
                            <div id="editImagePreview" class="image-preview-box"></div>
                            <div class="file-input-wrapper">
                                <input type="file" id="editProjectImage" name="projectImage" accept="image/*" class="file-input">
                                <label for="editProjectImage" class="file-input-label">
                                    <i class="lni lni-upload-1"></i>
                                    <span class="file-input-text">Upload New Image</span>
                                </label>
                                <span class="file-name" id="editFileName">No file chosen</span>
                            </div>
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
                    </div>
                </div>

                <button type="submit" class="btn-login">Update Project</button>
            </form>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal" id="addProjectModal">
        <div class="modal__overlay"></div>
        <button class="modal__close" id="addProjectModalClose">close window</button>
        <div class="modal__content modal__content--project">
            <!-- Upload overlay for loading and success states -->
            <div class="upload-overlay" id="addUploadOverlay">
                <div class="upload-spinner" id="addUploadSpinner">
                    <i class="lni lni-spinner-3"></i>
                </div>
                <div class="upload-success" id="addUploadSuccess">
                    <i class="lni lni-check-circle-1"></i>
                </div>
                <div class="upload-text" id="addUploadText">Uploading...</div>
            </div>

            <h2 class="modal__logo-text">crbntyp</h2>
            <p class="modal__subtitle">Add a new project to your portfolio</p>

            <form class="login-form" id="addProjectForm" enctype="multipart/form-data">
                <div id="addProjectError" class="error-message" style="display: none;"></div>

                <div class="form-grid">
                    <div class="form-column">
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
                    </div>

                    <div class="form-column">
                        <div class="form-group" id="addImageUploadGroup">
                            <label>Project Image</label>
                            <div id="addImagePreview" class="image-preview-box"></div>
                            <div class="file-input-wrapper">
                                <input type="file" id="addProjectImage" name="projectImage" accept="image/*" class="file-input">
                                <label for="addProjectImage" class="file-input-label">
                                    <i class="lni lni-upload-1"></i>
                                    <span class="file-input-text">Upload Image</span>
                                </label>
                                <span class="file-name" id="addFileName">No file chosen</span>
                            </div>
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
                    </div>
                </div>

                <button type="submit" class="btn-login">Add Project</button>
            </form>
        </div>
    </div>

<?php include '../includes/html-close.php'; ?>
