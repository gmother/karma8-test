<?php

set_time_limit(300);

require_once __DIR__ . "/common.php";

$users = get_users_to_check();

foreach ($users as $user) {
    $startTs = time();
    $res = check_email($user['email']);
    update_check_result($user['id'], $res);
    $delay = time() - $startTs;
    write_log(sprintf('User %d, email %s has been checked: %s (%d sec)', $user['id'], $user['email'], $res ? 'ok' : 'fail', $delay));
}