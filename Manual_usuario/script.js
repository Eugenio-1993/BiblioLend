const input = document.querySelector('#search-input');
const sections = [...document.querySelectorAll('[data-searchable]')];
const links = [...document.querySelectorAll('.nav-link')];

input.addEventListener('input', () => {
  const query = input.value.trim().toLocaleLowerCase('es');
  sections.forEach((section) => {
    section.hidden = query && !section.textContent.toLocaleLowerCase('es').includes(query);
  });
});

document.addEventListener('keydown', (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    input.focus();
  }
});

const observer = new IntersectionObserver((entries) => {
  const current = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
  if (!current) return;
  links.forEach((link) => link.classList.toggle('active', link.getAttribute('href') === `#${current.target.id}`));
}, { rootMargin: '-18% 0px -68% 0px', threshold: [0, 0.2, 0.5] });

sections.forEach((section) => observer.observe(section));
