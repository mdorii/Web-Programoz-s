// shared-nav.js  – injects navigation and footer into every page
(function () {
  const pages = [
    { href: 'index.html',      label: '🏠 Főoldal' },
    { href: 'javascript.html', label: '⚙️ JavaScript' },
    { href: 'react.html',      label: '⚛️ React' },
    { href: 'spa.html',        label: '📱 SPA' },
    { href: 'fetchapi.html',   label: '🌐 Fetch API' },
    { href: 'axios.html',      label: '🔄 Axios' },
    { href: 'oojs.html',       label: '🎨 OO JS' },
  ];

  const current = location.pathname.split('/').pop() || 'index.html';

  // NAV
  const nav = document.getElementById('main-nav');
  if (nav) {
    nav.innerHTML = '<ul>' + pages.map(p =>
      `<li><a href="${p.href}"${p.href === current ? ' class="active"' : ''}>${p.label}</a></li>`
    ).join('') + '</ul>';
  }

  // FOOTER
  const footer = document.getElementById('main-footer');
  if (footer) {
    footer.innerHTML = 'Készítette: <span>Madarász Dóra</span> | Neptun: <span>TFO727</span> &nbsp;|&nbsp; <span>Mészáros Márton Bence</span> | Neptun: <span>KUS0K8</span>';
  }
})();
