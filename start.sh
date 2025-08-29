#!/bin/bash

clear

# Function to get a value from .env file (non-commented lines)
get_env_value() {
    local key="$1"
    grep "^$key=" .env 2>/dev/null | head -1 | cut -d'=' -f2- | sed 's/^"\(.*\)"$/\1/'
}

env_db=$(get_env_value "DB_CONNECTION")

if [[ "$*" == *"-r"* ]]; then
    if [[ "$env_db" == "mysql" ]]; then
        # Get the .env DB values that do not start with '#'
        db=$(get_env_value "DB_DATABASE")
        user=$(get_env_value "DB_USERNAME")
        pass=$(get_env_value "DB_PASSWORD")

        echo "Removing Database: $db with $user privileges"
        mysql -u "$user" -p"$pass" -e "DROP DATABASE IF EXISTS $db; CREATE DATABASE $db;"
    fi

    if [[ "$env_db" == "sqlite" ]]; then
        # Remove the database
        rm -f database/database.sqlite
    fi

    php artisan migrate --force --seed
    php artisan games
fi

# Start the artisan server
php artisan serve
