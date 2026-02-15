document.addEventListener('DOMContentLoaded', function() {
    // Navegação Ativa na Sidebar
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section');
    
    function setActiveNav() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= sectionTop - 150) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').slice(1) === current) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', setActiveNav);

    // Abas de Código
    const codeTabs = document.querySelectorAll('.code-tab');

    function switchLanguage(event) {
        const language = event.target.getAttribute('data-language');
        const methodContainer = event.target.closest('.api-method');
        
        // Remove active class from all tabs in this method
        methodContainer.querySelectorAll('.code-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Add active class to clicked tab
        event.target.classList.add('active');
        
        // Hide all code blocks and show the selected one
        methodContainer.querySelectorAll('.code-examples .code-block').forEach(block => {
            block.classList.remove('active');
            if (block.getAttribute('data-language') === language) {
                block.classList.add('active');
            }
        });
    }

    codeTabs.forEach(tab => {
        tab.addEventListener('click', switchLanguage);
    });

    // Inicializar primeira aba de código ativa em cada método
    document.querySelectorAll('.api-method').forEach(method => {
        const firstTab = method.querySelector('.code-tab');
        if (firstTab) firstTab.classList.add('active');
        
        const firstCodeBlock = method.querySelector('.code-examples .code-block');
        if (firstCodeBlock) firstCodeBlock.classList.add('active');
    });

    // Copiar Código
    const copyButtons = document.querySelectorAll('.copy-button');

    function copyCode(event) {
        const button = event.target.closest('.copy-button');
        const codeBlock = button.closest('.code-header').nextElementSibling;
        const code = codeBlock.textContent;

        navigator.clipboard.writeText(code.trim()).then(() => {
            // Feedback visual
            const originalText = button.textContent;
            button.textContent = 'Copiado!';
            button.style.background = 'rgba(11,107,225,0.2)';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = 'transparent';
            }, 2000);
        }).catch(err => {
            console.error('Erro ao copiar:', err);
            button.textContent = 'Erro ao copiar';
            button.style.background = 'rgba(255,71,87,0.2)';
        });
    }

    copyButtons.forEach(button => {
        button.addEventListener('click', copyCode);
    });

    // Menu Mobile
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.setAttribute('aria-label', 'Toggle menu');
    menuToggle.innerHTML = `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    `;

    document.querySelector('.main-content').insertBefore(menuToggle, document.querySelector('.main-content').firstChild);

    // Criar e adicionar overlay
    const overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);

    const sidebar = document.querySelector('.sidebar');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    });

    // Fechar menu ao clicar no overlay
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    });

    // Fechar menu com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Animação de entrada das seções
    const observerOptions = {
        threshold: 0,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Remover classe que esconde a seção
                entry.target.classList.remove('section-hidden');
                // Adicionar classe de fade in
                requestAnimationFrame(() => {
                    entry.target.classList.add('fade-in');
                });
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    sections.forEach(section => {
        // Adicionar classe hidden inicialmente
        section.classList.add('section-hidden');
        observer.observe(section);
    });

    // Smooth Scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // Fecha o menu mobile se estiver aberto
                if (sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    });

    // Expandir/Colapsar Códigos Longos
    const longCodeBlocks = document.querySelectorAll('pre code');
    const maxHeight = 300;

    longCodeBlocks.forEach(block => {
        const wrapper = document.createElement('div');
        wrapper.className = 'code-wrapper';
        block.parentNode.insertBefore(wrapper, block);
        wrapper.appendChild(block);

        // Verificar altura após renderização
        requestAnimationFrame(() => {
            if (block.clientHeight > maxHeight) {
                wrapper.classList.add('collapsed');
                
                const expandButton = document.createElement('button');
                expandButton.className = 'expand-code-button';
                expandButton.textContent = 'Mostrar mais';
                wrapper.appendChild(expandButton);

                expandButton.addEventListener('click', () => {
                    wrapper.classList.toggle('collapsed');
                    expandButton.textContent = wrapper.classList.contains('collapsed') 
                        ? 'Mostrar mais' 
                        : 'Mostrar menos';
                });
            }
        });
    });

    // Busca na Documentação
    const searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.placeholder = 'Buscar na documentação...';
    searchInput.className = 'doc-search';
    document.querySelector('.sidebar-logo').after(searchInput);

    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = e.target.value.toLowerCase();
            
            sections.forEach(section => {
                const content = section.textContent.toLowerCase();
                if (content.includes(searchTerm) || searchTerm === '') {
                    section.style.display = '';
                } else {
                    section.style.display = 'none';
                }
            });
        }, 300);
    });

    // Syntax highlighting com Prism
    Prism.highlightAll();
});

// Adicionar estilos dinâmicos
const style = document.createElement('style');
style.textContent = `
    .menu-toggle {
        display: none;
        background: transparent;
        border: none;
        color: var(--primary-color);
        cursor: pointer;
        padding: 0.5rem;
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1000;
    }

    .menu-toggle svg {
        width: 24px;
        height: 24px;
    }

    @media (max-width: 999px) {
        .menu-toggle {
            display: block;
        }
    }

    .code-wrapper {
        position: relative;
    }

    .code-wrapper.collapsed pre code {
        max-height: 300px;
        overflow: hidden;
    }

    .expand-code-button {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, var(--bg-card));
        padding: 1rem;
        text-align: center;
        color: var(--primary-color);
        cursor: pointer;
        border: none;
    }

    .doc-search {
        width: 100%;
        padding: 0.75rem;
        margin-bottom: 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-primary);
    }

    .doc-search::placeholder {
        color: var(--text-secondary);
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }

    .overlay.active {
        display: block;
    }

    .section-hidden {
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: none;
    }

    .fade-in {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .section-hidden,
        .fade-in {
            transition: none;
            transform: none;
        }
    }

    @media (max-width: 768px) {
        .section-hidden,
        .fade-in {
            transform: none;
            transition: opacity 0.4s ease;
        }
    }
`;

document.head.appendChild(style);