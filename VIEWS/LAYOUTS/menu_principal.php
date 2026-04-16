<div class="headeringo">

    <?php foreach ($menu_botones as $boton): ?>

        <button onclick="window.location.href='<?php echo htmlspecialchars($boton['url']); ?>'">

            <?php echo htmlspecialchars($boton['label']); ?>

        </button>

    <?php endforeach; ?>

</div>