(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var heroVideo = document.querySelector('.hero-video');
    if (heroVideo && !reduce) {
        var showHeroVideo = function () {
            var heroSection = heroVideo.closest('.hero');
            if (heroSection) { heroSection.classList.add('has-video'); }
        };
        if (heroVideo.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA && !heroVideo.paused) { showHeroVideo(); }
        else { heroVideo.addEventListener('playing', showHeroVideo, {once: true}); }
    }
    var far = document.getElementById('far');
    var near = document.getElementById('near');
    if (far && near) {
        var ns = 'http://www.w3.org/2000/svg', seed = 7;
        var random = function () { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; };
        var rect = function (x, y, width, height, fill, parent, cssClass) {
            var item = document.createElementNS(ns, 'rect');
            item.setAttribute('x', x); item.setAttribute('y', y); item.setAttribute('width', width); item.setAttribute('height', height); item.setAttribute('fill', fill);
            if (cssClass) item.setAttribute('class', cssClass);
            parent.appendChild(item); return item;
        };
        for (var fx = 0; fx < 1440;) { var fw = 26 + random() * 46, fh = 70 + random() * 150; rect(fx, 400 - fh, fw - 3, fh, '#0a0e13', far); fx += fw; }
        for (var nx = -10; nx < 1440;) {
            var nw = 54 + random() * 70, nh = 120 + random() * 210, bx = nx, by = 400 - nh;
            rect(bx, by, nw - 6, nh, '#10161c', near);
            for (var x = bx + 9; x < bx + nw - 14; x += 19) for (var y = by + 9; y < 400 - 9; y += 23) {
                var lit = random();
                if (lit < .32) { var windowItem = rect(x, y, 11, 13, lit < .03 ? '#E73101' : '#fff3df', near, !reduce && random() < .5 ? 'tw' : ''); windowItem.setAttribute('opacity', (.35 + random() * .6).toFixed(2)); }
                else rect(x, y, 11, 13, '#0b0f14', near);
            }
            nx += nw + 2;
        }
    }
    var adminPanel = document.getElementById('bx-panel');
    var updateAdminPanelOffset = function () {
        var panelHeight = 0;
        if (adminPanel) {
            var panelRect = adminPanel.getBoundingClientRect();
            if (panelRect.bottom > 0 && panelRect.top <= 1) { panelHeight = panelRect.height; }
        }
        document.documentElement.style.setProperty('--bp-admin-panel-height', panelHeight + 'px');
    };
    var adminPanelFollowFrame = 0;
    var adminPanelFollowUntil = 0;
    var followAdminPanel = function () {
        updateAdminPanelOffset();
        if (performance.now() < adminPanelFollowUntil) {
            adminPanelFollowFrame = requestAnimationFrame(followAdminPanel);
        } else {
            adminPanelFollowFrame = 0;
        }
    };
    var scheduleAdminPanelFollow = function () {
        adminPanelFollowUntil = performance.now() + 500;
        if (!adminPanelFollowFrame) {
            adminPanelFollowFrame = requestAnimationFrame(followAdminPanel);
        }
    };
    updateAdminPanelOffset();
    window.addEventListener('resize', scheduleAdminPanelFollow);
    document.addEventListener('scroll', scheduleAdminPanelFollow, {passive: true});
    if (adminPanel && 'ResizeObserver' in window) { new ResizeObserver(scheduleAdminPanelFollow).observe(adminPanel); }
    if (adminPanel && 'MutationObserver' in window) { new MutationObserver(scheduleAdminPanelFollow).observe(adminPanel, {attributes: true, attributeFilter: ['class', 'style']}); }
    var progress = document.getElementById('prog'), header = document.getElementById('hdr'), hero = document.querySelector('.hero');
    var onScroll = function () { var root = document.documentElement, max = root.scrollHeight - root.clientHeight; if (progress) progress.style.width = (max > 0 ? root.scrollTop / max * 100 : 0) + '%'; if (header) header.classList.toggle('on-hero', hero && hero.getBoundingClientRect().bottom > 80); };
    document.addEventListener('scroll', onScroll, {passive: true}); onScroll();
    var counters = document.querySelectorAll('.proof [data-count]');
    if (counters.length && !reduce && 'IntersectionObserver' in window) {
        var countObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                observer.unobserve(entry.target);
                var counter = entry.target;
                var target = parseInt(counter.dataset.count, 10) || 0;
                var numberNode = Array.from(counter.childNodes).find(function (node) { return node.nodeType === Node.TEXT_NODE; });
                if (!numberNode) { numberNode = counter.insertBefore(document.createTextNode('0'), counter.firstChild); }
                var startedAt = performance.now();
                var draw = function (now) {
                    var progressValue = Math.min(1, (now - startedAt) / 900);
                    var eased = 1 - Math.pow(1 - progressValue, 3);
                    numberNode.nodeValue = String(Math.round(target * eased));
                    if (progressValue < 1) { requestAnimationFrame(draw); }
                };
                numberNode.nodeValue = '0';
                requestAnimationFrame(draw);
            });
        }, {threshold: 0.45});
        counters.forEach(function (counter) { countObserver.observe(counter); });
    }
    var journalFilters = document.querySelectorAll('[data-home-journal-role]');
    var journalCards = Array.from(document.querySelectorAll('[data-home-journal-card]'));
    journalFilters.forEach(function (filterButton) {
        filterButton.addEventListener('click', function () {
            var selectedRole = filterButton.dataset.homeJournalRole || '';
            var shown = 0;
            var usedRoles = {};
            journalFilters.forEach(function (button) {
                var active = button === filterButton;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            journalCards.forEach(function (card) {
                var roles = (card.dataset.homeJournalRoles || '').split(',').filter(Boolean);
                var visible = false;
                if (selectedRole) {
                    visible = shown < 3 && roles.indexOf(selectedRole) !== -1;
                } else {
                    var newRole = roles.find(function (role) { return !usedRoles[role]; });
                    visible = shown < 3 && Boolean(newRole);
                    if (newRole) { usedRoles[newRole] = true; }
                }
                card.hidden = !visible;
                if (visible) { shown++; }
            });
        });
    });
    var salary = document.getElementById('salary'), months = document.getElementById('months'), budget = document.getElementById('budget');
    var format = function (number) { return new Intl.NumberFormat('ru-RU').format(Math.round(number)) + ' ₽'; };
    var calculate = function () { if (!salary || !months || !budget) return; var s = +salary.value, m = +months.value, b = +budget.value, labour = s * m, hypotheses = b * m, replacement = s * 1.33, total = labour + hypotheses + replacement; document.getElementById('salary-v').textContent = format(s); document.getElementById('months-v').textContent = m + ' мес.'; document.getElementById('budget-v').textContent = format(b); document.getElementById('o1').textContent = format(labour); document.getElementById('o2').textContent = format(hypotheses); document.getElementById('o3').textContent = format(replacement); document.getElementById('total').textContent = format(total); document.getElementById('ratio').textContent = '× ' + Math.max(1, Math.round(total / 600000)); };
    [salary, months, budget].forEach(function (item) { if (item) item.addEventListener('input', calculate); }); calculate();
    var tabs = Array.from(document.querySelectorAll('.tab[data-tab]'));
    var activateTab = function (activeTab, setFocus) {
        var tabCode = activeTab.getAttribute('data-tab');
        tabs.forEach(function (item) {
            var active = item === activeTab;
            item.setAttribute('aria-selected', String(active));
            item.tabIndex = active ? 0 : -1;
        });
        document.querySelectorAll('.panel[id^="panel-"]').forEach(function (panel) {
            var active = panel.id === 'panel-' + tabCode;
            panel.setAttribute('data-active', String(active));
            panel.hidden = !active;
        });
        if (setFocus) { activeTab.focus(); }
    };
    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () { activateTab(tab, false); });
        tab.addEventListener('keydown', function (event) {
            var nextIndex = null;
            if (event.key === 'ArrowRight') { nextIndex = (index + 1) % tabs.length; }
            if (event.key === 'ArrowLeft') { nextIndex = (index - 1 + tabs.length) % tabs.length; }
            if (event.key === 'Home') { nextIndex = 0; }
            if (event.key === 'End') { nextIndex = tabs.length - 1; }
            if (nextIndex === null) { return; }
            event.preventDefault();
            activateTab(tabs[nextIndex], true);
        });
    });
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
            var cursor = input.selectionStart;
            var removeAt = event.key === 'Backspace' ? cursor - 1 : cursor;
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
    var validateForm = function (form) {
        var required = form.querySelectorAll('[required]');
        for (var index = 0; index < required.length; index++) {
            if (!String(required[index].value).trim()) {
                var fieldLabel = form.querySelector('label[for="' + required[index].id + '"]');
                var fieldName = fieldLabel ? fieldLabel.textContent.trim().replace('*', '') : 'поле';
                return {valid: false, message: 'Заполните поле «' + fieldName + '».', field: required[index]};
            }
        }
        var phone = form.querySelector('[data-phone-mask]');
        if (phone && phone.value) {
            var phoneDigits = phone.value.replace(/\D/g, '');
            if (phoneDigits.length !== 11) {
                return {valid: false, message: 'Введите номер телефона полностью.', field: phone};
            }
        }
        return {valid: true};
    };
    document.querySelectorAll('[data-bp-form]').forEach(function (form) {
        if (form.dataset.bpBound === 'true') { return; }
        form.dataset.bpBound = 'true';
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
            fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin'})
                .then(function (response) { return response.json(); })
                .then(function (response) {
                    if (!response.success) { throw new Error(response.message || 'Не удалось отправить заявку.'); }
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
    var reveal = document.querySelectorAll('.rv');
    if (!reduce && 'IntersectionObserver' in window) { var observer = new IntersectionObserver(function (entries) { entries.forEach(function (entry) { if (entry.isIntersecting) { entry.target.classList.add('in'); observer.unobserve(entry.target); } }); }, {threshold: .14}); reveal.forEach(function (item) { observer.observe(item); }); } else reveal.forEach(function (item) { item.classList.add('in'); });
}());
