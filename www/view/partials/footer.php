<?php
// file: view/partials/footer.php
?>
<link rel="stylesheet" type="text/css" href="../../assets/styles/footer.css">

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section about">
            <h2 class="footer-title">Sobre nosotros</h2>
            <p>Somos una compañía líder en el mercado de pagos online. Nuestro objetivo es facilitar la vida de nuestros clientes.</p>
        </div>

        <div class="footer-section language">
            <h2 class="footer-title">Idiomas</h2>
            <?php include(__DIR__."/../layouts/language_select_element.php"); ?>
        </div>

        <div class="footer-section contact">
            <h2 class="footer-title">Contacta con nosotros</h2>
            <ul>
                <li>Email: info@eazypay.com</li>
                <li>Phone: +34 656 34 22 43</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2024 EazyPay | Todos los derechos reservados</p>
    </div>
</footer>
