#!/bin/bash

if [ -z "$DELAY" ]; then
    echo "DELAY must be set"
    exit 1
fi

while true
do
  echo "[`date`] Run check-emails in $CHECK_THREADS threads"
  for (( k = 0; k <= $CHECK_THREADS; k++ ))
  do
    /usr/local/bin/php /var/www/html/app/src/check-emails.php $k &
  done

  echo "[`date`] Run send-notifications in $SEND_THREADS threads"
  for (( k = 0; k <= $SEND_THREADS; k++ ))
  do
    /usr/local/bin/php /var/www/html/app/src/send-notifications.php $k &
  done

  echo "[`date`] Wait $DELAY seconds"
  sleep $DELAY
done
