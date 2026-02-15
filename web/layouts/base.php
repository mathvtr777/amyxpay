<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="transparent" data-width="default" data-menu-styles="light" data-toggled="close">

    <head>

        <!-- Meta Data -->
		<meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="URANOPAY">
        <meta name="Author" content="URANOPAY">
        <meta name="keywords" content="URANOPAY">
        
        <!-- TITLE -->
		<title>URANO PAY</title>

        <!-- FAVICON -->
        <link rel="icon" href="../img/logo.png" type="image/x-icon">

        <!-- BOOTSTRAP CSS -->
	    <link  id="style" href="<?php echo $baseUrl; ?>/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- STYLES CSS -->
        <link href="<?php echo $baseUrl; ?>/assets/css/styles.css" rel="stylesheet">
        
        <!-- ICONS CSS -->
        <link href="<?php echo $baseUrl; ?>/assets/icon-fonts/icons.css" rel="stylesheet">

        <?php include '../layouts/components/styles.php'; ?>
        
        <!-- MAIN JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/main.js"></script>

        <?php echo $styles; ?>

	</head>

    <body>

        <!-- SWITCHER -->
        <?php include '../layouts/components/switcher.php'; ?>

        <!-- END SWITCHER -->

        <!-- LOADER -->
        <div id="loader">
            <img src="<?php echo $baseUrl; ?>/assets/images/media/loader.svg" alt="">
        </div>
        <!-- END LOADER -->

        <!-- PAGE -->
        <div class="page">

            <!-- HEADER -->
            <?php include '../layouts/components/header.php'; ?>

            <!-- END HEADER -->

            <!-- SIDEBAR -->
            <?php include '../layouts/components/sidebar.php'; ?>

            <!-- END SIDEBAR -->

            <!-- MAIN-CONTENT -->
            <?php echo $content; ?>
            <!-- END MAIN-CONTENT -->

            <!-- FOOTER -->
            <?php include '../layouts/components/footer.php'; ?>

            <!-- END FOOTER -->

        </div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->
        <?php include '../layouts/components/scripts.php'; ?>

        <?php echo $scripts; ?>

        <!-- STICKY JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/sticky.js"></script>

        <!-- DEFAULTMENU JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/defaultmenu.js"></script>

        <!-- CUSTOM JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/custom.js"></script>
        
        <!-- CUSTOM-SWITCHER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/custom-switcher.js"></script>

        <!-- END SCRIPTS -->

    </body>
</html>