<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}
include '../layouts/base.php';
?>
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-center min-vh-100">
            <div class="text-center">
                <div class="mb-4">
                    <span class="avatar avatar-xxl bg-danger-transparent rounded-circle">
                        <i class="bi bi-slash-circle fs-1"></i>
                    </span>
                </div>
                <h3 class="fw-bold mb-2">Módulo Descontinuado</h3>
                <p class="text-muted fs-15 mb-4 max-w-lg mx-auto">
                    O sistema de Saques foi removido. Utilize o painel do seu provedor de pagamentos para gerenciar saques.
                </p>
                <a href="../home" class="btn btn-primary">Voltar para Home</a>
            </div>
        </div>
    </div>
</div>
