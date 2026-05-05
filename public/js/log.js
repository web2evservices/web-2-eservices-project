const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

// Apply saved state on page load
if (localStorage.getItem('authMode') === 'register') {
    container.classList.add("active");
} else {
    container.classList.remove("active");
}

// When clicking Register
registerBtn.addEventListener('click', () => {
    container.classList.add("active");
    localStorage.setItem('authMode', 'register');
});

// When clicking Login
loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
    localStorage.setItem('authMode', 'login');
});