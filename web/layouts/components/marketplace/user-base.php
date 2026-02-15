<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-nav-style="menu-hover" data-menu-position="fixed" data-theme-mode="light">

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

        <?php include 'layouts/components/marketplace/styles.php'; ?>

        <!-- LANDING-MAIN JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/landing-main.js"></script>
        
        <?php echo $styles; ?>

	</head>

    <body class="landing-body">

        <!-- SWITCHER -->
        <?php include 'layouts/components/marketplace/switcher.php'; ?>

        <!-- END SWITCHER -->

        <!-- PAGE -->
		<div class="landing-page-wrapper">

            <!-- HEADER -->
            <?php include 'layouts/components/marketplace/user-header.php'; ?>

            <!-- END HEADER -->

            <!-- SIDEBAR -->
            <?php include 'layouts/components/marketplace/user-sidebar.php'; ?>

            <!-- END SIDEBAR -->

            <!-- MAIN-CONTENT -->
            <?php echo $content; ?>

            <!-- FOOTER -->
            <?php include 'layouts/components/marketplace/footer.php'; ?>

            <!-- FOOTER -->

            <!-- END MAIN-CONTENT -->

		</div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->
        <?php include 'layouts/components/marketplace/scripts.php'; ?>

        <?php echo $scripts; ?>
        
        <!-- Landing JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/landing.js"></script>

        <!-- STICKY Landing JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/sticky-landing.js"></script>

        <!-- END SCRIPTS -->

	</body>
</html>
