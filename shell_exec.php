<?php
$output1 = shell_exec('ls -l');
$output2 = shell_exec('whoami');
$output3 = shell_exec('uptime');

echo "<pre>";
echo "List of files:\n$output1\n";
echo "Current user:\n$output2\n";
echo "System uptime:\n$output3\n";
echo "</pre>";
?>