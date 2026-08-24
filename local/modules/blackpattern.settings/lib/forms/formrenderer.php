<?php

namespace BlackPattern\Forms;

use Bitrix\Main\Loader;
use BlackPattern\Settings\SiteSettings;

final class FormRenderer
{
    public static function render(string $formCode, array $options = []): string
    {
        if (!Loader::includeModule('iblock')) {
            return '';
        }

        $iblock = \CIBlock::GetList([], ['TYPE' => 'forms', 'CODE' => $formCode, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'])->Fetch();
        if (!$iblock) {
            return '';
        }

        $properties = FormSubmission::getProperties((int)$iblock['ID']);
        if (!$properties) {
            return '';
        }

        $submitText = $options['SUBMIT_TEXT'] ?? 'Отправить заявку';
        $successUrl = $options['SUCCESS_URL'] ?? '/thanks/';
        $extraClass = preg_replace('/[^a-zA-Z0-9 _-]/', '', (string)($options['CLASS'] ?? ''));
        $formClass = trim('bp-form ' . $extraClass);
        $compact = ($options['COMPACT'] ?? 'N') === 'Y';
        $settings = SiteSettings::get();
        $privacyUrl = $settings['PRIVACY_URL'] ?: '/privacy/';
        $captchaEnabled = YandexCaptcha::isEnabled($settings) && trim((string)$settings['YANDEX_CAPTCHA_CLIENT_KEY']) !== '';
        static $captchaScriptRendered = false;

        ob_start();
        ?>
        <form class="<?= htmlspecialcharsbx($formClass); ?>" data-bp-form data-success-url="<?= htmlspecialcharsbx($successUrl); ?>" action="/local/ajax/form_submit.php" method="post" novalidate>
            <input type="hidden" name="sessid" value="<?= htmlspecialcharsbx(bitrix_sessid()); ?>">
            <input type="hidden" name="form_code" value="<?= htmlspecialcharsbx($formCode); ?>">
            <?php foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'page_url', 'referrer'] as $trackingCode): ?>
                <input type="hidden" name="tracking[<?= $trackingCode; ?>]" value="" data-bp-tracking="<?= $trackingCode; ?>">
            <?php endforeach; ?>
            <div class="bp-form-honeypot" aria-hidden="true"><label>Не заполняйте это поле<input type="text" name="website" value="" tabindex="-1" autocomplete="off"></label></div>
            <div class="bp-form-fields">
                <?php foreach ($properties as $property): ?>
                    <?= self::renderField($property, $formCode, $compact); ?>
                <?php endforeach; ?>
            </div>
            <label class="bp-form-consent"><input type="checkbox" name="privacy_consent" value="Y" required> <span>Я согласен с <a href="<?= htmlspecialcharsbx($privacyUrl); ?>" target="_blank" rel="noopener">политикой обработки персональных данных</a>.</span></label>
            <?php if ($captchaEnabled): ?>
                <div class="smart-captcha bp-form-captcha" data-sitekey="<?= htmlspecialcharsbx($settings['YANDEX_CAPTCHA_CLIENT_KEY']); ?>" data-hl="ru"></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-red bp-form-submit"><?= htmlspecialcharsbx($submitText); ?></button>
            <p class="bp-form-status" aria-live="polite"></p>
        </form>
        <?php if ($captchaEnabled && !$captchaScriptRendered): $captchaScriptRendered = true; ?>
            <script src="https://smartcaptcha.cloud.yandex.ru/captcha.js" defer></script>
        <?php endif; ?>
        <?php

        return (string)ob_get_clean();
    }

    private static function renderField(array $property, string $formCode, bool $compact): string
    {
        $code = $property['CODE'];
        $id = 'bp-form-' . strtolower($formCode . '-' . $code);
        $required = $property['IS_REQUIRED'] === 'Y';
        $name = 'fields[' . $code . ']';
        $label = htmlspecialcharsbx($property['NAME']) . ($required ? ' <span aria-hidden="true">*</span>' : '');
        $isPhone = $code === 'PHONE';
        $isEmail = $code === 'EMAIL';
        $isTextarea = in_array($code, ['MESSAGE', 'COMMENT'], true);
        $inputType = $property['PROPERTY_TYPE'] === 'N' ? 'number' : ($isPhone ? 'tel' : ($isEmail ? 'email' : 'text'));
        $placeholder = $isPhone ? '+7 (___) ___-__-__' : $property['NAME'];

        ob_start();
        ?>
        <div class="bp-form-field">
            <label for="<?= htmlspecialcharsbx($id); ?>"<?= $compact ? ' class="bp-visually-hidden"' : ''; ?>><?= $label; ?></label>
            <?php if ($property['PROPERTY_TYPE'] === 'L'): ?>
                <select id="<?= htmlspecialcharsbx($id); ?>" name="<?= htmlspecialcharsbx($name); ?>"<?= $required ? ' required' : ''; ?>>
                    <option value=""><?= htmlspecialcharsbx($property['NAME']); ?></option>
                    <?php foreach ($property['ENUMS'] as $enum): ?>
                        <option value="<?= (int)$enum['ID']; ?>"><?= htmlspecialcharsbx($enum['VALUE']); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($isTextarea): ?>
                <textarea id="<?= htmlspecialcharsbx($id); ?>" name="<?= htmlspecialcharsbx($name); ?>" placeholder="<?= htmlspecialcharsbx($placeholder); ?>"<?= $required ? ' required' : ''; ?>></textarea>
            <?php else: ?>
                <input id="<?= htmlspecialcharsbx($id); ?>" type="<?= $inputType; ?>" placeholder="<?= htmlspecialcharsbx($placeholder); ?>"<?= $property['PROPERTY_TYPE'] === 'N' ? ' step="any"' : ''; ?><?= $isPhone ? ' inputmode="tel" autocomplete="tel" data-phone-mask' : ''; ?><?= $isEmail ? ' inputmode="email" autocomplete="email"' : ''; ?> name="<?= htmlspecialcharsbx($name); ?>"<?= $required ? ' required' : ''; ?>>
            <?php endif; ?>
        </div>
        <?php

        return (string)ob_get_clean();
    }
}
