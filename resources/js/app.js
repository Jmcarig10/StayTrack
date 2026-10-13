/*
 * StayTrack — server-rendered behavior script.
 * Companions the /prototype/*.css design system. No framework.
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initSidebar();
        initNotifications();
        initPasswordToggle();
        initTableSearch();
        initStatusFilters();
        initPaymentMethodFilter();
        initPaymentBillSummary();
        initExportPayments();
        initPrintReceipt();
        initDeleteModal();
        initToast();
    }

    /* ------------------------------------------------------------------ */
    /* Mobile sidebar                                                       */
    /* ------------------------------------------------------------------ */

    function initSidebar() {
        var sidebar = document.getElementById('app-sidebar');
        var overlay = document.getElementById('mobile-overlay');

        if (!sidebar) {
            return;
        }

        document.querySelectorAll('[data-mobile-menu]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                openSidebar();
                trigger.setAttribute('aria-expanded', 'true');
            });
        });

        document.querySelectorAll('[data-mobile-close]').forEach(function (trigger) {
            trigger.addEventListener('click', closeSidebar);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeSidebar();
            }
        });

        function openSidebar() {
            sidebar.classList.add('is-open');
            if (overlay) {
                overlay.hidden = false;
                overlay.classList.add('is-open');
            }
            document.body.classList.add('sidebar-open');
            document.querySelector('[data-mobile-menu]')?.setAttribute('aria-expanded', 'true');
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            if (overlay) {
                overlay.hidden = true;
                overlay.classList.remove('is-open');
            }
            document.body.classList.remove('sidebar-open');
            document.querySelectorAll('[data-mobile-menu]').forEach(function (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            });
        }
    }

    /* ------------------------------------------------------------------ */
    /* Notification panel                                                   */
    /* ------------------------------------------------------------------ */

    function initNotifications() {
        var panel = document.getElementById('notification-panel');

        if (!panel) {
            return;
        }

        document.querySelectorAll('[data-toggle-notifications]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                var isOpen = panel.classList.contains('is-open');
                if (isOpen) {
                    closePanel();
                } else {
                    panel.hidden = false;
                    panel.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.querySelectorAll('[data-close-notifications]').forEach(function (trigger) {
            trigger.addEventListener('click', closePanel);
        });

        document.addEventListener('click', function (e) {
            if (!panel.classList.contains('is-open')) {
                return;
            }
            var inside = panel.contains(e.target) ||
                e.target.closest('[data-toggle-notifications]');
            if (!inside) {
                closePanel();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) {
                closePanel();
            }
        });

        document.querySelectorAll('[data-action="mark-notifications-read"]').forEach(function (button) {
            button.addEventListener('click', function () {
                panel.querySelectorAll('.notification-item.is-unread').forEach(function (item) {
                    item.classList.remove('is-unread');
                });
                document.querySelectorAll('.notification-dot').forEach(function (dot) {
                    dot.style.opacity = '0';
                });
            });
        });

        function closePanel() {
            panel.classList.remove('is-open');
            panel.hidden = true;
            document.querySelectorAll('[data-toggle-notifications]').forEach(function (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            });
        }
    }

    /* ------------------------------------------------------------------ */
    /* Password show / hide                                                 */
    /* ------------------------------------------------------------------ */

    function initPasswordToggle() {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = document.getElementById(button.getAttribute('data-password-toggle')) ||
                    button.closest('.password-field')?.querySelector('input');
                if (!target) {
                    return;
                }
                var show = target.type === 'password';
                target.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.classList.toggle('is-active', show);
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* Table text search                                                    */
    /* ------------------------------------------------------------------ */

    function initTableSearch() {
        document.querySelectorAll('[data-table-search]').forEach(function (input) {
            var table = document.getElementById(input.getAttribute('data-table-search'));
            input.addEventListener('focus', function () {
                input.removeAttribute('readonly');
            }, { once: true });
            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }
                event.preventDefault();
                applySearch(input, table);
                resetFilterChips(input);
            });
        });

        function applySearch(input, table) {
            if (!table) {
                return;
            }
            var query = input.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                if (!query || row.textContent.toLowerCase().includes(query)) {
                    row.hidden = false;
                } else {
                    row.hidden = true;
                }
            });
        }

        function resetFilterChips(input) {
            if (!input.value.trim()) {
                return;
            }
            document.querySelectorAll('[data-status-filter][data-table="' + input.getAttribute('data-table-search') + '"]').forEach(function (chip) {
                if (!chip.classList.contains('is-active')) {
                    chip.classList.remove('is-active');
                }
            });
            if (input.value.trim()) {
                var activeChip = document.querySelector('[data-status-filter][data-table="' + input.getAttribute('data-table-search') + '"][data-status-filter="all"]');
                if (activeChip) {
                    activeChip.classList.add('is-active');
                }
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /* Status filter chips                                                  */
    /* ------------------------------------------------------------------ */

    function initStatusFilters() {
        document.querySelectorAll('[data-status-filter]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var group = Array.from(chip.parentElement.children);
                group.forEach(function (sibling) {
                    sibling.classList.remove('is-active');
                });
                chip.classList.add('is-active');

                var table = document.getElementById(chip.getAttribute('data-table')) ||
                    chip.closest('.panel')?.querySelector('table');
                if (!table) {
                    return;
                }
                var status = chip.getAttribute('data-status-filter');
                table.querySelectorAll('tbody tr[data-status]').forEach(function (row) {
                    var match = status === 'all' || row.getAttribute('data-status') === status;
                    row.hidden = !match;
                });

                var search = table.closest('.panel')?.querySelector('[data-table-search="' + table.id + '"]');
                if (search) {
                    search.value = '';
                }
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* Payment method filter                                                */
    /* ------------------------------------------------------------------ */

    function initPaymentMethodFilter() {
        document.querySelectorAll('[data-payment-method-filter]').forEach(function (select) {
            select.addEventListener('change', function () {
                var method = select.value.toLowerCase();
                var table = document.getElementById(select.getAttribute('data-payment-method-filter'));
                if (!table) {
                    return;
                }
                table.querySelectorAll('tbody tr[data-method]').forEach(function (row) {
                    var match = method === 'all' ||
                        row.getAttribute('data-method').toLowerCase() === method;
                    row.hidden = !match;
                });
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* Payment create — bill summary + amount cap                           */
    /* ------------------------------------------------------------------ */

    function initPaymentBillSummary() {
        var field = document.querySelector('[data-payment-bill-field]');
        var amountInput = document.getElementById('payment-amount');

        if (!field) {
            return;
        }

        field.addEventListener('change', updateSummary);
        updateSummary();

        function updateSummary() {
            var option = field.options[field.selectedIndex];
            var data = option ? option.dataset : {};
            var balance = parseFloat(data.balance || '0') || 0;

            setText('payment-summary-bill', option && option.value ? 'Bill #' + option.value : '—');
            setText('payment-summary-tenant', data.tenant || '—');
            setText('payment-summary-unit', data.unit || '—');
            setText('payment-summary-total', data.total ? formatPeso(data.total) : '—');
            setText('payment-summary-paid', data.paid ? formatPeso(data.paid) : '—');
            setText('payment-summary-balance', data.balance ? formatPeso(data.balance) : '—');

            if (amountInput) {
                if (option && option.value) {
                    amountInput.max = balance.toFixed(2);
                    amountInput.placeholder = 'Up to ' + formatPeso(balance);
                } else {
                    amountInput.removeAttribute('max');
                    amountInput.placeholder = '';
                }
            }
        }

        function setText(id, value) {
            var el = document.getElementById(id);
            if (el) {
                el.textContent = value;
            }
        }

        function formatPeso(value) {
            return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    /* ------------------------------------------------------------------ */
    /* Payments export (CSV)                                                 */
    /* ------------------------------------------------------------------ */

    function initExportPayments() {
        document.querySelectorAll('[data-action="export-payments"]').forEach(function (button) {
            button.addEventListener('click', function () {
                var table = document.getElementById('payments-table');
                if (!table) {
                    return;
                }

                var headers = [];
                table.querySelectorAll('thead th').forEach(function (th) {
                    headers.push(th.textContent.trim());
                });

                var rows = [
                    headers.join(','),
                ];

                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    if (tr.hidden) {
                        return;
                    }
                    var cells = [];
                    tr.querySelectorAll('td').forEach(function (td) {
                        cells.push('"' + (td.textContent.trim().replace(/"/g, '""')) + '"');
                    });
                    rows.push(cells.join(','));
                });

                var blob = new Blob([rows.join('\n')], { type: 'text/csv' });
                var url = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = 'staytrack-payments-' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* Receipt print/download                                               */
    /* ------------------------------------------------------------------ */

    function initPrintReceipt() {
        document.querySelectorAll('[data-action="download-receipt"]').forEach(function (button) {
            button.addEventListener('click', window.print);
        });
    }

    /* ------------------------------------------------------------------ */
    /* Delete confirmation modal                                            */
    /* ------------------------------------------------------------------ */

    function initDeleteModal() {
        var modal = document.getElementById('delete-modal');
        var form = document.getElementById('delete-form');
        var confirmButton = document.querySelector('[data-delete-confirmed]');
        var nameHolder = document.querySelector('[data-delete-name]');
        var passwordField = document.getElementById('delete-password-field');
        var passwordInput = document.getElementById('delete-password-input');
        var passwordError = document.getElementById('delete-password-error');
        var pendingUrl = null;

        if (!modal) {
            return;
        }

        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-confirm-delete]');
            if (!trigger) {
                return;
            }
            e.preventDefault();
            pendingUrl = trigger.getAttribute('data-delete-url') || '';

            if (nameHolder) {
                nameHolder.textContent = trigger.getAttribute('data-delete-target') || 'this item';
            }

            showPasswordCheck(trigger.hasAttribute('data-delete-password'));
            modal.hidden = false;
            modal.classList.add('is-open');
            modal.querySelector('.modal')?.classList.add('is-open');
            if (passwordField && !passwordField.hidden) {
                passwordInput.focus();
            }
        });

        document.querySelectorAll('[data-cancel-delete]').forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        if (confirmButton) {
            confirmButton.addEventListener('click', function () {
                if (passwordField && !passwordField.hidden) {
                    var value = passwordInput.value.trim();
                    if (!value) {
                        passwordError.hidden = false;
                        passwordInput.focus();
                        return;
                    }
                    var hiddenPassword = document.getElementById('delete-password');
                    if (hiddenPassword) {
                        hiddenPassword.value = value;
                    }
                }
                if (form) {
                    form.setAttribute('action', pendingUrl);
                    form.submit();
                }
                closeModal();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        function showPasswordCheck(needed) {
            if (!passwordField) {
                return;
            }
            passwordField.hidden = !needed;
            passwordError.hidden = true;
            passwordInput.value = '';
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.querySelector('.modal')?.classList.remove('is-open');
            modal.hidden = true;
            showPasswordCheck(false);
            pendingUrl = null;
        }
    }

    /* ------------------------------------------------------------------ */
    /* Flash toast                                                          */
    /* ------------------------------------------------------------------ */

    function initToast() {
        var toast = document.getElementById('success-toast');

        if (!toast || toast.hidden) {
            return;
        }

        toast.classList.add('is-visible');

        var closeTimer = setTimeout(function () {
            hideToast();
        }, 5000);

        document.querySelectorAll('[data-toast-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                clearTimeout(closeTimer);
                hideToast();
            });
        });

        function hideToast() {
            toast.classList.remove('is-visible');
            toast.hidden = true;
        }
    }
})();
