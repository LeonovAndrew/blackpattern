<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="price rv">
    <?php foreach ($arResult['ITEMS'] as $item):
        $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_EDIT'));
        $this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
        $featured = $item['PROPERTIES']['FEATURED']['VALUE_XML_ID'] === 'Y';
    ?>
        <article class="pc<?= $featured ? ' tot' : ''; ?>" id="<?= $this->GetEditAreaId($item['ID']); ?>">
            <?php if ($item['PROPERTIES']['TIER']['VALUE']): ?><span class="tier"><?= htmlspecialcharsbx($item['PROPERTIES']['TIER']['VALUE']); ?></span><?php endif; ?>
            <h3><?= htmlspecialcharsbx($item['NAME']); ?></h3>
            <?php if ($item['PREVIEW_TEXT']): ?><p><?= htmlspecialcharsbx($item['PREVIEW_TEXT']); ?></p><?php endif; ?>
            <?php if ($item['PROPERTIES']['PRICE']['VALUE']): ?><div class="amt"><?= htmlspecialcharsbx($item['PROPERTIES']['PRICE']['VALUE']); ?></div><?php endif; ?>
            <?php if ($item['PROPERTIES']['TERMS']['VALUE']): ?><div class="term"><?= htmlspecialcharsbx($item['PROPERTIES']['TERMS']['VALUE']); ?></div><?php endif; ?>
            <?php if ($featured): ?><a href="#cta" class="btn btn-inv price-cta">Обсудить задачу →</a><?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
