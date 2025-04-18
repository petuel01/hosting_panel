<?php
$output1 = shell_exec('ls -l');
$output2 = shell_exec('whoami');
$output3 = shell_exec('uptime');
$output4 = shell_exec('lxc list 2>&1'); // Added lxc command with error redirection

echo "<pre>";
echo "List of files:\n$output1\n";
echo "Current user:\n$output2\n";
echo "System uptime:\n$output3\n";
echo "LXC containers:\n$output4\n"; // Display output of lxc command
echo "</pre>";
?>