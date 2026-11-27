/* ==========================================================================
   GB Popup
   Data di inizio e cookie vengono controllati qui, così il popup funziona anche
   con le pagine in cache.

   API pubblica (es. per form diversi da Contact Form 7):
   - window.gbPopup.subscribed()  iscrizione riuscita: non riproporre per un anno
   - window.gbPopup.close()       chiude il popup
   Eventi sul popup: "gb-popup:open", "gb-popup:close".
   ========================================================================== */
(function () {
	"use strict";

	const popup = document.getElementById("gb-popup");
	if (!popup) return;

	const data = popup.dataset;
	const preview = data.preview === "1";
	const cookieName = "gb_popup_" + data.key;
	const start = parseInt(data.start, 10) || 0;
	const end = parseInt(data.end, 10) || 0;
	const now = Date.now() / 1000;
	const SUBSCRIBED_DAYS = 365;

	const box = popup.querySelector(".gb-popup__box");
	const img = popup.querySelector(".gb-popup__image img");
	const source = popup.querySelector(".gb-popup__image source");
	const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	let lastFocus = null;
	let isOpen = false;
	let subscribed = false;

	function track(action) {
		if (typeof window.gtag === "function") {
			window.gtag("event", "gb_popup_" + action, { popup_id: data.key });
		}
	}

	// Cookie tecnico: memorizza solo che il popup è già stato chiuso
	function setCookie(days) {
		if (preview) return;
		document.cookie = cookieName + "=1; max-age=" + days * 86400 + "; path=/; SameSite=Lax" + (location.protocol === "https:" ? "; Secure" : "");
	}

	function hasCookie() {
		return document.cookie.split("; ").some((c) => c.indexOf(cookieName + "=") === 0);
	}

	function onKeydown(e) {
		if (e.key === "Escape") {
			close("close");
			return;
		}

		// Tengo il focus dentro il popup
		if (e.key === "Tab") {
			const focusables = Array.from(box.querySelectorAll(focusableSelector)).filter((el) => el.offsetParent !== null);
			if (!focusables.length) return;
			const first = focusables[0];
			const last = focusables[focusables.length - 1];
			if (!box.contains(document.activeElement) || document.activeElement === box) {
				e.preventDefault();
				(e.shiftKey ? last : first).focus();
			} else if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		}
	}

	function show() {
		if (isOpen) return;
		isOpen = true;
		lastFocus = document.activeElement;
		document.documentElement.classList.add("gb-popup-open");
		// Doppio rAF: la transizione parte dopo che il popup è stato reso visibile
		requestAnimationFrame(() =>
			requestAnimationFrame(() => {
				popup.classList.add("is-open");
				box.focus({ preventScroll: true });
			})
		);
		document.addEventListener("keydown", onKeydown);
		popup.dispatchEvent(new CustomEvent("gb-popup:open"));
		track("view");
	}

	// Un altro overlay aperto (menu mobile, ricerca, minicart...)? Lo deduco dal blocco dello scroll.
	function pageIsBusy() {
		const html = getComputedStyle(document.documentElement).overflow;
		const body = getComputedStyle(document.body).overflow;
		return html === "hidden" || body === "hidden";
	}

	let attempts = 0;

	function open() {
		// Riprovo ogni 2 secondi, ma al massimo 5 volte (un tema potrebbe bloccare lo scroll sempre)
		if (pageIsBusy() && attempts++ < 5) {
			setTimeout(open, 2000);
			return;
		}

		popup.hidden = false;

		if (!img) {
			show();
			return;
		}

		// Carico le immagini solo ora (prima la <source> mobile, così il browser scarica solo
		// quella adatta allo schermo) e apro quando sono pronte, al massimo dopo 3 secondi
		if (source && source.dataset.srcset) source.srcset = source.dataset.srcset;
		if (img.dataset.srcset) img.srcset = img.dataset.srcset;
		img.src = img.dataset.src;

		if (img.complete && img.naturalWidth) {
			show();
		} else {
			img.addEventListener("load", show, { once: true });
			img.addEventListener("error", show, { once: true });
			setTimeout(show, 3000);
		}
	}

	function close(action) {
		if (!isOpen) return;
		isOpen = false;
		setCookie(subscribed ? SUBSCRIBED_DAYS : parseInt(data.days, 10) || 7);
		track(action || "close");
		popup.classList.remove("is-open");
		document.documentElement.classList.remove("gb-popup-open");
		document.removeEventListener("keydown", onKeydown);
		setTimeout(() => (popup.hidden = true), 300);
		popup.dispatchEvent(new CustomEvent("gb-popup:close"));
		if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus({ preventScroll: true });
	}

	function markSubscribed() {
		if (subscribed) return;
		subscribed = true;
		setCookie(SUBSCRIBED_DAYS);
		track("subscribe");
	}

	window.gbPopup = { close: () => close("close"), subscribed: markSubscribed };

	popup.querySelectorAll("[data-gb-popup-close]").forEach((el) => {
		el.addEventListener("click", () => close("close"));
	});

	// Click sul pulsante: memorizzo la chiusura e lascio proseguire la navigazione
	const button = popup.querySelector("[data-gb-popup-button]");
	if (button) {
		button.addEventListener("click", () => close("click"));
	}

	// Contact Form 7: iscrizione riuscita. Il popup resta aperto per il messaggio di conferma.
	popup.addEventListener("wpcf7mailsent", markSubscribed);

	if (!preview) {
		if (start && now < start) return;
		if (end && now > end) return;
		if (hasCookie()) return;
	}

	setTimeout(open, (parseInt(data.delay, 10) || 0) * 1000);
})();
