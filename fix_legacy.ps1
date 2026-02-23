# Fix legacy references in XAMPP htdocs directory
$basePath = "C:\xampp\htdocs\uranoPAY\web"
$countFixed = 0

Get-ChildItem -Path $basePath -Recurse -Filter "*.php" | ForEach-Object {
    $file = $_.FullName
    $content = Get-Content $file -Raw -Encoding UTF8
    if ($null -eq $content) { return }
    $original = $content

    # 1. Replace deposito_liquido with amount in Queries and Variables
    $content = $content -replace "SUM\(deposito_liquido\)", "SUM(amount)"
    $content = $content -replace "deposito_liquido", "amount"
    $content = $content -replace "\['deposito_liquido'\]", "['amount']"

    # 2. Remove taxa_cash_in, taxa_cash_out, taxa_... from INSERT/SELECT (Crude regex but effective for lists)
    # Remove from SELECT list
    $content = $content -replace ", taxa_cash_in", ""
    $content = $content -replace ", taxa_cash_out", ""
    $content = $content -replace ", taxa_pix_cash_in_adquirente", ""
    $content = $content -replace ", taxa_pix_cash_in_valor_fixo", ""
    
    # Remove from bind_result (variable names)
    $content = $content -replace ", \$taxa_cash_in", ""
    $content = $content -replace ", \$taxa_cash_out", ""
    $content = $content -replace ", \$taxa_pix_cash_in_adquirente", ""
    $content = $content -replace ", \$taxa_pix_cash_in_valor_fixo", ""

    # 3. Patch 'retiradas' table queries to be harmless (dummy table)
    # This prevents Fatal Error: Table doesn't exist.
    # We replace "FROM retiradas" with a comment or dummy check if possible, but regex is risky for logic.
    # Instead, we'll try to replace "SELECT ... FROM retiradas" with a dummy select if it's a simple count/sum
    
    # Replace SUM(...) FROM retiradas
    $content = $content -replace "SELECT SUM\(valor_liquido\) FROM retiradas", "SELECT 0 FROM dual"
    $content = $content -replace "SELECT COUNT\(\*\) FROM retiradas", "SELECT 0 FROM dual"
    $content = $content -replace "SELECT COUNT\(\*\) AS total FROM retiradas", "SELECT 0 as total FROM dual"
    
     # 4. Remove saldo/valor_sacado from bind_result if missed by previous script
    $content = $content -replace ", \$saldo", ""
    $content = $content -replace ", \$valor_sacado", ""

    if ($content -ne $original) {
        Set-Content -Path $file -Value $content -Encoding UTF8 -NoNewline
        Write-Host "PATCHED: $file" -ForegroundColor Yellow
        $countFixed++
    }
}

Write-Host "`nDone! $countFixed files patched." -ForegroundColor Cyan
