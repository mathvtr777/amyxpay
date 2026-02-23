# Fix all 'saldo' references in XAMPP htdocs directory
$basePath = "C:\xampp\htdocs\uranoPAY\web"
$countFixed = 0

Get-ChildItem -Path $basePath -Recurse -Filter "*.php" | ForEach-Object {
    $file = $_.FullName
    $content = Get-Content $file -Raw -Encoding UTF8
    if ($null -eq $content) { return }
    $original = $content

    # Fix SELECT queries with saldo (with cliente_id and taxa_cash_out)
    $content = $content -replace 'SELECT user_id, nome, status, permission, saldo, transacoes_aproved, cliente_id, taxa_cash_out FROM users', 'SELECT user_id, nome, status, permission, transacoes_aproved, cliente_id FROM users'
    
    # Fix SELECT queries with saldo (with cliente_id)
    $content = $content -replace 'SELECT user_id, nome, status, permission, saldo, transacoes_aproved, cliente_id FROM users', 'SELECT user_id, nome, status, permission, transacoes_aproved, cliente_id FROM users'

    # Fix SELECT queries with saldo (without cliente_id)
    $content = $content -replace 'SELECT user_id, nome, status, permission, saldo, transacoes_aproved FROM users', 'SELECT user_id, nome, status, permission, transacoes_aproved FROM users'

    # Fix bind_result with saldo (with cliente_id and taxa_cash_out)
    $content = $content -replace '\$stmt_user->bind_result\(\$user_id, \$nome, \$status, \$permission, \$saldo, \$transacoes_aproved, \$cliente_id, \$taxa_cash_out\)', '$stmt_user->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved, $cliente_id)'
    $content = $content -replace '\$stmt->bind_result\(\$user_id, \$nome, \$status, \$permission, \$saldo, \$transacoes_aproved, \$cliente_id, \$taxa_cash_out\)', '$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved, $cliente_id)'

    # Fix bind_result with saldo (with cliente_id)
    $content = $content -replace '\$stmt_user->bind_result\(\$user_id, \$nome, \$status, \$permission, \$saldo, \$transacoes_aproved, \$cliente_id\)', '$stmt_user->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved, $cliente_id)'
    $content = $content -replace '\$stmt->bind_result\(\$user_id, \$nome, \$status, \$permission, \$saldo, \$transacoes_aproved, \$cliente_id\)', '$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved, $cliente_id)'

    # Fix bind_result with saldo (without cliente_id)
    $content = $content -replace '\$stmt->bind_result\(\$user_id, \$nome, \$status, \$permission, \$saldo, \$transacoes_aproved\)', '$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved)'
    $content = $content -replace '\$stmt_user->bind_result\(\$user_id, \$nome, \$status, \$permission, \$saldo, \$transacoes_aproved\)', '$stmt_user->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved)'

    if ($content -ne $original) {
        Set-Content -Path $file -Value $content -Encoding UTF8 -NoNewline
        Write-Host "FIXED: $file" -ForegroundColor Green
        $countFixed++
    }
}

Write-Host "`nDone! $countFixed files fixed." -ForegroundColor Cyan
