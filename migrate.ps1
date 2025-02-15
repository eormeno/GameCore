Clear-Host

$currentPath = Get-Location
# check if the currentPath ends with 'src' or 'src\'
if ($currentPath -match 'src$' -or $currentPath -match 'src\\$') {
    # if the currentPath ends with 'src' or 'src\', then change the directory to the parent directory
    Set-Location ..
}

Set-Location src

Remove-Item database/database.sqlite -ErrorAction SilentlyContinue
# execute the migration force and seed
php artisan migrate --force --seed
php artisan games