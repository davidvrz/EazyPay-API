<?php
// file: view/layouts/welcome.php

$view = ViewManager::getInstance();
?>
<!DOCTYPE html>
<html>
<head>
	<title><?= htmlspecialchars($view->getVariable("title", "no title")) ?></title>
	<meta charset="utf-8">

	<link rel="stylesheet" href="../../assets/styles/global.css" type="text/css">
	
	<?= $view->getFragment("css") ?>
	<?= $view->getFragment("javascript") ?>

	<link rel="icon" href="../../assets/images/isotype.png" type="image/png">
</head>
<body>
	<header>
		<!-- <h1><?= i18n("Welcome to the Group App!") ?></h1> -->
		<?php include __DIR__ . '/../partials/navbar.php'; ?>
	</header>
	<main>
		<!-- flash message -->
		<div id="flash">
			<?= $view->popFlash() ?>
		</div>
		<?= $view->getFragment(ViewManager::DEFAULT_FRAGMENT) ?>
	</main>

	<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
