<?php
//file: view/layouts/default.php

$view = ViewManager::getInstance();
$currentuser = $view->getVariable("currentusername");

?><!DOCTYPE html>
<html>
<head>
	<title><?= $view->getVariable("title", "no title") ?></title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="assets/styles/global.css" type="text/css">
	<!-- enable ji18n() javascript function to translate inside your scripts -->
	<script src="index.php?controller=language&amp;action=i18njs">
	</script>
	<?= $view->getFragment("css") ?>
	<?= $view->getFragment("javascript") ?>
</head>
<body>
	<header>
		<?php include __DIR__ . '/../partials/navbar.php'; ?>
	</header>

	<main>
		<div id="flash">
			<?= $view->popFlash() ?>
		</div>

		<?php if (isset($currentuser)): ?> 
			<?= $view->getFragment(ViewManager::DEFAULT_FRAGMENT) ?>
		<?php else: ?>
			<?php include __DIR__ . '/../users/login.php'; ?>
		<?php endif ?>
		
	</main>

	<footer>
		<?php include __DIR__ . '/../partials/footer.php'; ?>
	</footer>

</body>
</html>
