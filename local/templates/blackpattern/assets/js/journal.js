(function () {
    var article = document.querySelector('.article-body');
    var progress = document.getElementById('prog');
    if (article && progress) {
        var updateReadingProgress = function () {
            var rect = article.getBoundingClientRect();
            var start = window.scrollY + rect.top - window.innerHeight * 0.35;
            var end = window.scrollY + rect.bottom - window.innerHeight * 0.65;
            var value = end > start ? (window.scrollY - start) / (end - start) : 0;
            progress.style.width = Math.max(0, Math.min(1, value)) * 100 + '%';
        };
        updateReadingProgress();
        window.addEventListener('scroll', updateReadingProgress, {passive: true});
        window.addEventListener('resize', updateReadingProgress);
    }

    var filter = document.querySelector('.journal-filter');
    var ensureContainer = function () {
        var result = document.querySelector('[data-journal-results]');
        if (result) { return result; }
        var list = document.querySelector('.journal-list');
        if (!list) { return null; }
        result = document.createElement('div');
        result.setAttribute('data-journal-results', '');
        var featured = document.querySelector('.journal-featured');
        list.parentNode.insertBefore(result, featured || list);
        if (featured) { result.appendChild(featured); }
        result.appendChild(list);
        return result;
    };
    var container = ensureContainer();
    if (!filter || !window.fetch) { return; }

    var setActiveFilters = function (url, availability) {
        var role = url.searchParams.get('role') || '';
        var theme = url.searchParams.get('theme') || '';
        filter.querySelectorAll('[data-journal-role]').forEach(function (link) {
            var code = link.dataset.journalRole;
            var available = !code || !availability || availability.roles[code];
            link.classList.toggle('is-active', link.dataset.journalRole === role);
            link.setAttribute('aria-pressed', link.dataset.journalRole === role ? 'true' : 'false');
            link.classList.toggle('is-disabled', !available);
            link.toggleAttribute('aria-disabled', !available);
            link.tabIndex = available ? 0 : -1;
            var roleUrl = new URL('/journal/', window.location.origin);
            if (code) { roleUrl.searchParams.set('role', code); }
            if (theme) { roleUrl.searchParams.set('theme', theme); }
            if (available) { link.href = roleUrl.pathname + roleUrl.search; } else { link.removeAttribute('href'); }
        });
        filter.querySelectorAll('[data-journal-theme]').forEach(function (link) {
            var code = link.dataset.journalTheme;
            var available = !code || !availability || availability.themes[code];
            link.classList.toggle('is-active', link.dataset.journalTheme === theme);
            link.setAttribute('aria-pressed', link.dataset.journalTheme === theme ? 'true' : 'false');
            link.classList.toggle('is-disabled', !available);
            link.toggleAttribute('aria-disabled', !available);
            link.tabIndex = available ? 0 : -1;
            var themeUrl = new URL('/journal/', window.location.origin);
            if (role) { themeUrl.searchParams.set('role', role); }
            if (code) { themeUrl.searchParams.set('theme', code); }
            if (available) { link.href = themeUrl.pathname + themeUrl.search; } else { link.removeAttribute('href'); }
        });
    };
    var load = function (url, pushState) {
        container = ensureContainer();
        if (!container) { window.location.assign(url.href); return; }
        container.setAttribute('aria-busy', 'true');
        container.classList.add('is-loading');
        fetch('/local/ajax/journal_list.php' + url.search, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
            .then(function (response) {
                if (!response.ok) { throw new Error('Journal request failed'); }
                return response.json();
            })
            .then(function (response) {
                container.outerHTML = response.html;
                container = ensureContainer();
                setActiveFilters(url, response.availability);
                if (pushState) { window.history.pushState({}, '', url.pathname + url.search); }
            })
            .catch(function () { window.location.assign(url.href); });
    };
    document.addEventListener('click', function (event) {
        var link = event.target.closest('.journal-filter a, .journal-pagination a');
        if (!link || link.classList.contains('is-disabled') || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) { return; }
        var url = new URL(link.href, window.location.origin);
        if (url.origin !== window.location.origin || url.pathname !== '/journal/') { return; }
        event.preventDefault();
        load(url, true);
    });
    window.addEventListener('popstate', function () { load(new URL(window.location.href), false); });

}());
