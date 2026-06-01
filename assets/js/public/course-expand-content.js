/**
 * Håndterer ekspanderbart innhold og accordion-funksjonalitet
 */
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
            toggleButton.innerHTML = `Vis mer <i class="ka-icon icon-chevron-down"></i>`;
            
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
        toggleButton.innerHTML = `Vis mer <i class="ka-icon icon-chevron-down"></i>`;
    } else {
        // Ekspander innhold
        element.style.maxHeight = 'none';
        element.classList.add('expanded');
        element.classList.remove('collapsed');
        toggleButton.innerHTML = `Lukk <i class="ka-icon icon-chevron-up"></i>`;
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
            toggleButton.innerHTML = `Lukk <i class="ka-icon icon-chevron-up"></i>`;
        }
    }
}

// Funksjon for å håndtere accordion

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
            otherIcon.textContent = "+";
        }
    });

    if (content.classList.contains("open")) {
        // Lukk denne seksjonen
        content.style.height = "0";
        content.classList.remove("open");
        accordionItem.classList.remove("active");
        icon.textContent = "+";
    } else {
        // Åpne denne seksjonen
        content.style.height = content.scrollHeight + 30 + "px";
        content.classList.add("open");
        accordionItem.classList.add("active");
        icon.textContent = "×";
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
            this.textContent = isExpanded ? 'Vis færre' : 'Vis flere lokasjoner';
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
        return interactive && target.contains(interactive);
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

// Kjør når DOM er lastet
document.addEventListener('DOMContentLoaded', function() {
    initExpandableContent();
    initAccordion();
    initLocationTabsToggle();
    initCursorTooltip();
});
