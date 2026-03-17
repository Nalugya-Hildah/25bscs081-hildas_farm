// Hilda's Poultry Farm — main.js

// Navbar scroll effect
const navbar = document.getElementById('navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  });
}

// Mobile nav toggle
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    const isOpen = navLinks.classList.contains('open');
    navToggle.setAttribute('aria-expanded', isOpen);
  });
  // Close on outside click
  document.addEventListener('click', (e) => {
    if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
      navLinks.classList.remove('open');
    }
  });
}

// Scroll reveal
const reveals = document.querySelectorAll('.reveal');
if (reveals.length) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
  reveals.forEach(el => observer.observe(el));
}

// Password toggle
document.querySelectorAll('.password-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.previousElementSibling;
    if (input) {
      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      btn.textContent = isPass ? '🙈' : '👁️';
    }
  });
});

// Smooth counter animation for stat numbers
function animateCounter(el) {
  const target = parseInt(el.textContent.replace(/\D/g, ''), 10);
  if (!target || isNaN(target)) return;
  const suffix = el.textContent.replace(/[\d,]/g, '');
  let count = 0;
  const step = Math.max(1, Math.floor(target / 60));
  const timer = setInterval(() => {
    count = Math.min(count + step, target);
    el.textContent = count.toLocaleString() + suffix;
    if (count >= target) clearInterval(timer);
  }, 25);
}

const counterEls = document.querySelectorAll('.stat-number, .num');
if (counterEls.length) {
  const cObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        cObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  counterEls.forEach(el => cObserver.observe(el));
}

// Flash message auto-dismiss
const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
  setTimeout(() => {
    alert.style.transition = 'opacity .5s';
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 500);
  }, 4000);
});

// Contact form simple validation
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const btn = contactForm.querySelector('[type=submit]');
    btn.textContent = '✅ Message Sent!';
    btn.disabled = true;
    setTimeout(() => { contactForm.reset(); btn.textContent = 'Send Message'; btn.disabled = false; }, 3000);
  });
}
