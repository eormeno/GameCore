Clear-Host

# A function for get some value from a .env file non-commented
function Get-EnvValue {
    param (
        [string]$key
    )

    Get-Content .env | Select-String -Pattern $key | Where-Object { $_ -notmatch "^#" } | ForEach-Object { $_ -replace "$key=", "" }
}

$env = Get-EnvValue "DB_CONNECTION"

if ($args -contains '-r') {

    if ($env -eq "mysql") {
        # get the .env DB_DATABASE value that does not start with '#'
        $db = Get-EnvValue "DB_DATABASE"
        # get the .env DB_USERNAME value that does not start with '#'
        $user = Get-EnvValue "DB_USERNAME"
        # get the .env DB_PASSWORD value that does not start with '#'
        $pass = Get-EnvValue "DB_PASSWORD"

        Write-Host "Removing Database: $db with $user privileges"
        & "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql" -u $user -p"$pass" -e "DROP DATABASE $db; CREATE DATABASE $db;"
    }

    if ($env -eq "sqlite") {
        # remove the database
        Remove-Item database/database.sqlite -ErrorAction SilentlyContinue
    }

    php artisan migrate --force --seed
    php artisan games
}

# if there is the '-r' argument, then remove the database.sqlite file
# if ($args -contains '-r') {
#     Remove-Item database/database.sqlite -ErrorAction SilentlyContinue
#     # execute the migration force and seed
#     php artisan migrate --force --seed
#     php artisan games
# }

$artisan = Start-Process -FilePath "powershell.exe" -ArgumentList "-Command", "php artisan serve" -PassThru -NoNewWindow
