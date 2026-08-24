<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<nav class="main-nav" id="main-navigation" aria-label="Основная навигация">
    <ul class="nav-links">
        <?php foreach ($arResult as $item): ?>
            <li><a href="<?= htmlspecialcharsbx($item['LINK']); ?>"<?= $item['SELECTED'] ? ' class="on"' : ''; ?>><?= htmlspecialcharsbx($item['TEXT']); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <a class="btn btn-red mobile-menu-cta" href="/#cta">Обсудить задачу</a>
</nav>
