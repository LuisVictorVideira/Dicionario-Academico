document.addEventListener('DOMContentLoaded', function() {
    // Verifica o tema salvo ou preferência do sistema
    const savedTheme = localStorage.getItem('theme') || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    // Aplica o tema inicial
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Cria o botão
    const themeSwitcher = document.createElement('button');
    themeSwitcher.className = 'theme-switcher';
    themeSwitcher.title = 'Alternar tema claro/escuro';
    themeSwitcher.innerHTML = `
        <span class="theme-icon sun">☀️</span>
        <span class="theme-icon moon">🌙</span>
    `;
    
    // Adiciona o botão ao header
    const header = document.querySelector('header');
    if(header) {
        header.appendChild(themeSwitcher);
    } else {
        document.body.insertBefore(themeSwitcher, document.body.firstChild);
    }
    
    // Alterna o tema ao clicar
    themeSwitcher.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
});