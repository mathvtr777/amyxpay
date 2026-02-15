<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="../home" class="header-logo">
            <img src="../img/URANOPAY-black.png" alt="logo" class="desktop-logo">
            <img src="../img/URANOPAY-black.png" alt="logo" class="toggle-dark">
            <img src="../img/URANOPAY-branca.png" alt="logo" class="desktop-dark">
            <img src="../img/URANOPAY-branca.png" alt="logo" class="toggle-logo">
        </a>
    </div>
    <!-- End::main-sidebar-header -->




    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">
                <!-- Start::slide__category -->
                <li class="slide__category"><span class="category-name">Main</span></li>
                <!-- End::slide__category -->





                <!-- Start::slide -->
                <li class="slide">
                    <a href="../home" class="side-menu__item">

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                            <path d="M216,115.54V208a8,8,0,0,1-8,8H160a8,8,0,0,1-8-8V160a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v48a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V115.54a8,8,0,0,1,2.62-5.92l80-75.54a8,8,0,0,1,10.77,0l80,75.54A8,8,0,0,1,216,115.54Z" opacity="0.2"></path>
                            <path d="M218.83,103.77l-80-75.48a1.14,1.14,0,0,1-.11-.11,16,16,0,0,0-21.53,0l-.11.11L37.17,103.77A16,16,0,0,0,32,115.55V208a16,16,0,0,0,16,16H96a16,16,0,0,0,16-16V160h32v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V115.55A16,16,0,0,0,218.83,103.77ZM208,208H160V160a16,16,0,0,0-16-16H112a16,16,0,0,0-16,16v48H48V115.55l.11-.1L128,40l79.9,75.43.11.1Z"></path>
                        </svg>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>
                <!-- End::slide -->





                <?php if ($status == 1): ?>






                    <!-- Start::slide -->
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M208,40V208H152V40Z" opacity="0.2"></path>
                                <path d="M224,200h-8V40a8,8,0,0,0-8-8H152a8,8,0,0,0-8,8V80H96a8,8,0,0,0-8,8v40H48a8,8,0,0,0-8,8v64H32a8,8,0,0,0,0,16H224a8,8,0,0,0,0-16ZM160,48h40V200H160ZM104,96h40V200H104ZM56,144H88v56H56Z"></path>
                            </svg>
                            <span class="side-menu__label">Relatorio</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)"></a>
                            </li>
                            <li class="slide">
                                <a href="../relatorio/pix-entrada.php" class="side-menu__item">Pix Entrada</a>
                            </li>

                            <li class="slide">
                                <a href="../relatorio/pix-saida.php" class="side-menu__item">Pix Saida</a>
                            </li>
                        </ul>
                    </li>
                    <!-- End::slide -->





                    <li class="slide">
                        <a href="../financeiro" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-68a28,28,0,0,1-28,28h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24A28,28,0,0,1,168,148Z"> </path>
                            </svg>
                            <span class="side-menu__label">Financeiro</span>

                        </a>
                    </li>




                    <li class="slide">
                        <a href="../checkout-build" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M216,48H40a8,8,0,0,0-8,8V216l32-16,32,16,32-16,32,16,32-16,32,16V56A8,8,0,0,0,216,48ZM112,160H64V96h48Z" opacity="0.2"></path>
                                <path d="M216,40H40A16,16,0,0,0,24,56V216a8,8,0,0,0,11.58,7.15L64,208.94l28.42,14.21a8,8,0,0,0,7.16,0L128,208.94l28.42,14.21a8,8,0,0,0,7.16,0L192,208.94l28.42,14.21A8,8,0,0,0,232,216V56A16,16,0,0,0,216,40Zm0,163.06-20.42-10.22a8,8,0,0,0-7.16,0L160,207.06l-28.42-14.22a8,8,0,0,0-7.16,0L96,207.06,67.58,192.84a8,8,0,0,0-7.16,0L40,203.06V56H216ZM136,112a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H144A8,8,0,0,1,136,112Zm0,32a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H144A8,8,0,0,1,136,144ZM64,168h48a8,8,0,0,0,8-8V96a8,8,0,0,0-8-8H64a8,8,0,0,0-8,8v64A8,8,0,0,0,64,168Zm8-64h32v48H72Z"></path>
                            </svg>
                            <span class="side-menu__label">Produtos</span>

                        </a>
                    </li>








                    <!-- Start::slide__category -->
                    <li class="slide__category"><span class="category-name">DESENVOLVEDOR</span></li>
                    <!-- End::slide__category -->



                    <li class="slide">
                        <a href="../documentacao" class="side-menu__item" data-bs-toggle="modal" data-bs-target="#modalDocumentation">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M224,56V200a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V56a8,8,0,0,1,8-8H216A8,8,0,0,1,224,56Z" opacity="0.2"></path>
                                <path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm0,160H40V56H216V200ZM184,96a8,8,0,0,1-8,8H80a8,8,0,0,1,0-16h96A8,8,0,0,1,184,96Zm0,32a8,8,0,0,1-8,8H80a8,8,0,0,1,0-16h96A8,8,0,0,1,184,128Zm0,32a8,8,0,0,1-8,8H80a8,8,0,0,1,0-16h96A8,8,0,0,1,184,160Z"></path>
                            </svg>
                            <span class="side-menu__label">Documentação</span>

                        </a>
                    </li>


                    <li class="slide">
                        <a href="../gateway" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M216,96V208a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V96a8,8,0,0,1,8-8H208A8,8,0,0,1,216,96Z" opacity="0.2"></path>
                                <path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-68-56a12,12,0,1,1-12-12A12,12,0,0,1,140,152Z"></path>
                            </svg>
                            <span class="side-menu__label">Chaves API <span class="badge bg-primary ms-2 shadow-primary">1</span></span>

                        </a>
                    </li>



                <?php endif; ?>






                <?php if ($permission > 2): ?>
                    <!-- Start::slide__category -->
                    <li class="slide__category"><span class="category-name">ADMINISTRADOR</span></li>
                    <!-- End::slide__category -->


                    <li class="slide">
                        <a href="../adm/" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M112,56v48a8,8,0,0,1-8,8H56a8,8,0,0,1-8-8V56a8,8,0,0,1,8-8h48A8,8,0,0,1,112,56Zm88-8H152a8,8,0,0,0-8,8v48a8,8,0,0,0,8,8h48a8,8,0,0,0,8-8V56A8,8,0,0,0,200,48Zm-96,96H56a8,8,0,0,0-8,8v48a8,8,0,0,0,8,8h48a8,8,0,0,0,8-8V152A8,8,0,0,0,104,144Zm96,0H152a8,8,0,0,0-8,8v48a8,8,0,0,0,8,8h48a8,8,0,0,0,8-8V152A8,8,0,0,0,200,144Z" opacity="0.2"></path>
                                <path d="M200,136H152a16,16,0,0,0-16,16v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V152A16,16,0,0,0,200,136Zm0,64H152V152h48v48ZM104,40H56A16,16,0,0,0,40,56v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V56A16,16,0,0,0,104,40Zm0,64H56V56h48v48Zm96-64H152a16,16,0,0,0-16,16v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V56A16,16,0,0,0,200,40Zm0,64H152V56h48v48Zm-96,32H56a16,16,0,0,0-16,16v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V152A16,16,0,0,0,104,136Zm0,64H56V152h48v48Z"></path>
                            </svg>
                            <span class="side-menu__label">Dash</span>

                        </a>
                    </li>



                    <li class="slide">
                        <a href="../adm/usuarios.php" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M117.25,157.92a60,60,0,1,0-66.5,0A95.83,95.83,0,0,0,3.53,195.63a8,8,0,1,0,13.4,8.74,80,80,0,0,1,134.14,0,8,8,0,0,0,13.4-8.74A95.83,95.83,0,0,0,117.25,157.92ZM40,108a44,44,0,1,1,44,44A44.05,44.05,0,0,1,40,108Zm210.14,98.7a8,8,0,0,1-11.07-2.33A79.83,79.83,0,0,0,172,168a8,8,0,0,1,0-16,44,44,0,1,0-16.34-84.87,8,8,0,1,1-5.94-14.85,60,60,0,0,1,55.53,105.64,95.83,95.83,0,0,1,47.22,37.71A8,8,0,0,1,250.14,206.7Z"> </path>
                            </svg>
                            <span class="side-menu__label">Usuarios</span>

                        </a>
                    </li>



                    <!-- Start::slide -->
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-68a28,28,0,0,1-28,28h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24A28,28,0,0,1,168,148Z"> </path>
                            </svg>
                            <span class="side-menu__label">Financeiro</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)"></a>
                            </li>
                            <li class="slide">
                                <a href="../adm-financeiro/transacoes.php" class="side-menu__item">Transações</a>
                            </li>
                            <li class="slide">
                                <a href="../adm-financeiro/carteiras.php" class="side-menu__item">Carteiras</a>
                            </li>

                            <li class="slide">
                                <a href="../adm-financeiro/entradas.php" class="side-menu__item">Entradas</a>
                            </li>

                            <li class="slide">
                                <a href="../adm-financeiro/saidas.php" class="side-menu__item">Saidas</a>
                            </li>
                            <li class="slide">
                                <a href="#" class="side-menu__item">Procurar transação</a>
                            </li>
                        </ul>
                    </li>
                    <!-- End::slide -->


                    <!-- Start::slide -->
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-68a28,28,0,0,1-28,28h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24A28,28,0,0,1,168,148Z"> </path>
                            </svg>
                            <span class="side-menu__label">Criar Transação</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)"></a>
                            </li>


                            <li class="slide">
                                <a href="../adm-criar/entrada.php" class="side-menu__item">Entrada</a>
                            </li>

                            <li class="slide">
                                <a href="../adm-criar/saida.php" class="side-menu__item">Saida</a>
                            </li>

                        </ul>
                    </li>
                    <!-- End::slide -->




                    <li class="slide">
                        <a href="../adm/saques_usuarios.php" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-68a28,28,0,0,1-28,28h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24A28,28,0,0,1,168,148Z"> </path>
                            </svg>
                            <span class="side-menu__label">Saques usuarios</span>

                        </a>
                    </li>



                    <!-- Start::slide -->
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="32" height="32" viewBox="0 0 256 256">
                                <path d="M216,56H176V48a24,24,0,0,0-24-24H104A24,24,0,0,0,80,48v8H40A16,16,0,0,0,24,72V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V72A16,16,0,0,0,216,56ZM96,48a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96ZM216,72v41.61A184,184,0,0,1,128,136a184.07,184.07,0,0,1-88-22.38V72Zm0,128H40V131.64A200.19,200.19,0,0,0,128,152a200.25,200.25,0,0,0,88-20.37V200ZM104,112a8,8,0,0,1,8-8h32a8,8,0,0,1,0,16H112A8,8,0,0,1,104,112Z"> </path>
                            </svg>
                            <span class="side-menu__label">Ajustes Plataforma</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)"></a>
                            </li>
                            <li class="slide">
                                <a href="../adm-config/" class="side-menu__item">Adquirentes & taxas</a>
                            </li>
                            <li class="slide">
                                <a href="#" class="side-menu__item">SMTP E-MAIL</a>
                            </li>
                            <li class="slide">
                                <a href="#" class="side-menu__item">Segurança</a>
                            </li>

                        </ul>
                    </li>
                    <!-- End::slide -->





                <?php endif; ?>








            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>

<!-- Modal -->
<div class="modal fade" id="modalDocumentation" tabindex="-1" aria-labelledby="modalDocumentationLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl custom-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalDocumentationLabel">Documentação</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe src="/documentacao" width="100%" height="100%" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<style>
    /* Desktop styles */
    .custom-modal {
        max-width: 80vw;
    }

    .custom-modal .modal-content {
        height: 90vh;
    }

    .custom-modal .modal-body {
        height: calc(100% - 56px);
        /* Adjust for header height */
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .custom-modal {
            max-width: 100vw;
            margin: 0;
            /* Remove margin for fullscreen effect */
        }

        .custom-modal .modal-content {
            height: 100vh;
            /* Fullscreen height */
            border-radius: 0;
            /* Remove border radius for fullscreen effect */
        }

        .custom-modal .modal-body {
            height: calc(100% - 56px);
            /* Adjust for header height */
        }
    }
</style>