Clear-Host

$filter = $args[0]
if ($filter -eq $null) {
    # if no filter is provided, then run all tests
    php artisan test
    Set-Location ..
    return
}

# A dictionary with the prefix and the test class name
$testClasses = @{
    'auth' = 'AuthTest';
    'gtn' = 'GTNPlayGameTest';
    'bba' = 'BBAPlayGameTest';
    'mtq' = 'MTQPlayGameTest';
}

# Find the filter in prefix and get the test class name
$testClass = $testClasses[$filter]

if ($testClass -eq $null) {
    Write-Host "Invalid filter. Available filters are:" -ForegroundColor Red
    foreach ($key in $testClasses.Keys) {
        Write-Host "  $key" -ForegroundColor Yellow -NoNewline
        Write-Host " - $($testClasses[$key])" -ForegroundColor Green
    }
    return
}

php artisan test --filter=$testClass
