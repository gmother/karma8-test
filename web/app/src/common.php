<?php

function getDb(): mysqli
{
    static $conn;

    if (empty($conn)) {
        $conn = mysqli_connect('mysql', 'dev', 'dev');
        $conn->select_db('test');
    }

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

function getStats(): array
{
    $result = [];

    $result['total_users'] = getDb()->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
    $result['emails_checked'] = getDb()->query('SELECT COUNT(*) FROM users WHERE checked = 1')->fetch_row()[0];
    $result['emails_valid'] = getDb()->query('SELECT COUNT(*) FROM users WHERE checked = 1 AND valid = 1')->fetch_row()[0];
    $result['emails_sent'] = getDb()->query('SELECT SUM(sent_num) FROM users')->fetch_row()[0];

    return $result;
}

function get_users_to_check(): array
{
    $limit = (int)($_SERVER['CHECK_NUM'] ?? 1);

    if (!getDb()->begin_transaction()) {
        die("Can't begin transaction");
    }

    // INTERVAL 75 HOUR - проверяем юзеров за 3 часа до первой предполагаемой рассылки
    $result = getDb()->query("    
        SELECT * FROM users
        WHERE valid_ts < NOW() + INTERVAL 75 HOUR 
            AND confirmed = 0
            AND checked = 0
            AND (locked_ts IS NULL OR locked_ts < NOW() - INTERVAL 15 MINUTE)
        ORDER BY valid_ts
        LIMIT ${limit}
        FOR UPDATE
    ");
    if (!$result) {
        getDb()->rollback();
        die("Can't get users");
    }

    $users = $result->fetch_all(MYSQLI_ASSOC);
    if (empty($users)) {
        return [];
    }

    $userIds = [];
    foreach ($users as $row) {
        $userIds[] = (int)$row['id'];
    }
    $ids = implode(",", $userIds);

    $result = getDb()->query("UPDATE users SET locked_ts = NOW() WHERE id IN (${ids})");
    if (!$result) {
        getDb()->rollback();
        die("Can't update users");
    }

    if (!getDb()->commit()) {
        getDb()->rollback();
        die("Can't commit transaction");
    }

    return $users;
}

function update_check_result(int $id, bool $res): void
{
    getDb()->query(
        sprintf(
            'UPDATE users SET checked = 1, valid = %d, locked_ts = NULL WHERE id = %d',
            $res ? 1 : 0,
            $id
        )
    );
}

function get_users_to_notify(): array
{
    $limit = (int)($_SERVER['SEND_NUM'] ?? 1);

    if (!getDb()->begin_transaction()) {
        die("Can't begin transaction");
    }

    $result = getDb()->query("    
        SELECT id, email FROM users
        WHERE valid_ts < NOW() + INTERVAL 72 HOUR 
            AND (confirmed = 1 OR (checked = 1 AND valid = 1)) 
            AND (sent_ts IS NULL OR sent_ts < NOW() - INTERVAL 48 HOUR)
            AND (locked_ts IS NULL OR locked_ts < NOW() - INTERVAL 15 MINUTE)
        ORDER BY valid_ts
        LIMIT ${limit}
        FOR UPDATE
    ");
    if (!$result) {
        getDb()->rollback();
        die("Can't get users");
    }

    $users = $result->fetch_all(MYSQLI_ASSOC);
    if (empty($users)) {
        return [];
    }

    $userIds = [];
    foreach ($users as $row) {
        $userIds[] = (int)$row['id'];
    }
    $ids = implode(",", $userIds);

    getDb()->query("UPDATE users SET locked_ts = NOW() WHERE id IN (${ids})");
    if (!$result) {
        getDb()->rollback();
        die("Can't update users");
    }

    if (!getDb()->commit()) {
        getDb()->rollback();
        die("Can't commit transaction");
    }

    return $users;
}

function update_sent_result(int $id): void
{
    getDb()->query("UPDATE users SET sent_ts = NOW(), sent_num = sent_num + 1, locked_ts = NULL WHERE id = ${id}");
}

function check_email(string $email): bool
{
    sleep(rand(1, 60));
    return rand(0, 1) == 0;
}

function send_email(string $from, string $to, string $text): void
{
    sleep(rand(1, 10));
}

function write_log($string)
{
    $row = sprintf("[%s] (%s) %s\n", date('Y-m-d H:i:s'), $_SERVER['argv'][1] ?? '-', $string);
    echo $row;
}