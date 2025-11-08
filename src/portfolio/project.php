<?php
require_once '../../includes/init.php';

$projectIDurl = $_GET["id"];

$sql = "SELECT * FROM projects WHERE projectID = '$projectIDurl'";
$result = $mysqli->query($sql);
while ($row = $result->fetch_assoc()) {
    $projectHeading = $row["projectHeading"];
    $projectDescription = $row["projectDescription"];
    $projectTeaser = $row["projectTeaser"];
    $projectURL = $row["url"];
    $projectURL_two = $row["url_two"];
}

$nextQuery = "SELECT * FROM projects WHERE projectID > $projectIDurl AND blobEntry = 0 ORDER BY projectID ASC LIMIT 1";
$nextResult = $mysqli->query($nextQuery) or die($mysqli->error);
while ($row = $nextResult->fetch_assoc()) {
    $nextID = $row["projectID"];
    $nextHeading = $row["projectHeading"];
}

$prevQuery = "SELECT * FROM projects WHERE projectID < $projectIDurl AND blobEntry = 0 ORDER BY projectID DESC LIMIT 1";
$prevResult = $mysqli->query($prevQuery) or die($mysqli->error);
while ($row = $prevResult->fetch_assoc()) {
    $prevID = $row["projectID"];
    $prevHeading = $row["projectHeading"];
}

// Set page variables
$pageTitle = $projectHeading;
$bodyClass = 'project-body';
$pathPrefix = '../../';
?>
<?php include '../../includes/html-head.php'; ?>

    <?php include_once '../../includes/analytics.php'; ?>
    <nav class="main absolute">
        Carbontype <span>/ Design &amp; Development</span>
        <a href="./"><i class="fa fa-angle-left"></i></a>
    </nav>
    <section class="fullscreen">
        <section class="profile-content-container">
            <header class="data">
                <div class="the-projectID">
                    <?php echo $projectIDurl ?>
                </div>
                
                <?php if ($prevID != null) { ?>
                    <div class="next-prev prev-item">
                        <a class="" href="project.php?id=<?php echo $prevID; ?>"><span class="screen"><?php echo $prevHeading; ?></span><span class="handheld">Previous</span></a>
                    </div>
                <?php 
            } ?>

                <?php if ($nextID != null) { ?>
                    <div class="next-prev next-item">
                        <a class="" href="project.php?id=<?php echo $nextID; ?>"><span class="screen"><?php echo $nextHeading; ?></span><span class="handheld">Next</span></a>
                    </div>
                <?php 
            } ?>

                <div class="data-container">
                    <h4 class="color-white text-uppercase tag"><?php echo $projectHeading; ?></h4>
                    <h1 class="h2 color-white mb-3"><?php echo $projectDescription; ?></h1>

                    <?php if ($projectURL != null) { ?>
                    <a href="<?php echo $projectURL; ?>" class="btn btn-white">Concept work</i> </a>
                    <?php 
                } ?>

                    <?php if ($projectURL_two != null) { ?>
                        <a href="<?php echo $projectURL_two; ?>" class="btn btn-ghost-white">Integration work</i> </a>
                    <?php 
                } ?>
                </div>

            </header>
            <aside class="visual" style="background-image:url('../../img/uploads/<?php echo $projectTeaser; ?>')">

            </aside>
        </section>
    </section>

<?php include '../../includes/html-close.php'; ?>