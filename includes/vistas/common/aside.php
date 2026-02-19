<?php require_once __DIR__.'/includes/config.php' ?>
<aside>
    <section>
        <h3>Características</h3>
        <ul>
            <?php

            //Entra la lista de caracteristicas desde la vista (ver por ejemplo index.php)
            if (isset($listaCaracteristicas) && is_array($listaCaracteristicas)) {
                foreach ($listaCaracteristicas as $item) {
                    echo "<li>$item</li>";
                }
            }
            ?>
        </ul>
    </section>
</aside>
