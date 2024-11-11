<?php
// file: view/partials/footer.php
?>

<link rel="stylesheet" type="text/css" href="../../assets/styles/partials/footer.css">

<footer class="footer">
    <div class="footer-content">
        <section class="footer-section about">
            <h2 class="footer-title"><?= i18n("About us") ?></h2>
            <p><?= i18n("We are a leading company in the online payments market. Our goal is to make our customers lives easier.") ?></p>
        </section>

        <section class="footer-section language">
            <h2 class="footer-title"><?= i18n("Languages") ?></h2>
            <?php include(__DIR__."/../layouts/language_select_element.php"); ?>
        </section>

        <section class="footer-section contact">
            <h2 class="footer-title"><?= i18n("Contact with us") ?></h2>
            <ul>
                <li><?= i18n("Email") ?>: info@eazypay.com</li>
                <li><?= i18n("Phone") ?>: +34 656 34 22 43</li>
            </ul>
        </section>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date("Y") ?> EazyPay | <?= i18n("All rights reserved") ?></p>
    </div>
</footer>
