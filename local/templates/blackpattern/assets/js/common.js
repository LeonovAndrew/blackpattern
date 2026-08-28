(function () {
    var menuButton = document.querySelector('.burger');
    var siteHeader = document.querySelector('.site-header');
    var mainNavigation = document.getElementById('main-navigation');
    var closeMenu = function (returnFocus) {
        if (!menuButton || !siteHeader || !mainNavigation) { return; }
        siteHeader.classList.remove('menu-open');
        document.body.classList.remove('mobile-menu-open');
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Открыть меню');
        if (returnFocus) { menuButton.focus(); }
    };
    if (menuButton && siteHeader && mainNavigation) {
        menuButton.addEventListener('click', function () {
            var open = menuButton.getAttribute('aria-expanded') !== 'true';
            siteHeader.classList.toggle('menu-open', open);
            document.body.classList.toggle('mobile-menu-open', open);
            menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuButton.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
        });
        mainNavigation.addEventListener('click', function (event) {
            if (event.target.closest('a')) { closeMenu(false); }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
                closeMenu(true);
            }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1140) { closeMenu(false); }
        });
    }

    var modalTrigger = null;
    var closeModal = function (modal) {
        if (!modal || typeof modal.close !== 'function') { return; }
        modal.close();
    };
    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-bp-modal-open]');
        if (opener) {
            var modal = document.getElementById(opener.getAttribute('data-bp-modal-open'));
            if (!modal || typeof modal.showModal !== 'function') { return; }
            event.preventDefault();
            modalTrigger = opener;
            modal.showModal();
            document.body.classList.add('bp-modal-open');
            window.setTimeout(function () {
                var field = modal.querySelector('.bp-form-field input, .bp-form-field select, .bp-form-field textarea');
                if (field) { field.focus(); }
            }, 0);
            return;
        }
        var closer = event.target.closest('[data-bp-modal-close]');
        if (closer) {
            event.preventDefault();
            closeModal(closer.closest('[data-bp-modal]'));
            return;
        }
        if (event.target.matches('[data-bp-modal]')) { closeModal(event.target); }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') { return; }
        var modal = document.querySelector('[data-bp-modal][open]');
        if (modal) {
            event.preventDefault();
            closeModal(modal);
        }
    });
    document.querySelectorAll('[data-bp-modal]').forEach(function (modal) {
        modal.addEventListener('cancel', function (event) {
            event.preventDefault();
            closeModal(modal);
        });
        modal.addEventListener('close', function () {
            document.body.classList.remove('bp-modal-open');
            if (modalTrigger && document.contains(modalTrigger)) { modalTrigger.focus(); }
            modalTrigger = null;
        });
    });

    var cookieNotice = document.querySelector('[data-cookie-notice]');
    var cookieNoticeButton = document.querySelector('[data-cookie-notice-accept]');
    if (cookieNotice && cookieNoticeButton) {
        var cookieNoticeAccepted = false;
        try { cookieNoticeAccepted = window.localStorage.getItem('bp_cookie_notice_accepted') === 'Y'; }
        catch (error) {}
        if (!cookieNoticeAccepted) { cookieNotice.hidden = false; }
        cookieNoticeButton.addEventListener('click', function () {
            try { window.localStorage.setItem('bp_cookie_notice_accepted', 'Y'); }
            catch (error) {}
            cookieNotice.hidden = true;
        });
    }

    var button = document.getElementById('totop');
    if (button) {
        window.addEventListener('scroll', function () {
            button.classList.toggle('show', window.scrollY > 640);
        }, {passive: true});
        button.addEventListener('click', function () {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    }

    var formatPhone = function (value) {
        var digits = String(value).replace(/\D/g, '');
        if (!digits) { return ''; }
        if (digits.charAt(0) === '8') { digits = '7' + digits.slice(1); }
        if (digits.charAt(0) !== '7') { digits = '7' + digits; }
        digits = digits.slice(0, 11);
        var result = '+7';
        if (digits.length > 1) { result += ' (' + digits.slice(1, 4); }
        if (digits.length >= 4) { result += ') ' + digits.slice(4, 7); }
        if (digits.length >= 7) { result += '-' + digits.slice(7, 9); }
        if (digits.length >= 9) { result += '-' + digits.slice(9, 11); }
        return result;
    };
    var phoneCursorAfterDigits = function (value, digitsCount) {
        if (!digitsCount) { return value ? Math.min(2, value.length) : 0; }
        var found = 0;
        for (var position = 0; position < value.length; position++) {
            if (/\d/.test(value.charAt(position))) {
                found++;
                if (found === digitsCount) { return position + 1; }
            }
        }
        return value.length;
    };
    document.querySelectorAll('[data-phone-mask]').forEach(function (input) {
        if (input.dataset.bpMaskBound === 'true') { return; }
        input.dataset.bpMaskBound = 'true';
        input.addEventListener('input', function () { input.value = formatPhone(input.value); });
        input.addEventListener('keydown', function (event) {
            if ((event.key !== 'Backspace' && event.key !== 'Delete') || input.selectionStart !== input.selectionEnd) { return; }
            var value = input.value;
            var removeAt = event.key === 'Backspace' ? input.selectionStart - 1 : input.selectionStart;
            while (removeAt >= 0 && removeAt < value.length && !/\d/.test(value.charAt(removeAt))) {
                removeAt += event.key === 'Backspace' ? -1 : 1;
            }
            if (removeAt < 0 || removeAt >= value.length) { return; }
            event.preventDefault();
            var digitsBefore = value.slice(0, removeAt).replace(/\D/g, '').length;
            input.value = formatPhone(value.slice(0, removeAt) + value.slice(removeAt + 1));
            var nextCursor = phoneCursorAfterDigits(input.value, digitsBefore);
            input.setSelectionRange(nextCursor, nextCursor);
        });
    });

    var trackingCodes = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
    var searchParams = new URLSearchParams(window.location.search);
    trackingCodes.forEach(function (code) {
        var value = searchParams.get(code);
        try {
            if (value) { window.sessionStorage.setItem('bp_' + code, value); }
        } catch (error) {}
    });
    var trackingValue = function (code) {
        if (code === 'page_url') { return window.location.href; }
        if (code === 'referrer') { return document.referrer; }
        var value = searchParams.get(code) || '';
        try { return value || window.sessionStorage.getItem('bp_' + code) || ''; }
        catch (error) { return value; }
    };

    var validateForm = function (form) {
        var required = form.querySelectorAll('[required]');
        for (var index = 0; index < required.length; index++) {
            var field = required[index];
            var empty = field.type === 'checkbox' ? !field.checked : !String(field.value).trim();
            if (empty || !field.checkValidity()) {
                var fieldLabel = form.querySelector('label[for="' + field.id + '"]');
                var fieldName = fieldLabel ? fieldLabel.textContent.trim().replace('*', '') : 'обязательное поле';
                var message = field.type === 'email' && field.value ? 'Введите корректный email.' : 'Заполните поле «' + fieldName + '».';
                return {valid: false, message: message, field: field};
            }
        }
        var phone = form.querySelector('[data-phone-mask]');
        if (phone && phone.value && phone.value.replace(/\D/g, '').length !== 11) {
            return {valid: false, message: 'Введите номер телефона полностью.', field: phone};
        }
        return {valid: true};
    };

    var refreshFormSessid = function (form) {
        var sessidField = form.querySelector('input[name="sessid"]');
        if (!sessidField) {
            return Promise.reject(new Error('Не удалось подготовить безопасную отправку формы.'));
        }

        return fetch(form.dataset.sessidUrl || '/local/ajax/form_session.php', {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.success || !/^[a-f0-9]{32}$/i.test(data.sessid || '')) {
                    throw new Error(data.message || 'Не удалось подготовить безопасную отправку формы.');
                }
                sessidField.value = data.sessid;
            });
        });
    };

    document.querySelectorAll('[data-bp-form]').forEach(function (form) {
        if (form.dataset.bpBound === 'true') { return; }
        form.dataset.bpBound = 'true';
        form.querySelectorAll('[data-bp-tracking]').forEach(function (input) {
            input.value = trackingValue(input.dataset.bpTracking);
        });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var submit = form.querySelector('[type="submit"]');
            var status = form.querySelector('.bp-form-status');
            var validation = validateForm(form);
            if (!validation.valid) {
                status.className = 'bp-form-status error';
                status.textContent = validation.message;
                validation.field.focus();
                return;
            }
            submit.disabled = true;
            status.className = 'bp-form-status';
            status.textContent = 'Отправляем заявку…';
            refreshFormSessid(form)
                .then(function () {
                    return fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin'});
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.success) { throw new Error(data.message || 'Не удалось отправить заявку.'); }
                        return data;
                    });
                })
                .then(function (response) {
                    form.reset();
                    status.className = 'bp-form-status success';
                    status.textContent = response.message;
                    if (form.dataset.successUrl) {
                        window.setTimeout(function () { window.location.assign(form.dataset.successUrl); }, 500);
                    }
                })
                .catch(function (error) {
                    status.className = 'bp-form-status error';
                    status.textContent = error.message || 'Не удалось отправить заявку. Попробуйте ещё раз.';
                })
                .finally(function () { submit.disabled = false; });
        });
    });
}());
