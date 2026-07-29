/**
 * Efecto "maquina de escribir" para las frases promocionales del inicio.
 * Lee las frases del atributo data-phrases del contenedor .home-typing.
 */
(function () {
	var box = document.querySelector('.home-typing');
	if (!box) { return; }

	var el = box.querySelector('.home-typing-text');
	var phrases;
	try {
		phrases = JSON.parse(box.getAttribute('data-phrases') || '[]');
	} catch (e) {
		phrases = [];
	}
	if (!el || !phrases.length) { return; }

	var pi = 0;      // indice de frase
	var ci = 0;      // indice de caracter
	var deleting = false;

	var TYPE_SPEED = 80;    // ms al escribir cada letra
	var DELETE_SPEED = 40;  // ms al borrar cada letra
	var HOLD = 1800;        // ms que se mantiene la frase completa
	var PAUSE = 400;        // ms antes de escribir la siguiente

	function tick() {
		var full = phrases[pi];

		if (!deleting) {
			ci++;
			el.textContent = full.substring(0, ci);
			if (ci >= full.length) {
				deleting = true;
				setTimeout(tick, HOLD);
				return;
			}
			setTimeout(tick, TYPE_SPEED);
		} else {
			ci--;
			el.textContent = full.substring(0, ci);
			if (ci <= 0) {
				deleting = false;
				pi = (pi + 1) % phrases.length;
				setTimeout(tick, PAUSE);
				return;
			}
			setTimeout(tick, DELETE_SPEED);
		}
	}

	tick();
})();
