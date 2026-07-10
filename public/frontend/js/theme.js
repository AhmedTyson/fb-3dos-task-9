const themeBtn = document.getElementById('theme-toggle');
const themeIcon = themeBtn.querySelector('i');
const bodyClass = document.body.classList;

themeBtn.addEventListener('click', () => {

    bodyClass.toggle('dark-theme');

    if (bodyClass.contains('dark-theme')) {
        themeIcon.className = 'fa-solid fa-sun';
    } else {
        themeIcon.className = 'fa-solid fa-moon';
    }
});