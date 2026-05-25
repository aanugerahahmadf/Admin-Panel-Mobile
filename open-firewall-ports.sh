#!/bin/bash
# Open required ports for development on Linux/WSL
set -e

PORTS=(8000 5000 3306)

echo "Opening ports: ${PORTS[*]}"

for port in "${PORTS[@]}"; do
    if command -v ufw &>/dev/null; then
        sudo ufw allow "$port"/tcp 2>/dev/null && echo "  Port $port opened via ufw"
    elif command -v iptables &>/dev/null; then
        sudo iptables -A INPUT -p tcp --dport "$port" -j ACCEPT 2>/dev/null && echo "  Port $port opened via iptables"
    else
        echo "  WARNING: Could not open port $port (neither ufw nor iptables found)"
    fi
done

echo "Done. Run 'ufw status' or 'iptables -L' to verify."
