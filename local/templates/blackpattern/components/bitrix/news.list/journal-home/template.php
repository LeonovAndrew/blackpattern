<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$items = [];
$roleOptions = [];
foreach ($arResult['ITEMS'] as $item) {
    $roleNames = array_values(array_filter((array)($item['PROPERTIES']['ROLE']['VALUE'] ?? [])));
    $roleCodes = array_values(array_filter((array)($item['PROPERTIES']['ROLE']['VALUE_XML_ID'] ?? [])));
    foreach ($roleCodes as $index => $roleCode) {
        if (!isset($roleOptions[$roleCode])) {
            $roleOptions[$roleCode] = $roleNames[$index] ?? $roleCode;
        }
    }
    $item['_ROLE_NAMES'] = $roleNames;
    $item['_ROLE_CODES'] = $roleCodes;
    $items[] = $item;
}

$visibleIds = [];
$usedRoles = [];
foreach ($items as $item) {
    foreach ($item['_ROLE_CODES'] as $roleCode) {
        if (!isset($usedRoles[$roleCode])) {
            $visibleIds[(int)$item['ID']] = true;
            $usedRoles[$roleCode] = true;
            break;
        }
    }
    if (count($visibleIds) >= 3) {
        break;
    }
}
?>
<?php if ($items): ?>
    <div class="home-journal-filters rv" role="group" aria-label="Фильтр материалов по роли">
        <button class="home-journal-filter is-active" type="button" data-home-journal-role="" aria-pressed="true">Все</button>
        <?php foreach ($roleOptions as $roleCode => $roleName): ?>
            <button class="home-journal-filter" type="button" data-home-journal-role="<?= htmlspecialcharsbx($roleCode); ?>" aria-pressed="false"><?= htmlspecialcharsbx($roleName); ?></button>
        <?php endforeach; ?>
    </div>
    <div class="home-journal-grid rv" data-home-journal-grid>
        <?php foreach ($items as $item):
            $roles = $item['_ROLE_NAMES'];
            $roleCodes = $item['_ROLE_CODES'];
            $themes = (array)($item['PROPERTIES']['THEME']['VALUE'] ?? []);
            $readingTime = (int)($item['PROPERTIES']['READING_TIME']['VALUE'] ?? 0);
            $roleClass = 'home-journal-dot-' . ($roleCodes[0] ?? 'own');
            $isVisible = isset($visibleIds[(int)$item['ID']]);
        ?>
            <a class="home-journal-card" data-home-journal-card data-home-journal-roles="<?= htmlspecialcharsbx(implode(',', $roleCodes)); ?>"<?= $isVisible ? '' : ' hidden'; ?> href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']); ?>">
                <div class="home-journal-meta"><span class="home-journal-who"><span class="home-journal-dot <?= htmlspecialcharsbx($roleClass); ?>"></span><?= htmlspecialcharsbx(implode(', ', $roles)); ?></span><?php if ($themes): ?> · <?= htmlspecialcharsbx(implode(', ', $themes)); ?><?php endif; ?></div>
                <h3><?= htmlspecialcharsbx($item['NAME']); ?></h3>
                <p><?= htmlspecialcharsbx($item['PREVIEW_TEXT']); ?></p>
                <span class="home-journal-read">Читать<?= $readingTime ? ' · ' . $readingTime . ' мин' : ''; ?> →</span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
