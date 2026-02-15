<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

    <head>

        <!-- Meta Data -->
		<meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="URANO PAY">
        <meta name="Author" content="URANO PAY">
        <meta name="keywords" content="URANO PAY">
        
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

        <!-- MAIN JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/authentication-main.js"></script>

        <?php echo $styles; ?>

	</head>

    <?php echo $body; ?>
        
        <?php echo $content; ?>

        <!-- SCRIPTS -->

        <!-- BOOTSTRAP JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

        <?php echo $scripts; ?>
        
        <!-- END SCRIPTS -->

	</body>
</html>

