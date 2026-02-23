# Fix legacy references in XAMPP htdocs directory
$basePath = "C:\xampp\htdocs\uranoPAY\web"
$countFixed = 0

Get-ChildItem -Path $basePath -Recurse -Filter "*.php" | ForEach-Object {
    $file = $_.FullName
    $content = Get-Content $file -Raw -Encoding UTF8
    if ($null -eq $content) { return }
    $original = $content

    # 1. Replace deposito_liquido with amount in Queries and Variables
    # Use single quotes to avoid PowerShell variable expansion
    $content = $content -replace 'SUM\(deposito_liquido\)', 'SUM(amount)'
    $content = $content -replace 'deposito_liquido', 'amount'
    $content = $content -replace "\['deposito_liquido'\]", "['amount']"

    # 2. Remove taxa_cash_in, taxa_cash_out, taxa_... from INSERT/SELECT
    # Regex: comma, space, dollar-sign(escaped), variable name
    
    # Remove from SELECT list (no dollar sign)
    $content = $content -replace ', taxa_cash_in', ''
    $content = $content -replace ', taxa_cash_out', ''
    $content = $content -replace ', taxa_pix_cash_in_adquirente', ''
    $content = $content -replace ', taxa_pix_cash_in_valor_fixo', ''
    
    # Remove from bind_result (variable names with dollar sign)
    # We need to escape the backslash for the regex engine: \\$ for a literal dollar sign in the match
    $content = $content -replace ', \$taxa_cash_in', ''
    $content = $content -replace ', \$taxa_cash_out', ''
    $content = $content -replace ', \$taxa_pix_cash_in_adquirente', ''
    $content = $content -replace ', \$taxa_pix_cash_in_valor_fixo', ''

    # 3. Patch 'retiradas' table queries
    $content = $content -replace 'SELECT SUM\(valor_liquido\) FROM retiradas', 'SELECT 0 FROM dual'
    $content = $content -replace 'SELECT COUNT\(\*\) FROM retiradas', 'SELECT 0 FROM dual'
    $content = $content -replace 'SELECT COUNT\(\*\) AS total FROM retiradas', 'SELECT 0 as total FROM dual'
    
     # 4. Remove saldo/valor_sacado from bind_result
    $content = $content -replace ', \$saldo', ''
    $content = $content -replace ', \$valor_sacado', ''

    if ($content -ne $original) {
        Set-Content -Path $file -Value $content -Encoding UTF8 -NoNewline
        Write-Host "PATCHED: $file" -ForegroundColor Yellow
        $countFixed++
    }
}

Write-Host "`nDone! $countFixed files patched." -ForegroundColor Cyan
