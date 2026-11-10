(() => {
  const base = new URL('../', document.currentScript.src);
  const menu = document.querySelector('.menu-toggle');
  const nav = document.querySelector('#main-nav');
  const closeMenu = () => {
    nav.classList.remove('is-open');
    menu.setAttribute('aria-expanded', 'false');
  };
  menu.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
  });
  nav.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu.getAttribute('aria-expanded') === 'true') {
      closeMenu();
      menu.focus();
    }
  });
  matchMedia('(min-width: 981px)').addEventListener('change', event => { if (event.matches) closeMenu(); });

  document.querySelectorAll('.solution-tile').forEach(card => {
    const front = card.querySelector('.flip-front');
    const back = card.querySelector('.flip-back');
    const contactLink = back.querySelector('[data-brand]');
    const canHover = matchMedia('(hover: hover) and (pointer: fine)');
    const flip = (open, focus = true) => {
      card.classList.toggle('is-flipped', open);
      front.setAttribute('aria-expanded', String(open));
      front.inert = open;
      back.inert = !open;
      if (focus) (open ? contactLink : front).focus({preventScroll:true});
    };
    front.addEventListener('click', () => flip(true));
    card.addEventListener('pointerenter', () => { if (canHover.matches) flip(true, false); });
    card.addEventListener('pointerleave', () => { if (canHover.matches) flip(false, card.contains(document.activeElement)); });
    card.addEventListener('focusout', () => queueMicrotask(() => {
      if (!card.contains(document.activeElement) && !card.matches(':hover')) flip(false, false);
    }));
    back.addEventListener('click', event => { if (!event.target.closest('a,button')) flip(false); });
    card.addEventListener('keydown', event => { if (event.key === 'Escape') flip(false); });
    card.querySelector('[data-brand]').addEventListener('click', () => {
      flip(false, false);
      document.querySelector('#message').focus({preventScroll:true});
    });
  });

  let previousSelection = '';
  document.querySelectorAll('[data-brand]').forEach(link => link.addEventListener('click', () => {
    const field = document.querySelector('#message');
    const text = `Deseo información sobre ${link.dataset.brand}.`;
    const existing = field.value.trim();
    field.value = previousSelection && existing.includes(previousSelection)
      ? existing.replace(previousSelection, text)
      : existing ? `${existing}\n\n${text}` : text;
    previousSelection = text;
    const feedback = document.querySelector('#contact-selection');
    feedback.textContent = `Solución seleccionada: ${link.dataset.brand}`;
    feedback.hidden = false;
  }));
  const form = document.querySelector('.contact-form');
  if (form) form.addEventListener('submit', () => {
    const button = form.querySelector('.submit');
    button.disabled = true;
    button.textContent = 'Enviando consulta…';
    form.setAttribute('aria-busy', 'true');
  });
  window.addEventListener('pageshow', () => {
    if (form && form.querySelector('.submit').disabled) {
      form.querySelector('.submit').disabled = false;
      form.querySelector('.submit').textContent = 'Enviar consulta';
      form.removeAttribute('aria-busy');
    }
  });
  document.querySelectorAll('.mauticform-required.mauticform-checkboxgrp').forEach(group => {
    const boxes = [...group.querySelectorAll('input[type="checkbox"]')];
    const validate = () => {
      if (boxes.length) boxes[0].setCustomValidity(boxes.some(box => box.checked) ? '' : 'Selecciona al menos una opción.');
    };
    group.addEventListener('change', validate);
    validate();
  });
  document.querySelectorAll('form[data-mautic-form]').forEach(campaign => {
    const conditionalRows = [...campaign.querySelectorAll('[data-mautic-form-show-on]')];
    const syncConditions = () => conditionalRows.forEach(row => {
      const condition = row.dataset.mauticFormShowOn;
      const separator = condition.indexOf(':');
      if (separator < 0) return;
      const field = condition.slice(0, separator);
      const expected = condition.slice(separator + 1);
      const controls = [...campaign.elements].filter(control => control.name === `mauticform[${field}]` || control.name === `mauticform[${field}][]`);
      const active = controls.some(control => control.value === expected && (!['checkbox', 'radio'].includes(control.type) || control.checked));
      row.hidden = !active;
      row.querySelectorAll('input, select, textarea').forEach(control => { control.disabled = !active; });
    });
    campaign.addEventListener('change', syncConditions);
    syncConditions();
  });

  const map = document.querySelector('#coverage-map');
  if (map) {
    const unavailable = () => {
      map.classList.add('map-unavailable');
      map.textContent = 'El mapa no está disponible. Escríbenos para consultar la atención en tu país.';
    };
      map.textContent = 'Cargando mapa…';
      const root = new URL('assets/vendor/jsvectormap/', base);
      const css = document.createElement('link');
      css.rel = 'stylesheet';
      css.href = new URL('jsvectormap.min.css', root);
      const cssReady = new Promise((resolve, reject) => { css.onload = resolve; css.onerror = reject; });
      document.head.append(css);
      const script = document.createElement('script');
      script.src = new URL('jsvectormap.min.js', root);
      script.onerror = unavailable;
      script.onload = async () => {
        try {
          await cssReady;
          await new Promise((resolve, reject) => {
            const geography = document.createElement('script');
            geography.src = new URL('america.js', root);
            geography.onload = resolve;
            geography.onerror = reject;
            document.head.append(geography);
          });
          const response = await fetch(new URL('assets/map-markers.json', base));
          if (!response.ok) throw new Error('map-data');
          const markers = await response.json();
          map.textContent = '';
          new jsVectorMap({
            selector: '#coverage-map', map: 'america', backgroundColor: '#f5f5f5',
            zoomOnScroll: false, zoomButtons: true, zoomMin: 1, zoomMax: 8,
            zoomAnimate: !matchMedia('(prefers-reduced-motion: reduce)').matches,
            regionStyle: {
              initial: {fill: '#d4d4d4', stroke: '#ffffff', strokeWidth: 0.8},
              hover: {fill: '#b4b4b4', cursor: 'default'}
            },
            markers: markers.map(point => ({name: point.name.replace(/:$/, ''), coords: [point.lat, point.lng]})),
            markerStyle: {
              initial: {r: 4, fill: '#111111', stroke: '#ffffff', strokeWidth: 1.5},
              hover: {fill: '#555555', stroke: '#111111'}
            },
            onRegionTooltipShow: event => event.preventDefault(),
            onMarkerTooltipShow: (event, tooltip, index) => tooltip.text(markers[index].name.replace(/:$/, ''))
          });
          map.querySelectorAll('.jvm-zoom-btn').forEach(button => {
            button.setAttribute('role', 'button');
            button.tabIndex = 0;
            button.setAttribute('aria-label', button.classList.contains('jvm-zoomin') ? 'Acercar mapa' : 'Alejar mapa');
            button.addEventListener('keydown', event => {
              if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); button.click(); }
            });
          });
        } catch { unavailable(); }
      };
      document.head.append(script);
  }
})();
