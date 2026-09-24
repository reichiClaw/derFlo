/* derFlo – schlanke Verbesserungen ohne Abhängigkeiten.
   Alles hier ist optional: Inhalte, Navigation, Kontaktlinks und das
   Formular funktionieren auch ohne JavaScript. */
(function () {
  "use strict";

  var doc = document;
  var root = doc.documentElement;
  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var lang = root.getAttribute("lang") === "en" ? "en" : "de";

  var text = {
    de: {
      required: "Bitte fülle dieses Feld aus.",
      email: "Bitte gib eine gültige E-Mail-Adresse ein.",
      short: "Ein paar Sätze mehr helfen mir, deine Anfrage einzuordnen.",
      fix: "Bitte prüfe die markierten Felder.",
      sending: "Deine Anfrage wird gesendet …",
      send: "Anfrage senden",
      success: "Vielen Dank für deine Anfrage! Auf Basis deiner Angaben stelle ich ein individuelles Menü und das zugehörige Angebot zusammen – das bekommst du in der Regel innerhalb von 24 Stunden per Mail. Mit kulinarischem Gruß, Flo",
      fail: "Das Senden hat gerade nicht funktioniert. Schreib mir bitte direkt an florian@karrer.kitchen oder ruf an: +43 660 14 11 020."
    },
    en: {
      required: "Please fill in this field.",
      email: "Please enter a valid email address.",
      short: "A few more sentences help me understand what you have in mind.",
      fix: "Please check the highlighted fields.",
      sending: "Sending your enquiry …",
      send: "Send enquiry",
      success: "Thank you for your enquiry! Based on your details, I’ll put together an individual menu and the matching offer – you’ll usually receive it by email within 24 hours. With culinary regards, Flo",
      fail: "Sending didn’t work just now. Please email me directly at florian@karrer.kitchen or call +43 660 14 11 020."
    }
  }[lang];

  /* ------------------------------------------------------------------
     Navigation (mobil ausklappbar)
     ------------------------------------------------------------------ */
  var header = doc.querySelector(".site-header");
  var toggle = doc.querySelector(".nav-toggle");
  var nav = doc.getElementById("site-nav");
  var desktop = window.matchMedia("(min-width: 60em)");

  function setNav(open) {
    if (!header || !toggle) return;
    header.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    if (open) {
      var first = nav.querySelector("a");
      if (first) first.focus();
    }
  }

  if (header && toggle && nav) {
    toggle.addEventListener("click", function () {
      setNav(!header.classList.contains("is-open"));
    });

    nav.addEventListener("click", function (e) {
      if (e.target.closest("a") && !desktop.matches) setNav(false);
    });

    doc.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && header.classList.contains("is-open")) {
        setNav(false);
        toggle.focus();
      }
    });

    doc.addEventListener("click", function (e) {
      if (header.classList.contains("is-open") && !header.contains(e.target)) setNav(false);
    });

    // Tastaturfokus im offenen Panel: verlässt der Fokus das Panel nach unten, schließt es.
    nav.addEventListener("focusout", function (e) {
      if (!header.classList.contains("is-open") || desktop.matches) return;
      if (e.relatedTarget && !header.contains(e.relatedTarget)) setNav(false);
    });

    var onDesktopChange = function (mq) {
      if (mq.matches) setNav(false);
    };
    if (desktop.addEventListener) desktop.addEventListener("change", onDesktopChange);
    else if (desktop.addListener) desktop.addListener(onDesktopChange);
  }

  /* ------------------------------------------------------------------
     Aktiven Abschnitt in der Navigation markieren
     ------------------------------------------------------------------ */
  var navLinks = Array.prototype.slice.call(doc.querySelectorAll(".site-nav__link[href^='#']"));
  var sections = navLinks
    .map(function (a) { return doc.getElementById(a.getAttribute("href").slice(1)); })
    .filter(Boolean);

  if ("IntersectionObserver" in window && sections.length) {
    var current = null;
    var sectionObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) current = entry.target.id;
      });
      navLinks.forEach(function (a) {
        var active = a.getAttribute("href") === "#" + current;
        if (active) a.setAttribute("aria-current", "true");
        else a.removeAttribute("aria-current");
      });
    }, { rootMargin: "-40% 0px -55% 0px" });
    sections.forEach(function (s) { sectionObserver.observe(s); });
  }

  /* ------------------------------------------------------------------
     Dezente Einblendungen (nur ohne reduced motion; Inhalte sind ohne JS sichtbar)
     ------------------------------------------------------------------ */
  var reveals = doc.querySelectorAll(".reveal");
  if (reveals.length) {
    if (reduceMotion || !("IntersectionObserver" in window)) {
      Array.prototype.forEach.call(reveals, function (el) { el.classList.add("is-visible"); });
    } else {
      var revealObserver = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            obs.unobserve(entry.target);
          }
        });
      }, { rootMargin: "0px 0px -8% 0px", threshold: 0.05 });
      Array.prototype.forEach.call(reveals, function (el) { revealObserver.observe(el); });

      // Sicherheitsnetz: Falls der Observer nicht auslöst, nach kurzer Zeit alles zeigen.
      window.setTimeout(function () {
        Array.prototype.forEach.call(reveals, function (el) { el.classList.add("is-visible"); });
      }, 2500);
    }
  }

  /* ------------------------------------------------------------------
     Anfragehilfe: verständliche Prüfung + Senden ohne Seitenwechsel
     ------------------------------------------------------------------ */
  var form = doc.querySelector("form.form");
  if (form) {
    var status = form.querySelector(".form__status");
    var submit = form.querySelector("button[type=submit]");
    var submitLabel = submit ? submit.innerHTML : "";

    function fieldWrap(input) {
      return input.closest(".field");
    }

    function setError(input, message) {
      var wrap = fieldWrap(input);
      var out = doc.getElementById(input.id + "-error");
      if (wrap) wrap.classList.toggle("is-invalid", !!message);
      if (out) out.textContent = message || "";
      if (message) input.setAttribute("aria-invalid", "true");
      else input.removeAttribute("aria-invalid");
    }

    function validateField(input) {
      var value = input.value.trim();
      if (input.required && !value) return text.required;
      if (input.type === "email" && value && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value)) return text.email;
      if (input.name === "message" && value && value.length < 10) return text.short;
      return "";
    }

    function showStatus(kind, message, list) {
      if (!status) return;
      status.className = "form__status" + (kind ? " is-" + kind : "");
      status.textContent = message;
      if (list && list.length) {
        var ul = doc.createElement("ul");
        list.forEach(function (item) {
          var li = doc.createElement("li");
          li.textContent = item;
          ul.appendChild(li);
        });
        status.appendChild(ul);
      }
    }

    var inputs = Array.prototype.slice.call(form.querySelectorAll("input:not([type=hidden]), select, textarea"))
      .filter(function (el) { return el.name !== "website"; });

    inputs.forEach(function (input) {
      input.addEventListener("blur", function () {
        setError(input, validateField(input));
      });
      input.addEventListener("input", function () {
        if (input.getAttribute("aria-invalid") === "true") setError(input, validateField(input));
      });
    });

    form.addEventListener("submit", function (e) {
      var firstInvalid = null;
      inputs.forEach(function (input) {
        var msg = validateField(input);
        setError(input, msg);
        if (msg && !firstInvalid) firstInvalid = input;
      });

      if (firstInvalid) {
        e.preventDefault();
        showStatus("error", text.fix);
        firstInvalid.focus();
        return;
      }

      if (!window.fetch || !window.FormData) return; // klassisches Absenden an anfrage.php

      e.preventDefault();
      showStatus("", text.sending);
      if (submit) {
        submit.disabled = true;
        submit.textContent = text.sending;
      }

      fetch(form.getAttribute("action"), {
        method: "POST",
        body: new FormData(form),
        headers: { "Accept": "application/json" },
        credentials: "same-origin"
      })
        .then(function (res) {
          return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        })
        .then(function (result) {
          var data = result.data || {};
          if (result.ok && data.ok) {
            showStatus("success", data.message || text.success);
            form.reset();
            inputs.forEach(function (input) { setError(input, ""); });
            if (status) {
              status.setAttribute("tabindex", "-1");
              status.focus();
            }
          } else {
            var errors = data.errors || {};
            var names = Object.keys(errors);
            names.forEach(function (name) {
              var input = form.elements[name];
              if (input && input.id) setError(input, errors[name]);
            });
            // Wenn der Server keine Feldfehler nennt (z. B. Mailversand fehlgeschlagen),
            // die Serverantwort bzw. den Hinweis auf den Direktkontakt zeigen.
            showStatus("error", data.message || (names.length ? text.fix : text.fail));
            if (names.length && form.elements[names[0]]) form.elements[names[0]].focus();
          }
        })
        .catch(function () {
          showStatus("error", text.fail);
        })
        .then(function () {
          if (submit) {
            submit.disabled = false;
            submit.innerHTML = submitLabel;
          }
        });
    });
  }

  /* ------------------------------------------------------------------
     Jahreszahl im Footer
     ------------------------------------------------------------------ */
  var year = doc.querySelector("[data-year]");
  if (year) year.textContent = String(new Date().getFullYear());
})();
