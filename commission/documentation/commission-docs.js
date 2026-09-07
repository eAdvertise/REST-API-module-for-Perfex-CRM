(() => {
  const search = document.querySelector('#docs-search');
  const nav = document.querySelector('#docs-nav');
  const toggle = document.querySelector('#nav-toggle');
  const filter = () => {
    const term = search.value.trim().toLowerCase();
    nav.querySelectorAll('li').forEach((item) => {
      item.hidden = term !== '' && !item.textContent.toLowerCase().includes(term);
    });
  };
  search.addEventListener('input', filter);
  document.querySelector('#clear-search').addEventListener('click', () => { search.value = ''; filter(); search.focus(); });
  document.querySelector('#focus-search').addEventListener('click', () => search.focus());
  document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); search.focus(); }
    if (event.key === 'Escape') { search.value = ''; filter(); document.body.classList.remove('nav-open'); }
  });
  toggle.addEventListener('click', () => {
    const open = document.body.classList.toggle('nav-open');
    toggle.setAttribute('aria-expanded', String(open));
  });
  nav.addEventListener('click', (event) => { if (event.target.closest('a')) document.body.classList.remove('nav-open'); });
  const links = [...nav.querySelectorAll('a[href^="#"]')];
  const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
  const observer = new IntersectionObserver((entries) => {
    entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
      links.forEach((link) => link.classList.toggle('active', link.getAttribute('href') === `#${entry.target.id}`));
    });
  }, { rootMargin: '-20% 0px -70% 0px' });
  sections.forEach((section) => observer.observe(section));
})();
