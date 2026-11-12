(function () {
    "use strict";

    function getPanel(button) {
        var panel = document.getElementById(button.getAttribute("aria-controls"));
        if (!panel || !panel.classList.contains("mk-faq-accordion-content") ||
            panel.closest(".mk-faq-container") !== button.closest(".mk-faq-container")) {
            return null;
        }
        return panel;
    }

    function setExpanded(button, panel, expanded) {
        button.setAttribute("aria-expanded", String(expanded));
        panel.setAttribute("aria-hidden", String(!expanded));
        panel.hidden = !expanded;
        button.classList.toggle("is-expanded", expanded);
        button.classList.toggle("is-collapsed", !expanded);
        panel.classList.toggle("is-expanded", expanded);
        panel.classList.toggle("is-collapsed", !expanded);
    }

    function getButton(event) {
        return event.target && event.target.closest ?
            event.target.closest(".mk-faq-container .mk-faq-accordion-title") : null;
    }

    function initialize() {
        var buttons = document.querySelectorAll(".mk-faq-container .mk-faq-accordion-title");
        for (var i = 0; i < buttons.length; i++) {
            var panel = getPanel(buttons[i]);
            if (panel) {
                setExpanded(buttons[i], panel, false);
            }
        }
    }

    // Native buttons support mouse, touch, Enter and Space through one click
    // handler, without synthesized duplicate touch/pointer clicks.
    document.addEventListener("click", function (event) {
        var button = getButton(event);
        var panel = button && getPanel(button);
        if (panel) {
            setExpanded(button, panel, button.getAttribute("aria-expanded") !== "true");
        }
    });

    document.addEventListener("keydown", function (event) {
        var button = getButton(event);
        if (!button || event.altKey || event.ctrlKey || event.metaKey ||
            ["ArrowDown", "ArrowUp", "Home", "End"].indexOf(event.key) === -1) {
            return;
        }

        var container = button.closest(".mk-faq-container");
        var buttons = Array.prototype.filter.call(
            container.querySelectorAll(".mk-faq-accordion-title"),
            function (item) { return item.closest(".mk-faq-container") === container; }
        );
        var index = buttons.indexOf(button);
        if (index === -1) {
            return;
        }
        if (event.key === "Home") {
            index = 0;
        } else if (event.key === "End") {
            index = buttons.length - 1;
        } else {
            index = (index + (event.key === "ArrowDown" ? 1 : -1) + buttons.length) % buttons.length;
        }
        event.preventDefault();
        buttons[index].focus();
    });

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialize, { once: true });
    } else {
        initialize();
    }
})();
