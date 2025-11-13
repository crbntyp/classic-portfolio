<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $pageTitle ?? 'Carbontype'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <?php if (!isset($skipTypekit) || !$skipTypekit): ?>
    <link rel="stylesheet" href="https://use.typekit.net/abs2nmm.css" />
    <?php endif; ?>
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo ($pathPrefix ?? '') . 'css/main.css' . (isset($cssVersion) ? "?v={$cssVersion}" : ''); ?>" />
</head>
<body class="<?php echo $bodyClass ?? 'home-body'; ?>">
    <canvas id="particle-canvas"></canvas>
