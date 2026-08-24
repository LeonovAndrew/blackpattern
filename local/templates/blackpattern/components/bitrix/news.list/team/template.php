<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="exps rv">
    <?php foreach ($arResult['ITEMS'] as $item):
        $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_EDIT'));
        $this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
        $photo = $item['PREVIEW_PICTURE']['SRC'] ?? '';
        $responsibilities = (array)($item['PROPERTIES']['RESPONSIBILITIES']['VALUE'] ?? []);
        $regalia = array_filter((array)($item['PROPERTIES']['REGALIA']['VALUE'] ?? []));
    ?>
        <article class="exp" id="<?= $this->GetEditAreaId($item['ID']); ?>">
            <?php if ($photo): ?>
                <img class="exp-photo" src="<?= $photo; ?>" alt="<?= htmlspecialcharsbx($item['NAME']); ?>" loading="lazy">
            <?php else: ?>
                <div class="exp-ph">ФОТО 1:1 · Ч/Б</div>
            <?php endif; ?>
            <div class="exp-b">
                <b><?= $item['NAME']; ?></b>
                <?php if ($item['PROPERTIES']['POSITION_EN']['VALUE']): ?><span class="rl"><?= htmlspecialcharsbx($item['PROPERTIES']['POSITION_EN']['VALUE']); ?></span><?php endif; ?>
                <?php if ($responsibilities || $regalia): ?><ul>
                    <?php foreach ($responsibilities as $responsibility): ?><li><?= htmlspecialcharsbx($responsibility); ?></li><?php endforeach; ?>
                    <?php foreach ($regalia as $itemRegalia): ?><li><span class="ph-chip"><?= htmlspecialcharsbx($itemRegalia); ?></span></li><?php endforeach; ?>
                </ul><?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
