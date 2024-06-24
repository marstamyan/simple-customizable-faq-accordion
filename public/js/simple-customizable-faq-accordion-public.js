;(function () {
    var doc = document,
        toggles = doc.querySelectorAll(".mk-faq-accordionTitle"),
        touchSupported = "ontouchstart" in window,
        pointerSupported = "pointerdown" in window,
        clickDelay, setAriaAttr, setAccordionAria, switchAccordion;

    clickDelay = function (e) {
        e.preventDefault()
        e.target.click()
    }

    setAriaAttr = function (el, ariaType, newProperty) {
        el.setAttribute(ariaType, newProperty)
    }
    setAccordionAria = function (el1, el2, expanded) {
        switch (expanded) {
            case "true":
                setAriaAttr(el1, "aria-expanded", "true")
                setAriaAttr(el2, "aria-hidden", "false")
                break
            case "false":
                setAriaAttr(el1, "aria-expanded", "false")
                setAriaAttr(el2, "aria-hidden", "true")
                break
            default:
                break
        }
    }
    switchAccordion = function (e) {
        console.log("triggered")
        e.preventDefault()
        var thisAnswer = e.target.parentNode.nextElementSibling
        var thisQuestion = e.target
        if (thisAnswer.classList.contains("is-collapsed")) {
            setAccordionAria(thisQuestion, thisAnswer, "true")
        } else {
            setAccordionAria(thisQuestion, thisAnswer, "false")
        }
        thisQuestion.classList.toggle("is-collapsed")
        thisQuestion.classList.toggle("is-expanded")
        thisAnswer.classList.toggle("is-collapsed")
        thisAnswer.classList.toggle("is-expanded")
        thisAnswer.classList.toggle("mk-faq-animateIn")
    }

    for (var i = 0, len = toggles.length; i < len; i++) {
        if (touchSupported) {
            toggles[i].addEventListener("touchstart", clickDelay, false)
        }
        if (pointerSupported) {
            toggles[i].addEventListener("pointerdown", clickDelay, false)
        }
        toggles[i].addEventListener("click", switchAccordion, false)
    }
})()
