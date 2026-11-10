(() => {
  const scriptEl = document.currentScript || document.querySelector('script[src*="assets/site.js"]');
  const base = new URL('../', (scriptEl && scriptEl.src) || window.location.href);
  const asset = (path) => new URL(path, base).href;
  const menu = document.querySelector('.menu-toggle');
  const nav = document.querySelector('#main-nav');
  const header = document.querySelector('.site-header');
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

  if (header) {
    const syncHeader = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });
  }

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
      map.textContent = 'El mapa no está disponible en este momento.';
    };

    const loadLeaflet = () => new Promise((resolve, reject) => {
      if (window.L) {
        resolve(window.L);
        return;
      }
      if (!document.querySelector('link[data-pontelog-leaflet]')) {
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = asset('assets/vendor/leaflet/leaflet.css');
        css.setAttribute('data-pontelog-leaflet', '1');
        document.head.append(css);
      }
      const script = document.createElement('script');
      script.src = asset('assets/vendor/leaflet/leaflet.js');
      script.onload = () => (window.L ? resolve(window.L) : reject(new Error('leaflet-missing')));
      script.onerror = () => reject(new Error('leaflet-load'));
      document.head.append(script);
    });

    const addMarkers = (L, coverage, markers) => {
      markers.forEach(point => {
        const label = String(point.name || '').replace(/:\s*$/, '').trim();
        L.circleMarker([point.lat, point.lng], {
          radius: 5,
          color: '#111111',
          weight: 0,
          fillColor: '#111111',
          fillOpacity: 1,
          opacity: 1,
        }).bindTooltip(label, {
          direction: 'top',
          opacity: 1,
          sticky: true,
          offset: [0, -8],
        }).addTo(coverage);
      });
    };

    const renderMap = async () => {
      map.textContent = 'Cargando mapa…';
      const L = await loadLeaflet();
      const markersRes = await fetch(asset('assets/map-markers.json'), { cache: 'no-cache' });
      if (!markersRes.ok) throw new Error('markers');
      const markers = await markersRes.json();

      let americas = null;
      try {
        const geoRes = await fetch(asset('assets/americas.json'), { cache: 'no-cache' });
        if (geoRes.ok) americas = await geoRes.json();
      } catch (_) {
        americas = null;
      }

      map.textContent = '';
      map.classList.remove('map-unavailable');

      const coverage = L.map(map, {
        scrollWheelZoom: false,
        attributionControl: false,
        maxBounds: [[-60, -140], [75, -20]],
        maxBoundsViscosity: 0.85,
      }).setView([5, -78], 3);

      let countries = null;
      if (americas && americas.features) {
        const baseStyle = {
          color: '#ffffff',
          weight: 1.1,
          opacity: 1,
          fillColor: '#d7d7d7',
          fillOpacity: 1,
        };
        const hoverStyle = {
          color: '#ffffff',
          weight: 1.4,
          fillColor: '#bcbcbc',
          fillOpacity: 1,
        };
        const nameMap = {
          Canada: 'CANADÁ',
          'United States of America': 'ESTADOS UNIDOS',
          Mexico: 'MÉXICO',
          Guatemala: 'GUATEMALA',
          Honduras: 'HONDURAS',
          Nicaragua: 'NICARAGUA',
          'Dominican Rep.': 'REPÚBLICA DOMINICANA',
          Colombia: 'COLOMBIA',
          Venezuela: 'VENEZUELA',
          Ecuador: 'ECUADOR',
          Peru: 'PERÚ',
          Brazil: 'BRASIL',
          Bolivia: 'BOLIVIA',
          Paraguay: 'PARAGUAY',
          Chile: 'CHILE',
          Argentina: 'ARGENTINA',
          Uruguay: 'URUGUAY',
        };
        countries = L.geoJSON(americas, {
          style: baseStyle,
          onEachFeature: (feature, layer) => {
            const raw = (feature.properties && feature.properties.name) || '';
            const label = nameMap[raw] || String(raw).toUpperCase();
            layer.bindTooltip(label, {
              sticky: true,
              direction: 'top',
              opacity: 1,
              offset: [0, -6],
            });
            layer.on({
              mouseover: () => layer.setStyle(hoverStyle),
              mouseout: () => countries.resetStyle(layer),
            });
          },
        }).addTo(coverage);
      }

      addMarkers(L, coverage, markers);

      if (countries && countries.getBounds().isValid()) {
        coverage.fitBounds(countries.getBounds().pad(0.04), { animate: false });
      } else if (markers.length) {
        coverage.fitBounds(L.latLngBounds(markers.map(p => [p.lat, p.lng])).pad(0.2), { animate: false });
      }

      requestAnimationFrame(() => coverage.invalidateSize());
      setTimeout(() => coverage.invalidateSize(), 250);
    };

    renderMap().catch(() => unavailable());
  }

  const banner = document.querySelector('#cookie-banner');
  if (banner) {
    const key = 'pontelog_consent';
    const readConsent = () => {
      try {
        const raw = localStorage.getItem(key) || document.cookie.split('; ').find(row => row.startsWith(key + '='))?.split('=').slice(1).join('=');
        return raw ? JSON.parse(decodeURIComponent(raw)) : null;
      } catch {
        return null;
      }
    };
    const writeAck = () => {
      const value = { noticed: true, ts: Date.now() };
      const encoded = encodeURIComponent(JSON.stringify(value));
      localStorage.setItem(key, JSON.stringify(value));
      document.cookie = `${key}=${encoded}; path=/; max-age=${60 * 60 * 24 * 180}; SameSite=Lax`;
      banner.hidden = true;
    };
    if (!readConsent()) banner.hidden = false;
    banner.querySelector('[data-cookie-ack]')?.addEventListener('click', writeAck);
  }
})();
