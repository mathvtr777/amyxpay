<header class="h-20 flex items-center justify-between px-8 bg-white dark:bg-background-dark border-b border-slate-200 dark:border-border-dark sticky top-0 z-40">
    <button class="p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark rounded-lg md:hidden">
        <span class="material-icons-round">menu</span>
    </button>
    <div class="flex items-center gap-6 ml-auto">
        <button class="p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark rounded-lg">
            <span class="material-icons-round">wb_sunny</span>
        </button>
        <button class="p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark rounded-lg">
            <span class="material-icons-round">fullscreen</span>
        </button>
        <div class="flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-border-dark">
            <div class="text-right hidden md:block">
                <div class="flex items-center justify-end gap-2 text-sm font-semibold text-slate-900 dark:text-white leading-tight">
                    <?php echo htmlspecialchars($nome); ?>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-widest <?php echo($planService && $planService->isPro()) ? 'bg-amber-500/20 text-amber-500 border border-amber-500/30' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'; ?>">
                        <?php echo htmlspecialchars($planService ? $planService->getPlanName() : 'STARTER'); ?>
                    </span>
                </div>
                <div class="flex items-center justify-end gap-2 mt-0.5">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400"><?php echo($permission > 2) ? 'Administrador' : 'Usuário'; ?></p>
                    <?php if ($planService && !$planService->isPro()): ?>
                        <!-- Upgrade button for free users -->
                        <a href="../planos/" class="text-[10px] text-primary hover:text-primary/80 font-bold transition-colors">Fazer Upgrade</a>
                    <?php
endif; ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center overflow-hidden border-2 border-primary/30">
                <img alt="User Avatar" class="w-full h-full object-cover" src="../img/user.png" onerror="this.src='../img/logo-favicon.png'">
            </div>
        </div>
    </div>
</header>
