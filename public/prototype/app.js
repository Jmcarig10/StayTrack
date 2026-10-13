(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const desktopSidebar = window.matchMedia('(min-width: 1024px)');
    const focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(',');

    const state = {
        route: 'login',
        role: null,
        pendingRole: null,
        deleteTrigger: null,
        deleteTarget: null,
        lastFocused: null,
        toastTimer: null,
        overlayTimer: null,
        simulatedPaymentId: 1000,
    };

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    document.addEventListener('DOMContentLoaded', initialise);

    function initialise() {
        document.documentElement.classList.add('js');
        document.documentElement.dataset.reducedMotion = reducedMotion.matches ? 'true' : 'false';

        document.addEventListener('click', handleClick);
        document.addEventListener('submit', handleSubmit);
        document.addEventListener('input', handleInput);
        document.addEventListener('change', handleChange);
        document.addEventListener('keydown', handleKeydown);
        window.addEventListener('popstate', handleLocationChange);
        window.addEventListener('hashchange', handleLocationChange);

        if (typeof reducedMotion.addEventListener === 'function') {
            reducedMotion.addEventListener('change', (event) => {
                document.documentElement.dataset.reducedMotion = event.matches ? 'true' : 'false';
            });
        }

        if (typeof desktopSidebar.addEventListener === 'function') {
            desktopSidebar.addEventListener('change', (event) => {
                if (event.matches) {
                    closeSidebar(false);
                }
            });
        }

        prepareAccessibility();
        cacheMetricValues();
        updatePaymentSummary();

        const previewParams = new URLSearchParams(window.location.search);
        const requestedRoute = normaliseRoute(
            previewParams.get('screen') || window.location.hash,
        );
        const requestedRole = normaliseRole(previewParams.get('role'));
        const previewRole = requestedRole || (
            requestedRoute === 'tenant-dashboard'
                ? 'tenant'
                : requestedRoute && requestedRoute !== 'login'
                    ? 'admin'
                    : null
        );

        setRole(previewRole);

        // Start at login normally; screen/role query parameters support direct
        // desktop/mobile review links and deterministic visual QA captures.
        navigate(requestedRoute || 'login', {
            history: 'replace',
            scroll: false,
            focus: false,
            allowGuest: Boolean(previewRole),
        });
    }

    function prepareAccessibility() {
        const sidebar = $('#app-sidebar');
        const notifications = $('#notification-panel');
        const modal = $('#delete-modal');
        const toast = $('#success-toast');

        if (sidebar && !sidebar.id) sidebar.id = 'app-sidebar';

        if (notifications) {
            notifications.setAttribute('aria-hidden', notifications.classList.contains('is-open') ? 'false' : 'true');
            if (!notifications.hasAttribute('tabindex')) notifications.tabIndex = -1;
        }

        if (modal) {
            modal.setAttribute('aria-hidden', modal.classList.contains('is-open') ? 'false' : 'true');
            modal.setAttribute('role', modal.getAttribute('role') || 'dialog');
            modal.setAttribute('aria-modal', 'true');
        }

        if (toast) {
            toast.setAttribute('role', toast.getAttribute('role') || 'status');
            toast.setAttribute('aria-live', toast.getAttribute('aria-live') || 'polite');
            toast.setAttribute('aria-atomic', 'true');
        }

        $$('[data-toggle-notifications]').forEach((control) => {
            control.setAttribute('aria-controls', 'notification-panel');
            control.setAttribute('aria-expanded', 'false');
        });

        $$('[data-mobile-menu]').forEach((control) => {
            control.setAttribute('aria-controls', 'app-sidebar');
            control.setAttribute('aria-expanded', 'false');
        });
    }

    function handleClick(event) {
        const target = event.target;
        if (!(target instanceof Element)) return;

        const roleControl = target.closest('[data-role]:not([data-role-only])');
        if (roleControl && isActionControl(roleControl)) {
            const role = normaliseRole(roleControl.dataset.role);
            if (role) {
                event.preventDefault();
                state.pendingRole = role;
                logIn(role, roleControl.dataset.route);
                return;
            }
        }

        const signOut = target.closest('[data-sign-out], [data-action="sign-out"]');
        if (signOut) {
            event.preventDefault();
            logOut();
            return;
        }

        const mobileMenu = target.closest('[data-mobile-menu]');
        if (mobileMenu) {
            event.preventDefault();
            openSidebar(mobileMenu);
            return;
        }

        if (target.closest('[data-mobile-close], #mobile-overlay')) {
            event.preventDefault();
            closeSidebar(true);
            return;
        }

        const notificationToggle = target.closest('[data-toggle-notifications]');
        if (notificationToggle) {
            event.preventDefault();
            toggleNotifications(notificationToggle);
            return;
        }

        if (target.closest('[data-close-notifications]')) {
            event.preventDefault();
            closeNotifications(true);
            return;
        }

        const notificationItem = target.closest('[data-notification-item]');
        if (notificationItem) markNotificationRead(notificationItem);

        const deleteControl = target.closest('[data-confirm-delete]');
        if (deleteControl) {
            event.preventDefault();
            openDeleteModal(deleteControl);
            return;
        }

        if (target.closest('[data-cancel-delete]')) {
            event.preventDefault();
            closeDeleteModal(true);
            return;
        }

        if (target.closest('[data-delete-confirmed]')) {
            event.preventDefault();
            confirmDelete();
            return;
        }

        if (target.closest('[data-toast-close]')) {
            event.preventDefault();
            hideToast();
            return;
        }

        const passwordToggle = target.closest('[data-password-toggle]');
        if (passwordToggle) {
            event.preventDefault();
            togglePassword(passwordToggle);
            return;
        }

        const routeControl = target.closest('[data-route]');
        if (routeControl) {
            event.preventDefault();
            const route = normaliseRoute(routeControl.dataset.route);
            if (route === 'login' && state.role) {
                logOut();
            } else {
                navigate(route);
            }
            return;
        }

        const panel = $('#notification-panel');
        if (
            panel?.classList.contains('is-open') &&
            !panel.contains(target) &&
            !target.closest('[data-toggle-notifications]')
        ) {
            closeNotifications(false);
        }
    }

    function handleSubmit(event) {
        const form = event.target.closest('form[data-demo-form]');
        if (!form) return;

        event.preventDefault();

        if (!form.noValidate && !form.checkValidity()) {
            form.reportValidity();
            form.querySelector(':invalid')?.focus({ preventScroll: false });
            return;
        }

        const type = normaliseRoute(form.dataset.demoForm || form.id);
        const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
        const successRoute = normaliseRoute(
            submitter?.dataset.successRoute || form.dataset.successRoute || defaultSuccessRoute(type),
        );
        const successMessage =
            submitter?.dataset.successMessage ||
            form.dataset.successMessage ||
            defaultSuccessMessage(type);

        switch (type) {
            case 'login': {
                const submittedRole = normaliseRole(
                    submitter?.dataset.role ||
                    form.dataset.role ||
                    readField(form, ['role']) ||
                    state.pendingRole ||
                    inferRoleFromLogin(form),
                );
                logIn(submittedRole || 'admin', successRoute);
                return;
            }

            case 'apartment-create':
                simulateApartmentCreate(form);
                form.reset();
                navigate(successRoute || 'apartments');
                showToast(successMessage || 'Apartment added successfully.');
                return;

            case 'lease-create':
                simulateLeaseCreate(form);
                form.reset();
                navigate(successRoute || 'bills');
                showToast(successMessage || 'Lease created and first bill generated.');
                return;

            case 'payment-create':
                simulatePayment(form);
                form.reset();
                updatePaymentSummary();
                navigate(successRoute || 'payments');
                showToast(successMessage || 'Payment recorded successfully.');
                return;

            case 'profile':
                simulateProfileSave(form);
                if (successRoute) navigate(successRoute);
                showToast(successMessage || 'Profile changes saved.');
                return;

            case 'password':
                form.reset();
                if (successRoute) navigate(successRoute);
                showToast(successMessage || 'Password updated successfully.');
                return;

            default:
                if (successRoute) navigate(successRoute);
                showToast(successMessage || 'Changes saved.');
        }
    }

    function handleInput(event) {
        const target = event.target;
        if (!(target instanceof Element)) return;

        if (target.matches('#payment-amount')) {
            validatePaymentAmount(target);
        }
    }

    function handleChange(event) {
        const target = event.target;
        if (!(target instanceof Element)) return;

        if (target.matches('#payment-bill')) updatePaymentSummary();

        if (target.matches('[data-status-filter]')) {
            filterTable(target.dataset.statusFilter, target.closest('.screen'));
        }
    }

    function handleKeydown(event) {
        const modal = $('#delete-modal');

        const searchInput = event.target;
        if (event.key === 'Enter' && searchInput instanceof Element && searchInput.matches('[data-table-search]')) {
            event.preventDefault();
            filterTable(searchInput.dataset.tableSearch, searchInput.closest('.screen'));
            return;
        }

        if (event.key === 'Tab' && modal?.classList.contains('is-open')) {
            trapFocus(event, modal);
            return;
        }

        if (event.key !== 'Escape') return;

        if (modal?.classList.contains('is-open')) {
            event.preventDefault();
            closeDeleteModal(true);
            return;
        }

        if ($('#notification-panel')?.classList.contains('is-open')) {
            event.preventDefault();
            closeNotifications(true);
            return;
        }

        if ($('#app-sidebar')?.classList.contains('is-open')) {
            event.preventDefault();
            closeSidebar(true);
        }
    }

    function handleLocationChange() {
        const route = normaliseRoute(window.location.hash);
        if (route && route !== state.route) {
            navigate(route, { history: 'none' });
        }
    }

    function navigate(rawRoute, options = {}) {
        const settings = {
            history: 'push',
            scroll: true,
            focus: true,
            allowGuest: false,
            ...options,
        };

        let route = normaliseRoute(rawRoute) || (state.role === 'tenant' ? 'tenant-dashboard' : 'dashboard');

        if (!state.role && route !== 'login' && !settings.allowGuest) route = 'login';
        if (state.role === 'tenant' && route === 'dashboard') route = 'tenant-dashboard';

        let screen = findScreen(route);
        if (screen && !isAllowedForRole(screen, state.role)) {
            route = state.role === 'tenant' ? 'tenant-dashboard' : 'dashboard';
            screen = findScreen(route);
        }

        if (!screen) {
            route = state.role === 'tenant' ? 'tenant-dashboard' : state.role ? 'dashboard' : 'login';
            screen = findScreen(route) || $('.screen[data-screen]');
        }

        if (!screen) return;

        $$('.screen[data-screen]').forEach((candidate) => {
            const active = candidate === screen;
            candidate.classList.toggle('is-active', active);
            candidate.hidden = !active;
            candidate.setAttribute('aria-hidden', active ? 'false' : 'true');
        });

        state.route = route;
        document.body.dataset.screen = route;

        updateShellVisibility(screen, route);
        updateSidebarActiveState(route);
        applyRoleVisibility();
        closeSidebar(false);
        closeNotifications(false);

        if (settings.history !== 'none') updateHistory(route, settings.history);

        const screenTitle = screen.dataset.title || $('[data-screen-title], h1', screen)?.textContent?.trim();
        if (screenTitle) document.title = `${screenTitle} · StayTrack`;

        if (settings.scroll) {
            window.scrollTo({ top: 0, left: 0, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
        }

        if (settings.focus) focusScreen(screen);

        document.dispatchEvent(
            new CustomEvent('staytrack:route-change', { detail: { route, role: state.role } }),
        );
    }

    function updateHistory(route, mode) {
        const url = `${window.location.pathname}${window.location.search}#${encodeURIComponent(route)}`;
        const method = mode === 'replace' ? 'replaceState' : 'pushState';

        if (mode === 'push' && window.location.hash === `#${encodeURIComponent(route)}`) {
            history.replaceState({ route }, '', url);
            return;
        }

        history[method]({ route }, '', url);
    }

    function updateShellVisibility(screen, route) {
        const shell = $('#app-shell');
        if (!shell) return;

        const screenInsideShell = shell.contains(screen);
        const shouldHide = route === 'login' && !screenInsideShell;
        shell.hidden = shouldHide;
        shell.setAttribute('aria-hidden', shouldHide ? 'true' : 'false');
    }

    function focusScreen(screen) {
        const heading = $('[data-screen-title], h1', screen);
        if (!heading) return;

        if (!heading.hasAttribute('tabindex')) heading.tabIndex = -1;
        window.requestAnimationFrame(() => heading.focus({ preventScroll: true }));
    }

    function findScreen(route) {
        return $$('.screen[data-screen]').find((screen) => screen.dataset.screen === route) || null;
    }

    function updateSidebarActiveState(route) {
        const activeRoute = routeGroup(route);

        $$('#app-sidebar [data-route], .app-sidebar [data-route]').forEach((link) => {
            const active = routeGroup(normaliseRoute(link.dataset.route)) === activeRoute;
            link.classList.toggle('is-active', active);
            link.dataset.active = active ? 'true' : 'false';
            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    }

    function routeGroup(route) {
        const groups = {
            'apartment-create': 'apartments',
            'apartment-detail': 'apartments',
            'lease-create': 'leases',
            'payment-create': 'payments',
            'payment-detail': 'payments',
        };
        return groups[route] || route;
    }

    function logIn(role, requestedRoute) {
        const resolvedRole = normaliseRole(role) || 'admin';
        setRole(resolvedRole);
        state.pendingRole = null;

        const requested = normaliseRoute(requestedRoute);
        const destination =
            requested && requested !== 'login'
                ? requested
                : resolvedRole === 'tenant'
                  ? 'tenant-dashboard'
                  : 'dashboard';

        navigate(destination);
    }

    function logOut() {
        closeDeleteModal(false);
        closeNotifications(false);
        closeSidebar(false);
        setRole(null);
        state.pendingRole = null;
        navigate('login', { allowGuest: true });

        const loginForm = $('form[data-demo-form="login"]');
        loginForm?.reset();
    }

    function setRole(role) {
        state.role = normaliseRole(role);
        const roleName = state.role || 'guest';
        document.documentElement.dataset.userRole = roleName;
        document.body.dataset.userRole = roleName;
        applyRoleVisibility();
        applyRoleCopy();
    }

    function applyRoleVisibility() {
        $$('[data-role-only]').forEach((element) => {
            const allowed = roleList(element.dataset.roleOnly).includes(state.role);
            element.hidden = !allowed;
            element.setAttribute('aria-hidden', allowed ? 'false' : 'true');
        });
    }

    function applyRoleCopy() {
        $$('[data-admin-text], [data-tenant-text]').forEach((element) => {
            const replacement = state.role === 'tenant' ? element.dataset.tenantText : element.dataset.adminText;
            if (replacement) element.textContent = replacement;
        });

        $$('[data-current-role]').forEach((element) => {
            element.textContent = state.role === 'tenant' ? 'Tenant' : state.role === 'admin' ? 'Administrator' : 'Guest';
        });
    }

    function isAllowedForRole(element, role) {
        if (!element.hasAttribute('data-role-only')) return true;
        return roleList(element.dataset.roleOnly).includes(role);
    }

    function roleList(value = '') {
        return value
            .split(/[\s,|]+/)
            .map(normaliseRole)
            .filter(Boolean);
    }

    function openSidebar(trigger) {
        const sidebar = $('#app-sidebar');
        if (!sidebar) return;

        state.lastFocused = trigger;
        sidebar.hidden = false;
        sidebar.classList.add('is-open');
        sidebar.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sidebar-open');

        const overlay = $('#mobile-overlay');
        if (overlay) {
            overlay.hidden = false;
            window.requestAnimationFrame(() => overlay.classList.add('is-open'));
        }

        $$('[data-mobile-menu]').forEach((button) => button.setAttribute('aria-expanded', 'true'));
        window.requestAnimationFrame(() => $(focusableSelector, sidebar)?.focus({ preventScroll: true }));
    }

    function closeSidebar(restoreFocus) {
        const sidebar = $('#app-sidebar');
        if (!sidebar) return;

        const wasOpen = sidebar.classList.contains('is-open');
        sidebar.classList.remove('is-open');
        sidebar.setAttribute('aria-hidden', desktopSidebar.matches ? 'false' : 'true');
        document.body.classList.remove('sidebar-open');

        const overlay = $('#mobile-overlay');
        if (overlay) {
            overlay.classList.remove('is-open');
            afterMotion(() => {
                if (!overlay.classList.contains('is-open')) overlay.hidden = true;
            });
        }

        $$('[data-mobile-menu]').forEach((button) => button.setAttribute('aria-expanded', 'false'));

        if (restoreFocus && wasOpen && state.lastFocused instanceof HTMLElement) {
            state.lastFocused.focus({ preventScroll: true });
        }
    }

    function toggleNotifications(trigger) {
        const panel = $('#notification-panel');
        if (!panel) return;

        if (panel.classList.contains('is-open')) {
            closeNotifications(true);
        } else {
            openNotifications(trigger);
        }
    }

    function openNotifications(trigger) {
        const panel = $('#notification-panel');
        if (!panel) return;

        state.lastFocused = trigger;
        clearTimeout(state.overlayTimer);
        panel.hidden = false;
        panel.setAttribute('aria-hidden', 'false');
        window.requestAnimationFrame(() => panel.classList.add('is-open'));
        document.body.classList.add('notifications-open');
        $$('[data-toggle-notifications]').forEach((button) => button.setAttribute('aria-expanded', 'true'));

        window.requestAnimationFrame(() => {
            ($('[data-close-notifications]', panel) || panel).focus({ preventScroll: true });
        });
    }

    function closeNotifications(restoreFocus) {
        const panel = $('#notification-panel');
        if (!panel) return;

        const wasOpen = panel.classList.contains('is-open');
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('notifications-open');
        $$('[data-toggle-notifications]').forEach((button) => button.setAttribute('aria-expanded', 'false'));

        clearTimeout(state.overlayTimer);
        state.overlayTimer = afterMotion(() => {
            if (!panel.classList.contains('is-open')) panel.hidden = true;
        });

        if (restoreFocus && wasOpen && state.lastFocused instanceof HTMLElement) {
            state.lastFocused.focus({ preventScroll: true });
        }
    }

    function markNotificationRead(item) {
        if (!item.matches('[data-unread], .is-unread')) return;

        item.classList.remove('is-unread');
        item.removeAttribute('data-unread');
        item.setAttribute('aria-label', `${item.getAttribute('aria-label') || 'Notification'} (read)`);

        $$('[data-notification-count]').forEach((badge) => {
            const next = Math.max(0, parseNumber(badge.textContent) - 1);
            badge.textContent = String(next);
            badge.hidden = next === 0;
        });
    }

    function openDeleteModal(trigger) {
        const modal = $('#delete-modal');
        if (!modal) return;

        state.deleteTrigger = trigger;
        state.deleteTarget = resolveDeleteTarget(trigger);
        state.lastFocused = document.activeElement;

        const subject =
            trigger.dataset.deleteName ||
            trigger.dataset.deleteLabel ||
            state.deleteTarget?.dataset.deleteLabel ||
            state.deleteTarget?.dataset.label ||
            'this item';

        $$('[data-delete-subject], [data-delete-name]', modal).forEach((element) => {
            element.textContent = subject;
        });

        clearTimeout(state.overlayTimer);
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        window.requestAnimationFrame(() => modal.classList.add('is-open'));

        window.requestAnimationFrame(() => {
            const preferred = $('[data-delete-confirmed], [data-cancel-delete]', modal);
            preferred?.focus({ preventScroll: true });
        });
    }

    function closeDeleteModal(restoreFocus) {
        const modal = $('#delete-modal');
        if (!modal) return;

        const wasOpen = modal.classList.contains('is-open');
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        clearTimeout(state.overlayTimer);
        state.overlayTimer = afterMotion(() => {
            if (!modal.classList.contains('is-open')) modal.hidden = true;
        });

        if (restoreFocus && wasOpen) {
            const returnTarget = state.deleteTrigger || state.lastFocused;
            if (returnTarget instanceof HTMLElement && returnTarget.isConnected) {
                returnTarget.focus({ preventScroll: true });
            }
        }

        if (!wasOpen || !restoreFocus) {
            state.deleteTrigger = null;
            state.deleteTarget = null;
        }
    }

    function confirmDelete() {
        const trigger = state.deleteTrigger;
        const target = state.deleteTarget;
        const table = target?.closest('[data-table], table');
        const message = trigger?.dataset.deleteMessage || 'Item deleted successfully.';
        const destination = normaliseRoute(trigger?.dataset.successRoute || '');

        closeDeleteModal(false);

        if (target?.isConnected) target.remove();
        if (table) updateEmptyState(table);

        state.deleteTrigger = null;
        state.deleteTarget = null;

        if (destination) navigate(destination);
        showToast(message);
    }

    function resolveDeleteTarget(trigger) {
        const reference = trigger.dataset.deleteTarget?.trim();
        if (reference) {
            if (reference.startsWith('#')) {
                const byId = document.getElementById(reference.slice(1));
                if (byId) return byId;
            }

            const byPlainId = document.getElementById(reference);
            if (byPlainId) return byPlainId;

            try {
                const bySelector = document.querySelector(reference);
                if (bySelector) return bySelector;
            } catch {
                // Human-readable labels are also valid data-delete-target values.
            }

            const byData = $$('[data-delete-item]').find((item) => item.dataset.deleteItem === reference);
            if (byData) return byData;
        }

        return trigger.closest('[data-delete-row], [data-row], tr, [data-card], .list-card');
    }

    function showToast(message, duration = 3600) {
        const toast = $('#success-toast');
        if (!toast) return;

        const messageElement =
            $('[data-toast-message], .toast-message, .toast__message', toast) ||
            $$('p, span', toast).find((element) => !element.closest('[data-toast-close]'));

        if (messageElement && message) messageElement.textContent = message;
        else if (message) toast.setAttribute('aria-label', message);

        clearTimeout(state.toastTimer);
        toast.hidden = false;
        toast.setAttribute('aria-hidden', 'false');
        window.requestAnimationFrame(() => toast.classList.add('is-visible'));

        state.toastTimer = window.setTimeout(hideToast, duration);
    }

    function hideToast() {
        const toast = $('#success-toast');
        if (!toast) return;

        clearTimeout(state.toastTimer);
        toast.classList.remove('is-visible');
        toast.setAttribute('aria-hidden', 'true');
        afterMotion(() => {
            if (!toast.classList.contains('is-visible')) toast.hidden = true;
        });
    }

    function simulateApartmentCreate(form) {
        const status = String(readField(form, ['status', 'apartment_status']) || '').toLowerCase();
        incrementMetric('apartments', 1);
        if (status === 'occupied') incrementMetric('occupied', 1);

        const values = {
            unit: readField(form, ['unit_number', 'unit', 'apartment_unit']),
            type: readField(form, ['unit_type', 'type']),
            floor: readField(form, ['floor']),
            rent: readField(form, ['monthly_rent', 'rent']),
            status: status || 'available',
        };
        populateFromTemplate('apartment', values, true);
    }

    function simulateLeaseCreate(form) {
        const values = {
            tenant: selectedText(form, ['tenant_id', 'tenant']),
            unit: selectedText(form, ['apartment_id', 'apartment', 'unit']),
            amount: readField(form, ['monthly_rent', 'rent', 'amount']),
            due: readField(form, ['due_date', 'start_date']),
            status: 'unpaid',
        };

        populateFromTemplate('bill', values, true);
        updateCounter('[data-bill-count]', 1);
    }

    function simulatePayment(form) {
        const select = $('#payment-bill', form) || $('#payment-bill');
        const option = select?.selectedOptions?.[0];
        const amountInput = $('#payment-amount', form) || $('#payment-amount');
        const amount = parseNumber(amountInput?.value || readField(form, ['amount_paid', 'amount']));
        const paidBefore = parseNumber(option?.dataset.paid);
        const total = parseNumber(option?.dataset.total);
        const balanceBefore = Number.isFinite(parseNumber(option?.dataset.balance))
            ? parseNumber(option?.dataset.balance)
            : Math.max(0, total - paidBefore);
        const appliedAmount = Math.max(0, Math.min(amount, balanceBefore || amount));
        const paidAfter = paidBefore + appliedAmount;
        const balanceAfter = Math.max(0, balanceBefore - appliedAmount);
        const tenant = option?.dataset.tenant || 'Tenant';
        const unit = option?.dataset.unit || '—';
        const method = selectedText(form, ['payment_method', 'method']) || 'Online payment';
        const date = readField(form, ['payment_date', 'date']) || new Date().toISOString().slice(0, 10);
        const currency = inferCurrencySymbol(option?.dataset.total || option?.dataset.balance || '');

        if (option) {
            option.dataset.paid = String(paidAfter);
            option.dataset.balance = String(balanceAfter);
            option.dataset.status = balanceAfter <= 0 ? 'paid' : 'partial';
        }

        incrementMetric('revenue', appliedAmount, true);
        updateCounter('[data-payment-count]', 1);

        const values = {
            id: `ST-${++state.simulatedPaymentId}`,
            tenant,
            unit,
            amount: formatMoney(appliedAmount, currency),
            method,
            date: formatDate(date),
            status: 'Completed',
            balance: formatMoney(balanceAfter, currency),
        };

        insertPaymentRow('recent-payments', values, true);
        insertPaymentRow('payments', values, true);
        populateLatestPayment(values);

        document.dispatchEvent(
            new CustomEvent('staytrack:payment-recorded', {
                detail: { ...values, amount: appliedAmount, balance: balanceAfter },
            }),
        );
    }

    function simulateProfileSave(form) {
        const fullName =
            readField(form, ['name', 'full_name']) ||
            [readField(form, ['first_name']), readField(form, ['last_name'])].filter(Boolean).join(' ');
        const email = readField(form, ['email']);
        const phone = readField(form, ['phone']);

        if (fullName) setAllText('[data-user-name], [data-profile-name]', fullName);
        if (email) setAllText('[data-user-email], [data-profile-email]', email);
        if (phone) setAllText('[data-user-phone], [data-profile-phone]', phone);

        if (fullName) {
            const initials = fullName
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part[0].toUpperCase())
                .join('');
            if (initials) setAllText('[data-user-initials]', initials);
        }
    }

    function updatePaymentSummary() {
        const select = $('#payment-bill');
        if (!select) return;

        const option = select.selectedOptions?.[0];
        const hasBill = Boolean(option?.value);
        const currency = inferCurrencySymbol(option?.dataset.total || option?.dataset.balance || '');
        const summary = {
            '#payment-summary-tenant': hasBill ? option?.dataset.tenant || '—' : '—',
            '#payment-summary-unit': hasBill ? option?.dataset.unit || '—' : '—',
            '#payment-summary-total': hasBill ? formatMoney(option?.dataset.total, currency) : formatMoney(0, currency),
            '#payment-summary-paid': hasBill ? formatMoney(option?.dataset.paid, currency) : formatMoney(0, currency),
            '#payment-summary-balance': hasBill ? formatMoney(option?.dataset.balance, currency) : formatMoney(0, currency),
        };

        Object.entries(summary).forEach(([selector, value]) => {
            const element = $(selector);
            if (element) element.textContent = value;
        });

        const amount = $('#payment-amount');
        if (amount) {
            const balance = hasBill ? Math.max(0, parseNumber(option?.dataset.balance)) : 0;
            amount.max = String(balance);
            amount.disabled = !hasBill || balance <= 0;
            amount.setCustomValidity('');
            amount.dataset.balance = String(balance);
            if (parseNumber(amount.value) > balance) amount.value = balance > 0 ? String(balance) : '';
        }
    }

    function validatePaymentAmount(input) {
        const balance = parseNumber(input.dataset.balance || input.max);
        const amount = parseNumber(input.value);

        if (amount > balance && balance >= 0) {
            input.setCustomValidity(`Payment cannot exceed the remaining balance of ${formatMoney(balance)}.`);
        } else {
            input.setCustomValidity('');
        }
    }

    function cacheMetricValues() {
        $$('[data-metric]').forEach((metric) => {
            const valueElement = $('[data-metric-value], .metric-value', metric) || metric;
            if (!valueElement.dataset.value) valueElement.dataset.value = String(parseNumber(valueElement.textContent));
        });
    }

    function incrementMetric(name, amount, currency = false) {
        $$(`[data-metric="${name}"]`).forEach((metric) => {
            const valueElement = $('[data-metric-value], .metric-value', metric) || metric;
            const current = parseNumber(valueElement.dataset.value || valueElement.textContent);
            const next = current + Number(amount || 0);
            const original = valueElement.textContent;

            valueElement.dataset.value = String(next);
            valueElement.textContent = currency || /[$€£₱¥]/.test(original)
                ? formatMoney(next, inferCurrencySymbol(original))
                : formatNumber(next);
        });
    }

    function updateCounter(selector, change) {
        $$(selector).forEach((element) => {
            const next = Math.max(0, parseNumber(element.dataset.value || element.textContent) + change);
            element.dataset.value = String(next);
            element.textContent = formatNumber(next);
        });
    }

    function insertPaymentRow(tableName, values, prepend) {
        const table = $(`[data-table="${tableName}"]`);
        if (!table) return;

        const template = $(`template[data-${tableName}-row-template], template[data-payment-row-template]`);
        if (template) {
            const fragment = template.content.cloneNode(true);
            fillFields(fragment, values);
            const container = $('tbody', table) || table;
            container[prepend ? 'prepend' : 'append'](fragment);
            updateEmptyState(table);
            return;
        }

        if (table.tagName !== 'TABLE') return;
        const body = $('tbody', table) || table.createTBody();
        const headers = $$('thead th', table).map((header) => header.textContent.trim().toLowerCase());
        if (!headers.length) return;

        const row = document.createElement('tr');
        row.dataset.simulatedRecord = 'true';
        row.dataset.status = values.status.toLowerCase();

        headers.forEach((header) => {
            const cell = document.createElement('td');
            const value = paymentValueForHeader(header, values);

            if (value instanceof Node) cell.append(value);
            else cell.textContent = value;

            row.append(cell);
        });

        body[prepend ? 'prepend' : 'append'](row);
        updateEmptyState(table);
    }

    function paymentValueForHeader(header, values) {
        if (/tenant|resident/.test(header)) return values.tenant;
        if (/unit|apartment|property/.test(header)) return values.unit;
        if (/amount|paid/.test(header)) return values.amount;
        if (/method/.test(header)) return values.method;
        if (/date/.test(header)) return values.date;
        if (/status/.test(header)) return values.status;
        if (/reference|receipt|transaction|payment id|^id$/.test(header)) return values.id;
        if (/action|view/.test(header)) {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.route = 'payment-detail';
            button.className = 'table-action';
            button.textContent = 'View';
            return button;
        }
        return '—';
    }

    function populateLatestPayment(values) {
        $$('[data-latest-payment-field]').forEach((element) => {
            const key = element.dataset.latestPaymentField;
            if (key && Object.hasOwn(values, key)) element.textContent = values[key];
        });

        const detailScreen = findScreen('payment-detail');
        if (detailScreen) {
            $$('[data-payment-field]', detailScreen).forEach((element) => {
                const key = element.dataset.paymentField;
                if (key && Object.hasOwn(values, key)) element.textContent = values[key];
            });
        }
    }

    function populateFromTemplate(type, values, prepend) {
        const template = $(`template[data-${type}-row-template]`);
        if (!template) return;

        const fragment = template.content.cloneNode(true);
        fillFields(fragment, values);
        const container = $(`[data-${type}-list]`) || $(`[data-table="${type}s"] tbody`);
        if (container) container[prepend ? 'prepend' : 'append'](fragment);
    }

    function fillFields(root, values) {
        Object.entries(values).forEach(([key, value]) => {
            $$(`[data-field="${key}"]`, root).forEach((element) => {
                element.textContent = value || '—';
            });
        });
    }

    function filterTable(name, screen) {
        const context = screen || document;
        const table = name ?
            document.getElementById(name) ||
            $(`[data-table="${name}"]`, context) ||
            $(`[data-table="${name}"]`) :
            $('[data-table]', context);
        if (!table) return;

        const tableName = table.dataset.table || name || '';
        const search = $(`[data-table-search="${tableName}"]`, context) || $('[data-table-search]', context);
        const statusFilter = $(`[data-status-filter="${tableName}"]`, context) || $('[data-status-filter]', context);
        const query = search?.value?.trim().toLocaleLowerCase() || '';
        const status = statusFilter?.value?.trim().toLocaleLowerCase() || 'all';
        const rows = $$('tbody tr, [data-row]', table);

        rows.forEach((row) => {
            const textMatch = !query || row.textContent.toLocaleLowerCase().includes(query);
            const rowStatus = (row.dataset.status || $('[data-status]', row)?.dataset.status || '').toLocaleLowerCase();
            const statusMatch = !status || status === 'all' || rowStatus === status || row.textContent.toLocaleLowerCase().includes(status);
            row.hidden = !(textMatch && statusMatch);
        });

        updateEmptyState(table, rows.some((row) => !row.hidden));
    }

    function updateEmptyState(table, visibleOverride) {
        const rows = $$('tbody tr, [data-row]', table);
        const hasVisibleRows = visibleOverride ?? rows.some((row) => !row.hidden);
        const tableName = table.dataset.table;
        const context = table.closest('.screen') || document;
        const empty =
            (tableName && $(`[data-table-empty="${tableName}"]`, context)) ||
            $('[data-table-empty]', context);

        if (empty) empty.hidden = hasVisibleRows;
    }

    function togglePassword(control) {
        const targetId = control.dataset.passwordToggle;
        let input = targetId ? document.getElementById(targetId.replace(/^#/, '')) : null;
        if (!input) input = $('input[type="password"], input[data-password-input]', control.closest('.field, .form-field, label') || control.parentElement);
        if (!input) return;

        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        control.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        control.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');

        const label = $('[data-password-toggle-label]', control);
        if (label) label.textContent = reveal ? 'Hide' : 'Show';
    }

    function trapFocus(event, container) {
        const focusable = $$(focusableSelector, container).filter((element) => !element.hidden && element.offsetParent !== null);
        if (!focusable.length) {
            event.preventDefault();
            container.tabIndex = -1;
            container.focus();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function selectedText(form, names) {
        for (const name of names) {
            const field = form.elements.namedItem(name);
            if (field instanceof HTMLSelectElement && field.value) {
                return field.selectedOptions[0]?.textContent?.trim() || field.value;
            }
        }
        return '';
    }

    function readField(form, names) {
        for (const name of names) {
            const field = form.elements.namedItem(name) || $(`#${name}`, form);
            if (field instanceof RadioNodeList) {
                if (field.value) return field.value;
            } else if (field && 'value' in field && String(field.value).trim()) {
                return String(field.value).trim();
            }
        }
        return '';
    }

    function inferRoleFromLogin(form) {
        const email = readField(form, ['email']).toLowerCase();
        return email.includes('tenant') || email.includes('resident') ? 'tenant' : 'admin';
    }

    function defaultSuccessRoute(type) {
        return {
            'apartment-create': 'apartments',
            'lease-create': 'bills',
            'payment-create': 'payments',
        }[type] || '';
    }

    function defaultSuccessMessage(type) {
        return {
            'apartment-create': 'Apartment added successfully.',
            'lease-create': 'Lease created and first bill generated.',
            'payment-create': 'Payment recorded successfully.',
            profile: 'Profile changes saved.',
            password: 'Password updated successfully.',
        }[type] || 'Changes saved.';
    }

    function normaliseRoute(value = '') {
        try {
            return decodeURIComponent(String(value))
                .trim()
                .replace(/^#\/?/, '')
                .replace(/^\//, '')
                .toLowerCase();
        } catch {
            return String(value).trim().replace(/^#?\/?/, '').toLowerCase();
        }
    }

    function normaliseRole(value = '') {
        const role = String(value || '').trim().toLowerCase();
        return role === 'admin' || role === 'tenant' ? role : null;
    }

    function isActionControl(element) {
        return element.matches('button, a, input[type="button"], input[type="submit"], [role="button"]');
    }

    function parseNumber(value) {
        const parsed = Number.parseFloat(String(value ?? '').replace(/[^0-9.-]+/g, ''));
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('en-US', { maximumFractionDigits: Number.isInteger(value) ? 0 : 2 }).format(value || 0);
    }

    function inferCurrencySymbol(sample = '') {
        const configured = document.body?.dataset.currencySymbol || document.documentElement.dataset.currencySymbol;
        if (configured) return configured;

        const match = String(sample).match(/[$€£₱¥]/);
        if (match) return match[0];

        const metricSample = $('[data-metric="revenue"]')?.textContent?.match(/[$€£₱¥]/);
        return metricSample?.[0] || '$';
    }

    function formatMoney(value, symbol = inferCurrencySymbol(value)) {
        return `${symbol}${new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(parseNumber(value))}`;
    }

    function formatDate(value) {
        const date = new Date(`${value}T00:00:00`);
        if (Number.isNaN(date.getTime())) return value || 'Today';
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        }).format(date);
    }

    function setAllText(selector, value) {
        $$(selector).forEach((element) => {
            element.textContent = value;
        });
    }

    function afterMotion(callback) {
        const delay = reducedMotion.matches ? 0 : 220;
        return window.setTimeout(callback, delay);
    }
})();
