#!/usr/bin/env bash
set -euo pipefail

laravel_version="${LARAVEL_VERSION:-13}"

case "$laravel_version" in
    9|9.*) testbench_version="^7.6" ;;
    10|10.*) testbench_version="^8.0" ;;
    11|11.*) testbench_version="^9.0" ;;
    12|12.*) testbench_version="^10.0" ;;
    13|13.*) testbench_version="^11.0" ;;
    *)
        echo "Unsupported Laravel version: $laravel_version (supported: 9, 10, 11, 12, 13)" >&2
        exit 1
        ;;
esac

original_composer="$(mktemp)"
cp composer.json "$original_composer"
original_lock="$(mktemp)"
had_lock=false
if [[ -f composer.lock ]]; then
    cp composer.lock "$original_lock"
    had_lock=true
fi
restore_composer() {
    cp "$original_composer" composer.json
    rm -f "$original_composer"
    if [[ "$had_lock" == true ]]; then
        cp "$original_lock" composer.lock
    else
        rm -f composer.lock
    fi
    rm -f "$original_lock"
}
trap restore_composer EXIT

composer require --dev "laravel/framework:${laravel_version}.*" "orchestra/testbench:${testbench_version}" --no-interaction --no-update
composer update --prefer-dist --no-interaction --no-security-blocking
vendor/bin/pest "$@"