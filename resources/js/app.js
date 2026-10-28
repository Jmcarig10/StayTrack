(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initSidebar();
        initNotifications();
        initProofModal();
        initPasswordToggle();
        initTableSearch();
        initStatusFilters();
        initPaymentMethodFilter();
        initPaymentBillSummary();
        initInstapayQr();
        initPrintReceipt();
        initDeleteModal();
        initExportModal();
        initToast();
    }

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

    function initNotifications() {
        var panel = document.getElementById('notification-panel');

        if (!panel) {
            return;
        }

        var panelTimer = null;

        function scheduleAutoClose() {
            clearTimeout(panelTimer);
            panelTimer = setTimeout(closePanel, 5000);
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
                    scheduleAutoClose();
                }
            });
        });

        panel.addEventListener('pointerdown', scheduleAutoClose);
        panel.addEventListener('focusin', scheduleAutoClose);

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
            clearTimeout(panelTimer);
            panel.classList.remove('is-open');
            panel.hidden = true;
            document.querySelectorAll('[data-toggle-notifications]').forEach(function (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            });
        }
    }

    function initProofModal() {
        var modal = document.getElementById('proof-modal');

        if (!modal) {
            return;
        }

        function openModal() {
            modal.hidden = false;
            modal.classList.add('is-open');
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        }

        document.querySelectorAll('[data-proof-open-modal]').forEach(function (button) {
            button.addEventListener('click', openModal);
        });

        modal.querySelectorAll('[data-proof-close]').forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    }

    function initPasswordToggle() {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            if (button.dataset.passwordToggleInit === '1') {
                return;
            }
            button.dataset.passwordToggleInit = '1';
            button.setAttribute('aria-pressed', 'false');
            button.addEventListener('click', function () {
                var target = document.getElementById(button.getAttribute('data-password-toggle')) ||
                    button.closest('.password-field')?.querySelector('input');
                if (!target) {
                    return;
                }
                var show = target.type === 'password';
                target.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', show ? 'true' : 'false');
                button.classList.toggle('is-active', show);
                var use = button.querySelector('use');
                if (use) {
                    var next = show ? '#icon-eye-off' : '#icon-eye';
                    use.setAttribute('href', next);
                    use.setAttribute('xlink:href', next);
                }
            });
        });
    }

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

    function initInstapayQr() {
        var panel = document.querySelector('[data-instapay-qr]');
        var select = document.getElementById('method');

        if (!panel || !select) {
            return;
        }

        var scanMethods = ['gcash', 'bank_transfer', 'online_payment'];

        function sync() {
            panel.hidden = scanMethods.indexOf(select.value) === -1;
        }

        select.addEventListener('change', sync);
        sync();
    }

    function initPrintReceipt() {
        document.querySelectorAll('[data-action="download-receipt"]').forEach(function (button) {
            button.addEventListener('click', window.print);
        });
    }

    function initDeleteModal() {
        var modal = document.getElementById('delete-modal');
        var form = document.getElementById('delete-form');
        var confirmButton = document.querySelector('[data-delete-confirmed]');
        var nameHolder = document.querySelector('[data-delete-name]');
        var titleHolder = document.getElementById('delete-modal-title');
        var verbHolder = document.querySelector('[data-delete-verb]');
        var noteHolder = document.querySelector('[data-delete-note]');
        var iconHolder = modal?.querySelector('[data-delete-icon]');
        var iconUse = iconHolder?.querySelector('use');
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

            applyMode(trigger.hasAttribute('data-archive-action'));
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

        function applyMode(isArchive) {
            if (titleHolder) {
                titleHolder.textContent = isArchive ? 'Archive this record?' : 'Delete this record?';
            }

            if (verbHolder) {
                verbHolder.textContent = isArchive ? 'archive' : 'delete';
            }

            if (noteHolder) {
                noteHolder.textContent = isArchive
                    ? 'You can restore this from Archive.'
                    : 'This action cannot be undone.';
            }

            if (confirmButton) {
                confirmButton.textContent = isArchive ? 'Yes, archive' : 'Yes, delete';
                confirmButton.classList.toggle('button-danger', !isArchive);
                confirmButton.classList.toggle('button-archive', isArchive);
            }

            if (iconHolder) {
                iconHolder.classList.toggle('danger', !isArchive);
                iconHolder.classList.toggle('warning', isArchive);
            }

            if (iconUse) {
                iconUse.setAttribute('href', isArchive ? '#icon-archive' : '#icon-trash');
            }
        }

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

    function initExportModal() {
        var modal = document.getElementById('export-modal');

        if (!modal) {
            return;
        }

        var title = document.getElementById('export-modal-title');
        var copy = document.getElementById('export-modal-copy');
        var pdfOption = modal.querySelector('[data-export-option="pdf"]');
        var excelOption = modal.querySelector('[data-export-option="excel"]');
        var firstOption = modal.querySelector('[data-export-option]');
        var activeTrigger = null;

        document.querySelectorAll('[data-export-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                activeTrigger = button;

                if (title) {
                    title.textContent = button.getAttribute('data-export-title') || 'Export report';
                }

                if (copy) {
                    copy.textContent = button.getAttribute('data-export-description') || 'Choose a format for your download.';
                }

                if (pdfOption) {
                    pdfOption.setAttribute('href', button.getAttribute('data-export-pdf') || '#');
                }

                if (excelOption) {
                    excelOption.setAttribute('href', button.getAttribute('data-export-excel') || '#');
                }

                modal.hidden = false;
                modal.classList.add('is-open');
                modal.querySelector('.modal')?.classList.add('is-open');
                document.body.classList.add('modal-open');
                button.setAttribute('aria-expanded', 'true');

                if (firstOption) {
                    firstOption.focus();
                }
            });
        });

        document.querySelectorAll('[data-export-close]').forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        modal.querySelectorAll('[data-export-option]').forEach(function (option) {
            option.addEventListener('click', closeModal);
        });

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

        function closeModal() {
            modal.classList.remove('is-open');
            modal.querySelector('.modal')?.classList.remove('is-open');
            modal.hidden = true;
            document.body.classList.remove('modal-open');

            if (activeTrigger) {
                activeTrigger.setAttribute('aria-expanded', 'false');
                activeTrigger.focus();
                activeTrigger = null;
            }
        }
    }

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
