<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$item = $arResult['ITEMS'][0] ?? null;
if (!$item) {
    return;
}

$this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item['IBLOCK_ID'], 'ELEMENT_EDIT'));
$propertyValue = static function (string $code, string $default = '') use ($item): string {
    $value = $item['PROPERTIES'][$code]['~VALUE'] ?? '';
    return is_scalar($value) && (string)$value !== '' ? (string)$value : $default;
};
$filePath = static function (string $code) use ($item): string {
    $fileId = (int)($item['PROPERTIES'][$code]['VALUE'] ?? 0);
    return $fileId > 0 ? (string)CFile::GetPath($fileId) : '';
};
$safeUrl = static function (string $url, string $default): string {
    $url = trim($url);
    return preg_match('~^(?:https?://|/|#)~i', $url) ? $url : $default;
};

$videoMp4 = $filePath('VIDEO_MP4');
$videoWebm = $filePath('VIDEO_WEBM');
$poster = $filePath('POSTER');
$stats = [];
for ($number = 1; $number <= 4; $number++) {
    $label = $propertyValue('STAT_' . $number . '_LABEL');
    $value = $propertyValue('STAT_' . $number . '_VALUE');
    if ($label === '' && $value === '') {
        continue;
    }
    $stats[] = [
        'LABEL' => $label,
        'VALUE' => $value,
        'ACCENT' => ($item['PROPERTIES']['STAT_' . $number . '_ACCENT']['VALUE_XML_ID'] ?? '') === 'Y',
    ];
}
?>
<section class="hero" id="<?= $this->GetEditAreaId($item['ID']); ?>">
    <div class="hero-media" aria-hidden="true">
        <?php if ($videoMp4 || $videoWebm): ?>
            <video class="hero-video" autoplay muted loop playsinline preload="metadata"<?= $poster ? ' poster="' . htmlspecialcharsbx($poster) . '"' : ''; ?>>
                <?php if ($videoWebm): ?><source src="<?= htmlspecialcharsbx($videoWebm); ?>" type="video/webm"><?php endif; ?>
                <?php if ($videoMp4): ?><source src="<?= htmlspecialcharsbx($videoMp4); ?>" type="video/mp4"><?php endif; ?>
            </video>
        <?php endif; ?>
        <div class="scene"><div class="sky"></div><svg class="citysvg" id="city" viewBox="0 0 1440 400" preserveAspectRatio="xMidYMax slice"><g id="far"></g><g id="near"></g></svg></div>
        <div class="streaks"><div class="streak w"></div><div class="streak r"></div><div class="streak w"></div><div class="streak r"></div></div>
        <div class="hero-grad"></div>
    </div>
    <div class="hero-inner"><div class="wrap hero-top">
        <span class="hero-kick"><?= $propertyValue('KICKER', 'Построение отдела маркетинга под ключ'); ?></span>
        <h1>
            <span class="ln"><span><?= htmlspecialcharsbx($propertyValue('H1_LINE_1', 'Marketing OS.')); ?></span></span>
            <span class="ln"><span><?= htmlspecialcharsbx($propertyValue('H1_LINE_2', 'Операционная')); ?><?php if ($propertyValue('H1_ACCENT')): ?> <em><?= htmlspecialcharsbx($propertyValue('H1_ACCENT')); ?></em><?php endif; ?></span></span>
            <span class="ln"><span><?= htmlspecialcharsbx($propertyValue('H1_LINE_3', 'отдела маркетинга.')); ?></span></span>
        </h1>
        <p class="hero-sub"><?= $propertyValue('DESCRIPTION'); ?></p>
        <div class="hero-act">
            <a href="<?= htmlspecialcharsbx($safeUrl($propertyValue('PRIMARY_URL'), '#cta')); ?>" class="btn btn-red"><?= htmlspecialcharsbx($propertyValue('PRIMARY_TEXT', 'Записаться на диагностику →')); ?></a>
            <a href="<?= htmlspecialcharsbx($safeUrl($propertyValue('SECONDARY_URL'), '#calc')); ?>" class="btn btn-glass"><?= htmlspecialcharsbx($propertyValue('SECONDARY_TEXT', 'Посчитать ошибку найма')); ?></a>
        </div>
    </div></div>
    <?php if ($stats): ?>
        <div class="hero-spec"><div class="wrap">
            <?php foreach ($stats as $stat): ?>
                <div class="hs<?= $stat['ACCENT'] ? ' hot' : ''; ?>"><div class="k"><?= htmlspecialcharsbx($stat['LABEL']); ?></div><div class="v"><?= htmlspecialcharsbx($stat['VALUE']); ?></div></div>
            <?php endforeach; ?>
        </div></div>
    <?php endif; ?>
</section>
