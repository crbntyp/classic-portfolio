<?php
require_once 'includes/init.php';

$sql = "SELECT * FROM projects WHERE category = 'classic-portfolio' ORDER BY sort_order ASC";
$result = $mysqli->query($sql);

// Set page variables
$pageTitle = 'Carbontype Portfolio';
$bodyClass = 'portfolio-body';
$pathPrefix = '';
?>
<?php include 'includes/html-head.php'; ?>

        <?php include_once 'includes/analytics.php'; ?>

        <?php include 'includes/user-nav.php'; ?>
        <?php include 'includes/main-nav.php'; ?>
        <?php include 'includes/login-modal.php'; ?>
        <?php include 'includes/about-us-modal.php'; ?>

        <div class="portfolio-container">
            <div class="portfolio-content">
                <div class="portfolio-header">
                    <div class="portfolio-header__content">
                        <img src="logo.png" class="portfolio-header__logo" alt="Carbontype Logo" />
                        <h1 class="portfolio-header__title">crbntyp</h1>
                    </div>
                </div>

                <section class="carousel">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Clean up values
                $projectID = $row["projectID"];
                $projectHeading = $row["projectHeading"];
                $projectDescription = $row["projectDescription"];
                $projectTeaser = $row["projectTeaser"];
                $projectCTA = $row["projectCTA"];
                ?>
                <div id="project-<?php echo $projectID; ?>" class="slide slide-fullscreen">
                    <div class="holder">
                        <a href="#" class="project-link"
                           data-project-id="<?php echo $projectID; ?>"
                           data-project-heading="<?php echo htmlspecialchars($projectHeading); ?>"
                           data-project-description="<?php echo htmlspecialchars($projectDescription); ?>">
                            <img src="uploads/<?php echo $projectTeaser; ?>"
                                 alt="<?php echo $projectHeading; ?>"
                                 class="holder-img">
                            <div class="project-heading">
                                <span><?php echo $projectHeading; ?></span>
                            </div>
                            <div class="color-palette" id="colorPalette">
                                <div class="color-palette__circle"></div>
                                <div class="color-palette__circle"></div>
                                <div class="color-palette__circle"></div>
                            </div>
                        </a>
                    </div>
                </div>
        <?php
            } // while
        } else {
            ?>
            <div class="slide slide-fullscreen">
                <div class="base">
                    <h2 class="color-white">No projects found.</h2>
                </div>
            </div>
        <?php
        }
        ?>
                </section>
            </div><!-- .portfolio-content -->
        </div><!-- .portfolio-container -->

        <!-- Project Description Modal -->
        <div id="projectModal" class="project-modal">
            <div class="project-modal__overlay"></div>
            <button class="project-modal__close" aria-label="Close modal">
                <i class="lni lni-close"></i>
            </button>
            <div class="project-modal__content">
                <h2 class="project-modal__title"></h2>
                <div class="project-modal__description"></div>
            </div>
        </div>

<?php include 'includes/html-close.php'; ?>