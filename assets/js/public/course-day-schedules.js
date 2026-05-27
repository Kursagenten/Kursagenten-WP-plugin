/**
 * Course Day Schedules - on-demand fetch and popup display
 *
 * Renders the full daySchedules list for a single ka_coursedate in a shared modal.
 * Markup is intentionally NOT a <table> so the layout reflows nicely on mobile:
 * each day is a card with stacked rows on narrow viewports, and a grid-aligned
 * row on wider screens. Heavy lifting lives in CSS (see frontend-course-style.css).
 *
 * Trigger element contract:
 *   <a class="show-ka-day-schedules" data-coursedate-id="123" data-course-title="...">
 *       4 dager
 *   </a>
 *
 * Data attributes:
 *   - data-coursedate-id (required): WP post ID of ka_coursedate.
 *   - data-course-title  (optional): used as modal header until response arrives.
 *
 * Globals (provided via wp_localize_script):
 *   kursagentenDaySchedules = {
 *     ajaxUrl: '/wp-admin/admin-ajax.php',
 *     nonce:   '...',
 *     i18n: { ... }
 *   }
 */
(function ($) {
    'use strict';

    if (typeof $ === 'undefined') {
        return;
    }

    var config = window.kursagentenDaySchedules || {};
    var i18n = config.i18n || {};
    var cache = {}; // In-memory cache keyed on coursedate id for the current page view.

    /**
     * Ensure the shared modal container exists in DOM and return its jQuery wrapper.
     */
    function ensureModal() {
        var $modal = $('#ka-day-schedules-modal');
        if ($modal.length) {
            return $modal;
        }

        $modal = $(
            '<div id="ka-day-schedules-modal" class="ka-course-dates-modal ka-day-schedules-modal" style="display: none;" role="dialog" aria-modal="true" aria-hidden="true">' +
                '<div class="ka-modal-overlay"></div>' +
                '<div class="ka-modal-content" role="document">' +
                    '<div class="ka-modal-header">' +
                        '<h3 class="ka-modal-title">' +
                            '<i class="ka-icon icon-calendar" aria-hidden="true"></i> ' +
                            '<span class="ka-modal-title-text"></span>' +
                        '</h3>' +
                        '<button type="button" class="ka-modal-close" aria-label="' + escapeAttr(i18n.close || 'Lukk') + '">&times;</button>' +
                    '</div>' +
                    '<div class="ka-modal-body">' +
                        '<div class="ka-day-schedules-status" aria-live="polite"></div>' +
                        '<div class="ka-day-schedules-list" role="list"></div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append($modal);
        return $modal;
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function renderStatus($modal, message, type) {
        var $status = $modal.find('.ka-day-schedules-status');
        if (!message) {
            $status.empty().removeClass('is-loading is-error is-empty');
            return;
        }
        $status
            .removeClass('is-loading is-error is-empty')
            .addClass('is-' + (type || 'info'))
            .text(message);
    }

    /**
     * Render the day list. Each day becomes a card; the card uses a CSS grid that
     * collapses to one column on narrow screens. Instructor and room arrays are
     * rendered as comma-separated translation-safe spans.
     */
    function renderDays($modal, days) {
        var $list = $modal.find('.ka-day-schedules-list').empty();

        if (!days || !days.length) {
            $list.append(
                '<p class="ka-day-schedules-empty">' +
                escapeHtml(i18n.empty || 'Ingen kursdager tilgjengelig.') +
                '</p>'
            );
            return;
        }

        var headers = {
            date:        i18n.colDate        || 'Dato',
            weekday:     i18n.colWeekday     || 'Dag',
            time:        i18n.colTime        || 'Klokkeslett',
            instructors: i18n.colInstructors || 'Instruktør',
            rooms:       i18n.colRoom        || 'Lokale'
        };

        // Header row (visible on desktop only via CSS).
        var $header = $(
            '<div class="ka-day-schedules-row ka-day-schedules-header" role="row" aria-hidden="false">' +
                '<span class="ka-day-col ka-day-col-date" role="columnheader">' + escapeHtml(headers.date) + '</span>' +
                '<span class="ka-day-col ka-day-col-weekday" role="columnheader">' + escapeHtml(headers.weekday) + '</span>' +
                '<span class="ka-day-col ka-day-col-time" role="columnheader">' + escapeHtml(headers.time) + '</span>' +
                '<span class="ka-day-col ka-day-col-instructors" role="columnheader">' + escapeHtml(headers.instructors) + '</span>' +
                '<span class="ka-day-col ka-day-col-rooms" role="columnheader">' + escapeHtml(headers.rooms) + '</span>' +
            '</div>'
        );
        $list.append($header);

        days.forEach(function (day) {
            var instructorsHtml = (day.instructors || []).map(function (name) {
                return '<span class="notranslate" translate="no">' + escapeHtml(name) + '</span>';
            }).join('<br>') || '&mdash;';

            var roomsHtml = (day.rooms || []).map(function (room) {
                return '<span class="notranslate" translate="no">' + escapeHtml(room) + '</span>';
            }).join('<br>') || '&mdash;';

            var $row = $(
                '<div class="ka-day-schedules-row ka-day-schedules-item" role="listitem">' +
                    '<span class="ka-day-col ka-day-col-date" data-label="' + escapeAttr(headers.date) + '">' +
                        escapeHtml(day.date_formatted || '') +
                    '</span>' +
                    '<span class="ka-day-col ka-day-col-weekday" data-label="' + escapeAttr(headers.weekday) + '">' +
                        escapeHtml(day.weekday || '') +
                    '</span>' +
                    '<span class="ka-day-col ka-day-col-time" data-label="' + escapeAttr(headers.time) + '">' +
                        escapeHtml(day.time_formatted || '') +
                    '</span>' +
                    '<span class="ka-day-col ka-day-col-instructors" data-label="' + escapeAttr(headers.instructors) + '">' +
                        instructorsHtml +
                    '</span>' +
                    '<span class="ka-day-col ka-day-col-rooms" data-label="' + escapeAttr(headers.rooms) + '">' +
                        roomsHtml +
                    '</span>' +
                '</div>'
            );

            $list.append($row);
        });
    }

    function openModal($modal) {
        $modal.attr('aria-hidden', 'false').fadeIn(150);
        $('body').css('overflow', 'hidden');
        // Move focus to close button for keyboard users.
        $modal.find('.ka-modal-close').trigger('focus');
        $(document).trigger('ka:day-schedules:opened');
    }

    function closeModal($modal) {
        $modal.attr('aria-hidden', 'true').fadeOut(150, function () {
            $('body').css('overflow', '');
            $(document).trigger('ka:day-schedules:closed');
        });
    }

    function fetchAndRender(coursedateId, $modal, fallbackTitle) {
        if (!config.ajaxUrl || !config.nonce) {
            renderStatus($modal, i18n.errorConfig || 'AJAX-konfigurasjon mangler.', 'error');
            return;
        }

        $modal.find('.ka-modal-title-text').text(fallbackTitle || '');
        renderStatus($modal, i18n.loading || 'Laster kursdager…', 'loading');
        $modal.find('.ka-day-schedules-list').empty();

        if (cache[coursedateId]) {
            applyData($modal, cache[coursedateId], fallbackTitle);
            return;
        }

        $.ajax({
            url: config.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'kursagenten_get_day_schedules',
                nonce: config.nonce,
                coursedate_id: coursedateId
            }
        })
            .done(function (response) {
                if (response && response.success && response.data) {
                    cache[coursedateId] = response.data;
                    applyData($modal, response.data, fallbackTitle);
                } else {
                    var message = (response && response.data && response.data.message)
                        ? response.data.message
                        : (i18n.errorGeneric || 'Kunne ikke hente kursdager.');
                    renderStatus($modal, message, 'error');
                }
            })
            .fail(function () {
                renderStatus($modal, i18n.errorNetwork || 'Nettverksfeil. Prøv igjen.', 'error');
            });
    }

    function applyData($modal, data, fallbackTitle) {
        var title = data.course_title || fallbackTitle || '';
        $modal.find('.ka-modal-title-text').text(title);
        renderStatus($modal, '', '');
        renderDays($modal, data.days);
    }

    $(function () {
        $(document).on('click', '.show-ka-day-schedules', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $trigger = $(this);
            var coursedateId = parseInt($trigger.data('coursedate-id'), 10);
            if (!coursedateId || coursedateId <= 0) {
                return;
            }

            var fallbackTitle = $trigger.data('course-title') || '';
            var $modal = ensureModal();
            openModal($modal);
            fetchAndRender(coursedateId, $modal, fallbackTitle);
        });

        $(document).on('click', '#ka-day-schedules-modal .ka-modal-close, #ka-day-schedules-modal .ka-modal-overlay', function (e) {
            e.preventDefault();
            closeModal($('#ka-day-schedules-modal'));
        });

        $(document).on('keydown', function (e) {
            if (e.key !== 'Escape' && e.keyCode !== 27) {
                return;
            }
            var $modal = $('#ka-day-schedules-modal');
            if ($modal.length && $modal.is(':visible')) {
                closeModal($modal);
            }
        });
    });
})(window.jQuery);
