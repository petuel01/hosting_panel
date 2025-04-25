#!/bin/bash

USER_DIR_BASE="/home/users"
FILE_MANAGER_SRC="/var/www/html/tinyfilemanager"
NGINX_SITES_DIR="/etc/nginx/sites-available"
NGINX_SITES_ENABLED="/etc/nginx/sites-enabled"
PORT_BASE=8000
LOG_FILE="/var/log/user_directory_management.log"

if [ "$EUID" -ne 0 ]; then
  echo "Error: This script must be run as root."
  exit 1
fi

validate_username() {
    local username=$1
    if [[ ! "$username" =~ ^[a-z_][a-z0-9_-]*$ ]]; then
        echo "Error: Invalid username format."
        exit 1
    fi
}

validate_size_limit() {
    local size_limit=$1
    if ! [[ "$size_limit" =~ ^[0-9]+$ ]]; then
        echo "Error: Size limit must be a number in KB."
        exit 1
    fi
}

assign_port() {
    local username=$1
    local port_file="/var/user_ports.txt"

    if [ ! -f "$port_file" ]; then
        touch "$port_file"
    fi

    # Check if user already has a port
    user_port=$(grep "^$username:" "$port_file" | cut -d':' -f2)
    if [ -n "$user_port" ]; then
        echo "$user_port"
        return
    fi

    # Find the next free port
    used_ports=$(cut -d':' -f2 "$port_file")
    next_port=$((PORT_BASE + 1))
    while echo "$used_ports" | grep -q "$next_port"; do
        ((next_port++))
    done

    echo "$username:$next_port" >> "$port_file"
    echo "$next_port"
}

create_linux_user() {
    local username=$1
    local password=$2

    if id "$username" &>/dev/null; then
        echo "User $username already exists."
    else
        echo "Creating user $username..."
        useradd -m -d "$USER_DIR_BASE/$username" -s /bin/bash "$username"
        echo "$username:$password" | chpasswd
        echo "User $username created and password set."
    fi
}

create_user_directory() {
    local username=$1
    local user_dir="$USER_DIR_BASE/$username"
    local web_dir="$user_dir/public_html"

    mkdir -p "$web_dir"
    chown -R www-data:www-data "$web_dir"
    chmod -R 755 "$web_dir"
}

setup_file_manager() {
    local username=$1
    local password=$2
    local web_dir="$USER_DIR_BASE/$username/public_html"
    local fm_dir="$web_dir/filemanager"

    cp -r "$FILE_MANAGER_SRC" "$fm_dir"
    chown -R www-data:www-data "$fm_dir"

    config_file="$fm_dir/config.php"
    if [[ -f "$config_file" ]]; then
        sed -i "s/\('admin' => \)[^,]*,/\1'$password',/" "$config_file"
        sed -i "s/\$auth_users =.*/\$auth_users = array(\n  '$username' => '$password'\n);/" "$config_file"
    fi
}

setup_nginx_port() {
    local username=$1
    local port=$2
    local web_dir="$USER_DIR_BASE/$username/public_html"
    local conf_file="$NGINX_SITES_DIR/$username.conf"

    echo "Setting up Nginx config for $username on port $port..."

    cat > "$conf_file" <<EOF
server {
    listen $port;
    server_name localhost;

    root $web_dir;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock; # ✅ Adjust to match your PHP version
    }

    location ~ /\.ht {
        deny all;
    }
}  
EOF

    ln -sf "$conf_file" "$NGINX_SITES_ENABLED/$username"
    nginx -t && systemctl reload nginx
}

monitor_and_enforce_limits() {
    local username=$1
    local size_limit=$2
    local user_dir="$USER_DIR_BASE/$username"

    dir_size=$(du -sk "$user_dir" | cut -f1)

    if [ "$dir_size" -gt "$size_limit" ]; then
        msg="Directory $user_dir exceeds the size limit of $size_limit KB (Current: $dir_size KB)"
        echo "Warning: $msg"
        echo "$(date): $msg" >> "$LOG_FILE"
        logger -t user_dir_mgmt "$msg"
    else
        echo "Directory $user_dir is within the size limit of $size_limit KB (Current: $dir_size KB)"
    fi
}

main() {
    local username password size_limit

    if [[ $# -eq 3 ]]; then
        username=$1
        password=$2
        size_limit=$3
    else
        read -p "Enter the username: " username
        read -s -p "Enter the password: " password
        echo ""
        read -p "Enter the size limit in KB (e.g., 2000000 for 2GB): " size_limit
    fi

    validate_username "$username"
    validate_size_limit "$size_limit"

    create_linux_user "$username" "$password"
    create_user_directory "$username"
    setup_file_manager "$username" "$password"

    # Assign unique port
    port=$(assign_port "$username")

    setup_nginx_port "$username" "$port"
    monitor_and_enforce_limits "$username" "$size_limit"

    local user_dir="$USER_DIR_BASE/$username"
    local dir_size=$(du -sh "$user_dir" | cut -f1)

    echo ""
    echo "========================================="
    echo "User Hosting Setup Summary"
    echo "========================================="
    echo "Username:          $username"
    echo "Dashboard URL:     http://<your-server-ip>:$port/filemanager"
    echo "Directory:         $user_dir/public_html"
    echo "Port Assigned:     $port"
    echo "Size Limit:        $size_limit KB"
    echo "Current Size:      $dir_size"
    echo "========================================="
}

main "$@"






=========================================
User Hosting Setup Summary
=========================================
Username:          gamish
Dashboard URL:     http://<your-server-ip>:8001/filemanager
Directory:         /home/users/gamish/public_html
Port Assigned:     8001
Size Limit:        2000000 KB
Current Size:      11M
=========================================
