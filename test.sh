#!/bin/bash

clear

filter=$1

if [ -z "$filter" ]; then
    # if no filter is provided, then run all tests
    php artisan test
    exit 0
fi

# Declare an associative array with the prefix and the test class name
declare -A testClasses=(
    ["auth"]="AuthTest"
    ["debug"]="DebugTest"
    ["cnt"]="CNTPlayGameTest"
    ["gtn"]="GTNPlayGameTest"
    ["bba"]="BBAPlayGameTest"
    ["mtq"]="MTQPlayGameTest"
    ["ui"]="UIBuilderTest"
    ["demo"]="DemoUITest"
    ["select"]="SelectDemoTest"
    ["input"]="InputDemoTest"
)

# Find the filter in prefix and get the test class name
testClass="${testClasses[$filter]}"

if [ -z "$testClass" ]; then
    echo -e "\033[31mInvalid filter. Available filters are:\033[0m"
    for key in "${!testClasses[@]}"; do
        echo -e "  \033[33m$key\033[0m - \033[32m${testClasses[$key]}\033[0m"
    done
    exit 1
fi

php artisan test --filter="$testClass" --compact
