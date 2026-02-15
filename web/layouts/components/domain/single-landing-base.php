<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

    <head>

        <!-- Meta Data -->
        <meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="Mamix - PHP Bootstrap 5 Premium Admin & Dashboard Template">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="keywords" content="phpadmin, php template, admin panel, admin, admin dashboard, php admin panel, admin dashboard ui, php admin, dashboard, php framework, admin dashboard template, bootstrap dashboard, admin theme, admin panel template, php developer">

        <!-- TITLE -->
        <title>Mamix - PHP Bootstrap 5 Premium Admin &amp; Dashboard Template </title>

        <!-- FAVICON -->
        <link rel="icon" href="<?php echo $baseUrl; ?>/assets/images/brand-logos/favicon.ico" type="image/x-icon">

        <!-- BOOTSTRAP CSS -->
        <link  id="style" href="<?php echo $baseUrl; ?>/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- STYLES CSS -->
        <link href="<?php echo $baseUrl; ?>/assets/css/styles.css" rel="stylesheet">

        <!-- ICONS CSS -->
        <link href="<?php echo $baseUrl; ?>/assets/icon-fonts/icons.css" rel="stylesheet">
        
        <!-- MAIN Js -->
        <script src="<?php echo $baseUrl; ?>/assets/js/authentication-main.js"></script>

        <?php echo $styles; ?>

	</head>

    <body class="bg-white">

            <!-- MAIN-CONTENT -->
            <?php echo $content; ?>

            <!-- END MAIN-CONTENT -->

		</div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->

        <!-- Bootstrap JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

        <?php echo $scripts; ?>

        <!-- END SCRIPTS -->

	</body>
</html>
