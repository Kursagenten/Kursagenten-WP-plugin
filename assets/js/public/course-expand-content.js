/**
 * Håndterer ekspanderbart innhold og accordion-funksjonalitet
 */
function kaExpandI18n(key, fallback) {
    if (typeof kursagentenExpandContent !== 'undefined' && kursagentenExpandContent.i18n && kursagentenExpandContent.i18n[key]) {
        return kursagentenExpandContent.i18n[key];
    }
    return fallback || key;
}

function kaExpandToggleHtml(labelKey, fallback, iconClass) {
    return kaExpandI18n(labelKey, fallback) + ' <i class="ka-icon ' + iconClass + '"></i>';
}

function initExpandableContent() {
    const expandableElements = document.querySelectorAll('.expand-content');

    expandableElements.forEach(element => {
        const dataSize = element.dataset.size;
        
        // Hvis data-size er "auto", ikke begrens høyden
        if (dataSize === 'auto') {
            return; // Hopp over dette elementet
        }
        
        const maxHeight = parseInt(dataSize) || 200;
        
        // Sjekk om innholdet er høyere enn maksimal høyde
        if (element.scrollHeight > maxHeight) {
            // Legg til collapsed klasse og sett maks høyde
            element.classList.add('collapsed');
            element.style.maxHeight = `${maxHeight}px`;
            
            // Opprett toggle-knapp
            const toggleButton = document.createElement('div');
            toggleButton.className = 'expand-toggle';
            toggleButton.innerHTML = kaExpandToggleHtml('showMore', 'Vis mer', 'icon-chevron-down');
            
            // Legg til toggle-knapp etter innholdselementet
            element.parentNode.insertBefore(toggleButton, element.nextSibling);
            toggleButton.style.display = 'block';
            
            // Håndter klikk på toggle-knapp
            toggleButton.addEventListener('click', (e) => {
                e.stopPropagation(); // Hindre at accordion-triggeren aktiveres
                toggleExpand(element, toggleButton, maxHeight);
            });
        }
    });
}

// Funksjon for å håndtere expand/collapse
function toggleExpand(element, toggleButton, maxHeight) {
    const isExpanded = element.classList.contains('expanded');
    
    if (isExpanded) {
        // Kollaps innhold
        element.style.maxHeight = `${maxHeight}px`;
        element.classList.remove('expanded');
        element.classList.add('collapsed');
        toggleButton.innerHTML = kaExpandToggleHtml('showMore', 'Vis mer', 'icon-chevron-down');
    } else {
        // Ekspander innhold
        element.style.maxHeight = 'none';
        element.classList.add('expanded');
        element.classList.remove('collapsed');
        toggleButton.innerHTML = kaExpandToggleHtml('close', 'Lukk', 'icon-chevron-up');
    }
}

function toggleAccordionHeight(target) {
    const accordionItem = target.closest(".courselist-item");
    if (!accordionItem) {
        //console.error("Feil: Fant ikke .courselist-item for", target);
        return;
    }

    const expandContent = accordionItem.closest(".expand-content");
    if (!expandContent) {
        //console.log("Feil: Fant ikke .expand-content for", accordionItem);
        return;
    }

    const isExpanded = expandContent.classList.contains("expanded");

    // Bare øk høyden hvis den ikke allerede er utvidet
    if (!isExpanded) {
        // Fjern maxHeight for å la innholdet vokse fritt
        expandContent.style.maxHeight = 'none';
        expandContent.classList.add("expanded");
        expandContent.classList.remove("collapsed");
        
        // Oppdater knappetekst
        const toggleButton = expandContent.parentNode.querySelector('.expand-toggle');
        if (toggleButton) {
            toggleButton.innerHTML = kaExpandToggleHtml('close', 'Lukk', 'icon-chevron-up');
        }
    }
}

// Funksjon for å håndtere accordion

/**
 * Single-course rows reuse .course-available as the accordion trigger (CSS dot).
 * Those icons must not get +/- text injected by toggleAccordion.
 */
function isCourseAvailabilityIcon(icon) {
    return icon && icon.classList.contains('course-available');
}

function resetAccordionIcon(icon) {
    if (!icon) {
        return;
    }
    if (isCourseAvailabilityIcon(icon)) {
        icon.textContent = '';
        return;
    }
    icon.textContent = '+';
}

function setAccordionIconOpen(icon) {
    if (!icon) {
        return;
    }
    if (isCourseAvailabilityIcon(icon)) {
        icon.textContent = '';
        return;
    }
    icon.textContent = '×';
}

function toggleAccordion(target) {
    const accordionItem = target.closest(".courselist-item");
    if (!accordionItem) {
        console.error("Feil: Fant ikke .courselist-item for", target);
        return;
    }

    // Støtter både courselist-content og accordion-content
    const content = accordionItem.querySelector(".courselist-content, .accordion-content");
    const icon = accordionItem.querySelector(".accordion-icon");

    if (!content || !icon) {
        console.error("Feil: Fant ikke content eller accordion-icon for", accordionItem);
        return;
    }

    // Oppdatert selektor for å støtte begge typer innhold
    const allContents = document.querySelectorAll(".courselist-content, .accordion-content");
    const allItems = document.querySelectorAll(".courselist-item");
    const allIcons = document.querySelectorAll(".accordion-icon");

    // Lukk alle andre seksjoner
    allContents.forEach((otherContent) => {
        if (otherContent !== content) {
            otherContent.style.height = "0";
            otherContent.classList.remove("open");
        }
    });
    allItems.forEach((otherItem) => {
        if (otherItem !== accordionItem) {
            otherItem.classList.remove("active");
        }
    });
    allIcons.forEach((otherIcon) => {
        if (otherIcon !== icon) {
            resetAccordionIcon(otherIcon);
        }
    });

    if (content.classList.contains("open")) {
        // Lukk denne seksjonen
        content.style.height = "0";
        content.classList.remove("open");
        accordionItem.classList.remove("active");
        resetAccordionIcon(icon);
    } else {
        // Åpne denne seksjonen
        content.style.height = content.scrollHeight + 30 + "px";
        content.classList.add("open");
        accordionItem.classList.add("active");
        setAccordionIconOpen(icon);
    }

    // Kall på toggleAccordionHeight for å håndtere expand-content
    toggleAccordionHeight(target);
}

// Funksjoner for å håndtere clickopen elementer
function initAccordion() {
    const elements = document.querySelectorAll(".clickopen");
    
    elements.forEach((element) => {
        element.removeEventListener("click", handleAccordionClick);
        element.addEventListener("click", handleAccordionClick);
    });
}

function handleAccordionClick(event) {
    toggleAccordion(event.target);
}

/**
 * Location tabs "Vis flere lokasjoner" toggle
 */
function initLocationTabsToggle() {
    const toggles = document.querySelectorAll('.location-tabs-toggle');
    toggles.forEach((btn) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const ul = this.closest('.location-tabs');
            if (!ul) return;
            const isExpanded = ul.classList.toggle('expanded');
            this.setAttribute('aria-expanded', isExpanded);
            this.textContent = isExpanded
                ? kaExpandI18n('showFewerLocations', 'Vis færre')
                : kaExpandI18n('showMoreLocations', 'Vis flere lokasjoner');
        });
    });
}

/**
 * Cursor-following tooltip for elements with the .ka-cursor-tooltip class and a
 * data-title attribute. A single bubble is appended to <body> and positioned at
 * the mouse pointer, so it never gets clipped by overflow:hidden containers
 * (e.g. the accordion) and always reads the current data-title (which may change
 * when a row is opened/closed).
 */
function initCursorTooltip() {
    let bubble = null;

    const getBubble = () => {
        if (!bubble) {
            bubble = document.createElement('div');
            bubble.className = 'ka-cursor-tooltip-bubble';
            document.body.appendChild(bubble);
        }
        return bubble;
    };

    // Hide the tooltip when hovering interactive children (e.g. the signup
    // button or links) so it doesn't linger over the actual click target.
    const isOverInteractive = (event, target) => {
        const interactive = event.target.closest('a, button');
        if (!interactive || !target.contains(interactive)) {
            return false;
        }
        // Allow cursor tooltip on links/buttons that are the tooltip host (e.g. day-schedules trigger).
        if (interactive.classList.contains('ka-cursor-tooltip')) {
            return false;
        }
        return true;
    };

    const positionBubble = (event) => {
        if (!bubble) {
            return;
        }
        const offset = 14;
        const rect = bubble.getBoundingClientRect();
        let x = event.clientX + offset;
        let y = event.clientY + offset;

        // Flip to the left/top if the bubble would overflow the viewport
        if (x + rect.width > window.innerWidth - 8) {
            x = event.clientX - offset - rect.width;
        }
        if (y + rect.height > window.innerHeight - 8) {
            y = event.clientY - offset - rect.height;
        }

        bubble.style.left = Math.max(8, x) + 'px';
        bubble.style.top = Math.max(8, y) + 'px';
    };

    document.addEventListener('mouseover', (event) => {
        const target = event.target.closest('.ka-cursor-tooltip[data-title]');
        if (!target) {
            return;
        }
        if (isOverInteractive(event, target)) {
            if (bubble) {
                bubble.classList.remove('visible');
            }
            return;
        }
        const el = getBubble();
        el.textContent = target.getAttribute('data-title') || '';
        el.classList.add('visible');
        positionBubble(event);
    });

    document.addEventListener('mousemove', (event) => {
        if (!bubble || !bubble.classList.contains('visible')) {
            return;
        }
        const target = event.target.closest('.ka-cursor-tooltip[data-title]');
        if (!target || isOverInteractive(event, target)) {
            bubble.classList.remove('visible');
            return;
        }
        // Keep text in sync in case data-title changed while hovering
        const title = target.getAttribute('data-title') || '';
        if (bubble.textContent !== title) {
            bubble.textContent = title;
        }
        positionBubble(event);
    });

    document.addEventListener('mouseout', (event) => {
        if (!bubble) {
            return;
        }
        const target = event.target.closest('.ka-cursor-tooltip');
        if (!target) {
            return;
        }
        // Only hide when the pointer actually leaves the tooltip element
        const related = event.relatedTarget;
        if (!related || !target.contains(related)) {
            bubble.classList.remove('visible');
        }
    });
}

/**
 * Compact design: toggle courselist panel below the infostripe.
 *
 * Opening the panel when navigating in from a location tab (and the related scroll)
 * is handled by a small self-contained inline script in compact.php. This function
 * only wires up the manual toggle buttons and keeps their labels in sync.
 */
function initCompactCourselistPanel() {
    var panel = document.getElementById('ka-compact-courselist-panel');
    if (!panel) {
        return;
    }

    var toggles = document.querySelectorAll('[data-compact-panel-toggle]');
    var panelCloseWrap = document.querySelector('.compact-more-dates--panel-close');

    function renderToggleLabel(toggle, isOpen) {
        var closeLabel = toggle.getAttribute('data-label-close') || '';
        var openLabel = toggle.getAttribute('data-label-open') || '';
        var isCloseOnly = toggle.classList.contains('compact-more-dates-link--close');

        if (isOpen && closeLabel) {
            toggle.innerHTML = isCloseOnly
                ? '&larr; ' + closeLabel
                : closeLabel + ' &larr;';
            return;
        }
        if (!isOpen && openLabel && !isCloseOnly) {
            toggle.innerHTML = openLabel + ' &rarr;';
        }
    }

    function syncToggles(open) {
        toggles.forEach(function(toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.classList.toggle('is-open', open);
            renderToggleLabel(toggle, open);
        });
        if (panelCloseWrap) {
            panelCloseWrap.hidden = !open;
        }
    }

    function setPanelOpen(open) {
        panel.hidden = !open;
        syncToggles(open);

        if (open) {
            var firstItem = panel.querySelector('.compact-courselist-expand-first');
            var main = firstItem ? firstItem.querySelector('.courselist-main') : null;
            if (main && !firstItem.classList.contains('active') && typeof toggleAccordion === 'function') {
                toggleAccordion(main);
            }
        }
    }

    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            setPanelOpen(panel.hidden);
        });
    });

    // The inline opener may have already revealed the panel; sync the toggle UI to match
    // without re-running the accordion (the inline script handles that on load).
    if (!panel.hidden) {
        syncToggles(true);
    }
}

// Kjør når DOM er lastet
document.addEventListener('DOMContentLoaded', function() {
    initExpandableContent();
    initAccordion();
    initLocationTabsToggle();
    initCursorTooltip();
    initCompactCourselistPanel();
});
