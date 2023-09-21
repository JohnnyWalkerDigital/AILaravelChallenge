#!/bin/bash

# Description: Check Laravel codebase for issues and attempt to fix where possible
# Author: Johnny Walker
# Version: 1.3.3
# Date: 11 January 2023
# Requirements:
#   PHP_CodeSniffer - https://github.com/squizlabs/PHP_CodeSniffer
#   Custom Codesniffer Config - https://gist.github.com/JohnnyWalkerDesign/aca5ee0d21c12d31440327079ae40c23
#   Larastan - https://github.com/nunomaduro/larastan
# Latest version at: https://gist.github.com/JohnnyWalkerDigital/512d3a7d714d081e3e791ce00f34d511
# Usage: bash check.sh

# Reset
Color_Off='\033[0m'       # Text Reset

# Regular Colors
Black='\033[0;30m'        # Black
Red='\033[0;31m'          # Red
Green='\033[0;32m'        # Green
Yellow='\033[0;33m'       # Yellow
Blue='\033[0;34m'         # Blue
Purple='\033[0;35m'       # Purple
Cyan='\033[0;36m'         # Cyan
White='\033[0;37m'        # White

# Set flags
QUICK_MODE='false'
while getopts 'q' flag; do
  case "${flag}" in
    q) QUICK_MODE='true' ;;
    *) printf "${Yellow}WARNING:${Color_Off} Unexpected parameter ${flag}" ;;
  esac
done
readonly QUICK_MODE

function is_codesniffer_installed() {
  # Check if PHP_CODESNIFFER is installed
  if [[ ! -f ./vendor/bin/phpcs ]]; then
    err "${Yellow}PHP_CODESNIFFER NOT FOUND:${Color_Off} Please run: ${Cyan}composer require --dev squizlabs/php_codesniffer${Color_Off} or ${Cyan}composer install${Color_Off}"
    exit 1
  fi

  # Check if PHP_CODESNIFFER configuration is present
  if [[ ! -f ./phpcs.xml  ]]; then
    err "${Yellow}PHP_CODESNIFFER CONFIG NOT FOUND:${Color_Off} Please ensure phpcs.xml is present"
    exit 1
  fi

  return 0
}

function run_codesniffer_check() {
  local result

  printf "${Cyan}Running PHP_CodeSniffer checks...${Color_Off}\n";

  # Run PHP_CODESNIFFER (hide warnings)
  ./vendor/bin/phpcs -n
  result=$?

  if [[ $result -eq 2 ]]; then
    # Fixable errors found
    return 2
  elif [[ $result -eq 3 ]]; then
    err "${Yellow}PHP_CODESNIFFER PROCESSING ERROR:${Color_Off} There was a processing error that prevented PHPCS from running successfully"
    exit 1
  elif [[ $result -eq 1 ]]; then
    err "${Red}PHP_CODESNIFFER FAILED:${Color_Off} Please manually fix the above errors and try again"
    exit 1
  fi

  printf "${Green}PHP_CODESNIFFER SUCCEEDED\n\n${Color_Off}"
  return 0
}

function run_codesniffer_fix() {
  local result

  # Run PHP_CODESNIFFER fixer
  ./vendor/bin/phpcbf
  result=$?

  # Exit codes are non-standard in v3
  # See: https://github.com/squizlabs/PHP_CodeSniffer/issues/1818#issuecomment-354420927
  if [[ $result -eq 2 ]]; then
    err "${Red}PHP_CODESNIFFER AUTOMATIC FIX FAILED:${Color_Off} Please manually fix the errors and try again"
    exit 1
  elif [[ $result -eq 3 ]]; then
    err "${Yellow}PHP_CODESNIFFER PROCESSING ERROR:${Color_Off} There was a processing error that prevented PHP_CS from running successfully"
    exit 1
  fi

  printf "${Green}PHP_CODESNIFFER AUTOMATIC FIX SUCCEEDED\n\n${Color_Off}"

  # Committing changes to GIT
  printf "${Cyan}Commiting fixed files to GIT...${Color_Off}\n"
  git add .
  git commit -m "[Deploy script] Apply automatic PHP_CODESNIFFER fixes"
  printf "${Green}Done!${Color_Off}\n\n";

  return 0
}

function is_larastan_installed() {
  # Check if LARASTAN is installed
  if [[ ! -d ./vendor/nunomaduro/larastan ]]; then
    err "${Yellow}LARASTAN NOT FOUND:${Color_Off} Please run: ${Cyan}composer require --dev nunomaduro/larastan${Color_Off} or ${Cyan}composer install${Color_Off}"
    exit 1
  fi

  # Check if LARASTAN configuration is present
  if [[ ! -f ./phpstan.neon  ]]; then
    err "${Yellow}LARASTAN CONFIG NOT FOUND:${Color_Off} Please ensure phpstan.neon is present"
    exit 1
  fi

  return 0
}

function run_larastan_check() {
  local result

  printf "${Cyan}Running Larastan checks...${Color_Off}\n";

  # Run LARASTAN (hide warnings)
  ./vendor/bin/phpstan analyse --memory-limit=2G
  result=$?

  if [[ $result -eq 1 ]]; then
    err "${Red}LARASTAN CHECKS FAILED:${Color_Off} Please manually fix the above errors and try again"
    exit 1
  fi

  printf "${Green}LARASTAN CHECKS SUCCEEDED\n\n${Color_Off}"
  return 0
}

function check_for_uncommitted_changes() {
  # Check for uncommitted changes in GIT...
  if [[ "$(git status --porcelain=v1 2>/dev/null | wc -l)" -gt "0" ]]; then
      err "${Red}CHECK FAILED:${Color_Off} Please commit all changes or run in quick mode (-q) to skip Laravel tests...";
      exit 1;
  fi
}

# Note: Laravel tests can fail if assets are not built
function build_assets() {
  # Problem when attempting this on AWS EB, so doing it locally instead
  local result

  printf "\n${Cyan}Bundling assets for deployment...${Color_Off}\n";
  # Check if running Vite or Mix
  if [[ -f ./webpack.mix.js ]]; then
    printf "Laravel Mix found...\n"
    npm run prod
    result=$?
  elif [[ -f ./vite.config.js ]]; then
    printf "Vite found..."
    npm run build
    result=$?
  else
    err "${Red}CHECK FAILED:${Color_Off} Could not determine which asset bundler is used";
    exit 1;
  fi

  if [[ ! $result -eq 0 ]]; then
    err "${Red}CHECK FAILED:${Color_Off} There was an error while attempting to bundle assets.
    Running ${Cyan}npm uninstall webpack --save-dev${Color_Off} and then ${Cyan}npm install webpack --save-dev${Color_Off} might help";
    exit 1;
  fi

  # Commit changes to GIT
  if [[ "$(git status --porcelain=v1 2>/dev/null | wc -l)" -gt "0" ]]; then
    printf "\n${Cyan}Commiting bundled assets to GIT...${Color_Off}\n"
    git add .
    git commit -m "[Deploy script] Add bundled assets for production"
    printf "${Green}Done!${Color_Off}\n";
  else
    # No changes
    printf "\nNothing to commit to GIT... ${Green}Skipped!${Color_Off}\n"
  fi

  return 0
}

function run_laravel_tests() {
  # Laravel Tests
  printf "${Cyan}Running Laravel tests...${Color_Off}\n";
  php artisan test --stop-on-failure
  result=$?

  if [[ ! $result -eq 0 ]]; then
    err "${Red}LARAVEL TESTS FAILED:${Color_Off} Please manually fix the above error and try again"
    exit 1
  fi

  return 0
}

function err() {
  printf "$*\n\n" >&2
}

function main() {
  local result

  if [[ "$QUICK_MODE" = "true" ]]; then
    printf "\nRUNNING IN QUICK MODE...\n\n";
  fi

  # CodeSniffer
  is_codesniffer_installed
  run_codesniffer_check
  result=$?

  if [[ result -eq 2 ]]; then
    printf "${Yellow}PHP_CODESNIFFER FOUND FIXABLE ERRORS:${Color_Off} Errors that are automatically fixable were found. Attempting to fix...\n\n"
    run_codesniffer_fix
    # Try again
    run_codesniffer_check
  fi

  # Larastan
  is_larastan_installed
  run_larastan_check

  if [[ "$QUICK_MODE" = "false" ]]; then
    check_for_uncommitted_changes
    build_assets
    run_laravel_tests
    printf "${Green}LARAVEL TESTS PASSED${Color_Off}\n\n";
  else
    printf "\nQUICK MODE: Skipping further tests...\n\n";
  fi

  exit 0
}

main "$@"
