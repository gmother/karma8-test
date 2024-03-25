<?php

require_once "../app/src/common.php";

$stats = getStats();

?><html>
<head>
<title>Service stats</title>
</head>
<body>
<h1>Service stats</h1>
<p>Users total: <b><?= $stats['total_users'] ?></b></p>
<p>E-mails checked / valid: <b><?= $stats['emails_checked'] ?> / <?= $stats['emails_valid'] ?></b></p>
<p>E-mails sent: <b><?= $stats['emails_sent'] ?></b></p>
</body>
</html>