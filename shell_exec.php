<?php
putenv('HOME=/home/www-data'); // Set HOME to a valid directory
$output4 = shell_exec('lxc list 2>&1'); // Run the lxc command
echo "<pre>";
echo "LXC containers:\n$output4\n";
echo "</pre>";