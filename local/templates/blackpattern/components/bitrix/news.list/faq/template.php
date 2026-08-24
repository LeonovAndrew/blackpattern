<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="faq-list rv">
    <?php foreach ($arResult['ITEMS'] as $item):
        $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_EDIT'));
        $this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
    ?>
        <details id="<?= $this->GetEditAreaId($item['ID']); ?>">
            <summary><?= $item['NAME']; ?><span class="faq-sign" aria-hidden="true">+</span></summary>
            <div class="faq-answer"><?= $item['PREVIEW_TEXT']; ?></div>
        </details>
    <?php endforeach; ?>
</div>
