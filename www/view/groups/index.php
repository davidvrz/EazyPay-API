<?php
// file: view/groups/index.php

require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$groups = $view->getVariable("groups");
$currentuser = $view->getVariable("currentusername");

$view->setVariable("title", "Groups");
?>

<link rel="stylesheet" href="../../assets/styles/groups/index.css" type="text/css">

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>

    <h1 class="main-title"><?= i18n("Groups") ?></h1>

    <div class="groups-list">
        <?php foreach ($groups as $group): ?>
            <div class="group-card">
                <div class="group-info">
                    <h3><a href="index.php?controller=groups&amp;action=view&amp;id=<?= $group->getId() ?>"><?= htmlentities($group->getName()) ?></a></h3>
                    <p><?= i18n("Admin") ?>: <?= $group->getAdmin()->getUserName() ?></p>
                    <p><?= htmlentities($group->getDescription()) ?></p>
                </div>
                <?php if (isset($currentuser) && $currentuser == $group->getAdmin()->getUsername()): ?>
                    <div class="group-actions">
                        <form method="POST" action="index.php?controller=groups&amp;action=delete" id="delete_group_<?= $group->getId(); ?>" style="display: inline">
                            <input type="hidden" name="id" value="<?= $group->getId() ?>">
                            <a href="#" onclick="if (confirm('<?= i18n("are you sure?") ?>')) { document.getElementById('delete_group_<?= $group->getId() ?>').submit() }"><?= i18n("Delete") ?></a>
                        </form>
                        <a href="index.php?controller=groups&amp;action=edit&amp;id=<?= $group->getId() ?>"><?= i18n("Edit") ?></a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($currentuser)): ?>
        <a href="index.php?controller=groups&amp;action=add" class="add-group-btn"><?= i18n("Create group") ?></a>
    <?php endif; ?>
</div>
