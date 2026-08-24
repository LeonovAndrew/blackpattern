<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="mods rv">
    <?php foreach ($arResult['ITEMS'] as $item):
        $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_EDIT'));
        $this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
        $inclusions = array_filter((array)($item['PROPERTIES']['INCLUSIONS']['VALUE'] ?? []));
    ?>
        <article class="mod" id="<?= $this->GetEditAreaId($item['ID']); ?>">
            <?php if ($item['PROPERTIES']['MODULE_NUMBER']['VALUE']): ?><span class="code"><?= htmlspecialcharsbx($item['PROPERTIES']['MODULE_NUMBER']['VALUE']); ?></span><?php endif; ?>
            <h3><?= htmlspecialcharsbx($item['NAME']); ?></h3>
            <?php if ($item['PREVIEW_TEXT']): ?><p><?= htmlspecialcharsbx($item['PREVIEW_TEXT']); ?></p><?php endif; ?>
            <?php if ($inclusions): ?><ul><?php foreach ($inclusions as $inclusion): ?><li><?= htmlspecialcharsbx($inclusion); ?></li><?php endforeach; ?></ul><?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
