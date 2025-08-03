#!/usr/bin/env bash
# Use a lock file in a location that's writable in Plesk
LOCKFILE="/tmp/import_all_jobs.lock"

# Check if the lock file exists
if [ -e "$LOCKFILE" ]; then
    # Check if the process is still running
    if ps -p $(cat "$LOCKFILE" 2>/dev/null) > /dev/null 2>&1; then
        echo "Another instance is already running. Exiting."
        exit 0
    else
        # Lock file exists but process is not running, remove the stale lock
        rm -f "$LOCKFILE"
    fi
fi

# Create lock file and store the PID
echo $$ > "$LOCKFILE"

# Set up trap to remove lock file when the script exits
trap 'rm -f "$LOCKFILE"; exit $?' INT TERM EXIT

# Change to the correct directory for Plesk
cd /home/httpd/vhosts/exedra.ch/httpdocs || exit 1

# Set environment variables directly for production
export APP_ENV=prod
export APP_DEBUG=0

# Define PHP and console paths
PHP=/opt/php82/bin/php
CONSOLE=bin/console

# Run the import commands
$PHP $CONSOLE app:import-zvoove-jobs --api-source=BSL --env=prod --no-debug
$PHP $CONSOLE app:import-zvoove-jobs --api-source=BSLAG --env=prod --no-debug
$PHP $CONSOLE app:import-zvoove-jobs --api-source=ZRH --env=prod --no-debug
$PHP $CONSOLE app:import-jobdesk-jobs --env=prod --no-debug

# Run the DeleteApplicationsCommand to clean up old applications
$PHP $CONSOLE app:delete-applications --env=prod --no-debug
