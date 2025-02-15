Clear-Host

# if there is the '-r' argument, then remove the database.sqlite file
if ($args -contains '-r') {
    Remove-Item database/database.sqlite -ErrorAction SilentlyContinue
    # execute the migration force and seed
    php artisan migrate --force --seed
    php artisan games
}

$artisan = Start-Process -FilePath "powershell.exe" -ArgumentList "-Command", "php artisan serve" -PassThru -NoNewWindow
