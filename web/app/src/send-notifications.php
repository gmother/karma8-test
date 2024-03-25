<?php

set_time_limit(300);

require_once __DIR__ . "/common.php";

$users = get_users_to_notify();

foreach ($users as $user) {
    $startTs = time();
    send_email('no-reply@mydomain.com', $user['email'], $user['username'] . ', your subscription is expiring soon');
    update_sent_result($user['id']);
    $delay = time() - $startTs;
    write_log(sprintf('User %d, notification to %s has been sent (%d sec)', $user['id'], $user['email'], $delay));
}