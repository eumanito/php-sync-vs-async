#!/bin/bash

INTERVAL=1

read_cpu_stat() {
    local cpu_line
    read -r cpu_line < /proc/stat
    echo "$cpu_line"
}

get_cpu_usage() {
    
    # Leitura inicial
    cpu_stat_1=$(read_cpu_stat)
    set -- $cpu_stat_1
    prev_user=$2; prev_nice=$3; prev_system=$4; prev_idle=$5
    prev_iowait=${6:-0}; prev_irq=${7:-0}; prev_softirq=${8:-0}; prev_steal=${9:-0}

    prev_idle=$((prev_idle + prev_iowait))
    prev_total=$((prev_user + prev_nice + prev_system + prev_idle + prev_irq + prev_softirq + prev_steal))

    sleep "$INTERVAL"

    # Leitura após o 1s
    cpu_stat_2=$(read_cpu_stat)
    set -- $cpu_stat_2
    user=$2; nice=$3; system=$4; idle=$5
    iowait=${6:-0}; irq=${7:-0}; softirq=${8:-0}; steal=${9:-0}

    curr_idle=$((idle + iowait))
    curr_total=$((user + nice + system + curr_idle + irq + softirq + steal))

    # Deltas
    delta_total=$((curr_total - prev_total))
    delta_idle=$((curr_idle - prev_idle))

    # CPU = (1 - idle/total) * 100
    if [ "$delta_total" -gt 0 ]; then
        cpu=$(echo "scale=2; 100 * (1 - ($delta_idle/$delta_total))" | bc -l)
    else
        cpu=0
    fi

    echo "$cpu"
}

while true; do
    echo "CPU: $(get_cpu_usage)%"
done
