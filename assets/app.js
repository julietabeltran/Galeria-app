const menuBtn = document.querySelector('#menuBtn');
const nav = document.querySelector('#nav');
if (menuBtn && nav) menuBtn.addEventListener('click', () => nav.classList.toggle('open'));

const input = document.querySelector('#imageInput');
const preview = document.querySelector('#preview');
if (input && preview) {
  input.addEventListener('change', () => {
    const file = input.files?.[0];
    if (!file) return;
    preview.src = URL.createObjectURL(file);
    preview.hidden = false;
  });
}

