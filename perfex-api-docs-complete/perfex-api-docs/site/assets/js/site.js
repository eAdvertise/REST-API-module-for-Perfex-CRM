(() => {
  'use strict';

  const doc = document;
  const root = doc.documentElement;
  const body = doc.body;
  const scriptUrl = doc.currentScript ? doc.currentScript.src : '/assets/js/site.js';
  const searchIndexUrl = new URL('../search-index.json', scriptUrl).href;
  const toast = doc.querySelector('[data-toast]');
  let toastTimer = 0;

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 1800);
  }

  async function copyText(value) {
    try {
      await navigator.clipboard.writeText(value);
      showToast('Copied to clipboard');
    } catch (_) {
      const area = doc.createElement('textarea');
      area.value = value;
      area.style.position = 'fixed';
      area.style.opacity = '0';
      doc.body.appendChild(area);
      area.select();
      doc.execCommand('copy');
      area.remove();
      showToast('Copied to clipboard');
    }
  }

  doc.addEventListener('click', (event) => {
    const direct = event.target.closest('[data-copy]');
    if (direct) {
      copyText(direct.dataset.copy || '');
      return;
    }
    const targetButton = event.target.closest('[data-copy-target]');
    if (targetButton) {
      const code = targetButton.parentElement && targetButton.parentElement.querySelector('code');
      if (code) copyText(code.textContent.trim());
    }
  });

  // Responsive navigation.
  const navButton = doc.querySelector('[data-nav-toggle]');
  const navClosers = doc.querySelectorAll('[data-nav-close]');
  function setNav(open) {
    body.classList.toggle('nav-open', open);
    if (navButton) navButton.setAttribute('aria-expanded', String(open));
  }
  if (navButton) navButton.addEventListener('click', () => setNav(!body.classList.contains('nav-open')));
  navClosers.forEach((element) => element.addEventListener('click', () => setNav(false)));
  doc.querySelectorAll('.sidebar a').forEach((link) => link.addEventListener('click', () => setNav(false)));
  window.addEventListener('resize', () => {
    if (window.innerWidth > 780) setNav(false);
  });

  // Theme switcher.
  const themeToggle = doc.querySelector('[data-theme-toggle]');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const current = root.dataset.theme;
      let next;
      if (current === 'auto') {
        next = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'light' : 'dark';
      } else {
        next = current === 'dark' ? 'light' : 'dark';
      }
      root.dataset.theme = next;
      localStorage.setItem('docs-theme', next);
      showToast(`${next[0].toUpperCase()}${next.slice(1)} theme enabled`);
    });
  }

  // Reading progress.
  const progress = doc.querySelector('.reading-progress span');
  function updateProgress() {
    if (!progress) return;
    const height = doc.documentElement.scrollHeight - window.innerHeight;
    const ratio = height > 0 ? Math.min(1, Math.max(0, window.scrollY / height)) : 0;
    progress.style.width = `${ratio * 100}%`;
  }
  window.addEventListener('scroll', updateProgress, { passive: true });
  window.addEventListener('resize', updateProgress);
  updateProgress();

  // Reference filtering and group collapse.
  const filterInput = doc.querySelector('[data-reference-filter]');
  const referenceGroups = Array.from(doc.querySelectorAll('[data-reference-group]'));
  const referenceEmpty = doc.querySelector('[data-reference-empty]');
  if (filterInput && referenceGroups.length) {
    const filter = () => {
      const query = filterInput.value.toLowerCase().trim();
      let visibleGroups = 0;
      referenceGroups.forEach((group) => {
        let visibleRows = 0;
        group.querySelectorAll('[data-endpoint-row]').forEach((row) => {
          const show = !query || (row.dataset.searchable || '').includes(query);
          row.hidden = !show;
          if (show) visibleRows += 1;
        });
        const groupNameMatches = (group.dataset.searchable || '').startsWith(query);
        const showGroup = !query || visibleRows > 0 || groupNameMatches;
        group.hidden = !showGroup;
        if (showGroup) {
          visibleGroups += 1;
          if (query) group.classList.remove('is-collapsed');
        }
      });
      if (referenceEmpty) referenceEmpty.hidden = visibleGroups !== 0;
    };
    filterInput.addEventListener('input', filter);
  }
  doc.querySelectorAll('[data-collapse-group]').forEach((button) => {
    button.addEventListener('click', () => {
      const group = button.closest('[data-reference-group]');
      if (!group) return;
      const collapsed = group.classList.toggle('is-collapsed');
      button.setAttribute('aria-expanded', String(!collapsed));
    });
  });

  // Create a local table of contents for Markdown guides.
  const prose = doc.querySelector('[data-prose]');
  const toc = doc.querySelector('[data-toc]');
  if (prose && toc) {
    const headings = Array.from(prose.querySelectorAll('h2, h3'));
    const used = new Set();
    const slug = (text) => {
      let value = text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'section';
      const base = value;
      let counter = 2;
      while (used.has(value)) value = `${base}-${counter++}`;
      used.add(value);
      return value;
    };
    headings.forEach((heading) => {
      if (!heading.id) heading.id = slug(heading.textContent || 'section');
      const link = doc.createElement('a');
      link.href = `#${heading.id}`;
      link.dataset.level = heading.tagName === 'H3' ? '3' : '2';
      link.textContent = heading.textContent;
      toc.appendChild(link);
    });

    if ('IntersectionObserver' in window && headings.length) {
      const links = Array.from(toc.querySelectorAll('a'));
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          links.forEach((link) => link.classList.toggle('is-active', link.hash === `#${entry.target.id}`));
        });
      }, { rootMargin: '-15% 0px -74% 0px', threshold: 0 });
      headings.forEach((heading) => observer.observe(heading));
    }
  }

  // Search dialog and index.
  const dialog = doc.querySelector('[data-search-dialog]');
  const searchInput = doc.querySelector('[data-search-input]');
  const searchResults = doc.querySelector('[data-search-results]');
  const searchStatus = doc.querySelector('[data-search-status]');
  const openButtons = doc.querySelectorAll('[data-search-open]');
  const closeButton = doc.querySelector('[data-search-close]');
  let searchIndex = null;
  let selectedIndex = -1;

  function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
  }

  async function loadSearchIndex() {
    if (searchIndex) return searchIndex;
    try {
      const response = await fetch(searchIndexUrl, { credentials: 'same-origin' });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      searchIndex = await response.json();
    } catch (error) {
      searchIndex = [];
      if (searchStatus) searchStatus.textContent = 'Search index could not be loaded';
      console.error('Documentation search index:', error);
    }
    return searchIndex;
  }

  function scoreEntry(entry, tokens) {
    const title = (entry.title || '').toLowerCase();
    const subtitle = (entry.subtitle || '').toLowerCase();
    const text = (entry.text || '').toLowerCase();
    let score = 0;
    for (const token of tokens) {
      if (title === token) score += 80;
      else if (title.startsWith(token)) score += 42;
      else if (title.includes(token)) score += 28;
      if (subtitle.includes(token)) score += 16;
      if (text.includes(token)) score += 4;
      if (!(title.includes(token) || subtitle.includes(token) || text.includes(token))) return -1;
    }
    if (entry.type === 'operation') score += 2;
    return score;
  }

  function renderSearch(entries, query) {
    if (!searchResults) return;
    selectedIndex = -1;
    if (!query.trim()) {
      searchResults.innerHTML = '<div class="search-empty"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg><strong>Search the documentation</strong><p>Find guides, endpoint names, methods and paths.</p></div>';
      if (searchStatus) searchStatus.textContent = `${entries.length} indexed pages`;
      return;
    }
    const tokens = query.toLowerCase().trim().split(/\s+/).filter(Boolean);
    const matches = entries
      .map((entry) => ({ entry, score: scoreEntry(entry, tokens) }))
      .filter((value) => value.score >= 0)
      .sort((a, b) => b.score - a.score || a.entry.title.localeCompare(b.entry.title))
      .slice(0, 40)
      .map((value) => value.entry);
    if (searchStatus) searchStatus.textContent = `${matches.length} result${matches.length === 1 ? '' : 's'}`;
    if (!matches.length) {
      searchResults.innerHTML = '<div class="search-empty"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><strong>No results found</strong><p>Try a resource name, HTTP method or path.</p></div>';
      return;
    }
    searchResults.innerHTML = matches.map((entry) => {
      const operation = entry.type === 'operation';
      const icon = operation
        ? '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m8 9-4 3 4 3m8-6 4 3-4 3M14 5l-4 14"/></svg>'
        : '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>';
      return `<a class="search-result" href="${escapeHtml(entry.url)}"><span class="search-result__icon">${icon}</span><span class="search-result__body"><strong>${escapeHtml(entry.title)}</strong><span>${escapeHtml(entry.subtitle || '')}</span></span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5"/></svg></a>`;
    }).join('');
  }

  async function openSearch() {
    if (!dialog || !searchInput) return;
    if (typeof dialog.showModal === 'function' && !dialog.open) dialog.showModal();
    const entries = await loadSearchIndex();
    renderSearch(entries, searchInput.value);
    window.setTimeout(() => searchInput.focus(), 10);
  }
  function closeSearch() {
    if (dialog && dialog.open) dialog.close();
  }
  openButtons.forEach((button) => button.addEventListener('click', openSearch));
  if (closeButton) closeButton.addEventListener('click', closeSearch);
  if (dialog) {
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) closeSearch();
    });
  }
  if (searchInput) {
    searchInput.addEventListener('input', async () => renderSearch(await loadSearchIndex(), searchInput.value));
    searchInput.addEventListener('keydown', (event) => {
      const results = Array.from(searchResults ? searchResults.querySelectorAll('.search-result') : []);
      if (!results.length) return;
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        selectedIndex += event.key === 'ArrowDown' ? 1 : -1;
        if (selectedIndex < 0) selectedIndex = results.length - 1;
        if (selectedIndex >= results.length) selectedIndex = 0;
        results.forEach((result, index) => result.classList.toggle('is-selected', index === selectedIndex));
        results[selectedIndex].scrollIntoView({ block: 'nearest' });
      } else if (event.key === 'Enter' && selectedIndex >= 0) {
        event.preventDefault();
        results[selectedIndex].click();
      }
    });
  }
  doc.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      openSearch();
    } else if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(doc.activeElement.tagName)) {
      event.preventDefault();
      openSearch();
    }
  });

  // Mark external links produced from Markdown.
  doc.querySelectorAll('.prose a[href^="http"]').forEach((link) => {
    try {
      if (new URL(link.href).origin !== location.origin) {
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
      }
    } catch (_) { /* ignore malformed URLs */ }
  });
})();
