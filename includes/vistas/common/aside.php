<?php
$tieneCaracteristicas = isset($listaCaracteristicas) && is_array($listaCaracteristicas) && !empty($listaCaracteristicas);
$tieneContenido       = isset($contenidoAside) && !empty(trim($contenidoAside));

if ($tieneCaracteristicas || $tieneContenido): ?>
<aside>
    <?php if ($tieneCaracteristicas): ?>
    <section>
        <h3>Caracter&iacute;sticas</h3>
        <ul>
            <?php foreach ($listaCaracteristicas as $item): ?>
                <li><?= $item ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <?php if ($tieneContenido): ?>
        <?= $contenidoAside ?>
    <?php endif; ?>
</aside>
<?php endif; ?>
