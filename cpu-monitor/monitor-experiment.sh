#!/bin/bash

# Função para ler o estado atual do /proc/stat
read_cpu_stat() {
    local cpu_line
    read -r cpu_line < /proc/stat
    echo "$cpu_line"
}

# Função para calcular o uso de CPU entre duas leituras
calculate_cpu_usage() {
    # Captura a leitura inicial
    cpu_stat_1=$(read_cpu_stat)
    set -- $cpu_stat_1
    prev_user=$2; prev_nice=$3; prev_system=$4; prev_idle=$5
    prev_iowait=${6:-0}; prev_irq=${7:-0}; prev_softirq=${8:-0}; prev_steal=${9:-0}

    prev_idle=$((prev_idle + prev_iowait))
    prev_total=$((prev_user + prev_nice + prev_system + prev_idle + prev_irq + prev_softirq + prev_steal))

    # Exibe a leitura inicial
    echo "Captura inicial do CPU:"
    echo "Usuário: $prev_user, Nice: $prev_nice, Sistema: $prev_system, Idle: $prev_idle"

    # Aguardar a tecla para captura final
    echo "Inicie o experimento e pressione qualquer tecla para capturar o estado final do CPU."
    read -n 1 -s

    # Captura a leitura após o experimento
    cpu_stat_2=$(read_cpu_stat)
    set -- $cpu_stat_2
    user=$2; nice=$3; system=$4; idle=$5
    iowait=${6:-0}; irq=${7:-0}; softirq=${8:-0}; steal=${9:-0}

    curr_idle=$((idle + iowait))
    curr_total=$((user + nice + system + curr_idle + irq + softirq + steal))

    # Exibe a leitura final
    echo "Captura final do CPU:"
    echo "Usuário: $user, Nice: $nice, Sistema: $system, Idle: $curr_idle"

    # Cálculo dos deltas
    delta_total=$((curr_total - prev_total))
    delta_idle=$((curr_idle - prev_idle))

    # Cálculo de CPU = (1 - idle/total) * 100
    if [ "$delta_total" -gt 0 ]; then
        cpu=$(echo "scale=2; 100 * (1 - ($delta_idle / $delta_total))" | bc -l)
    else
        cpu=0
    fi

    echo "$cpu"
}

# Captura inicial
echo "Pressione qualquer tecla para capturar o estado inicial do CPU."
read -n 1 -s
initial_cpu_usage=$(calculate_cpu_usage)

# Exibe a média de uso do CPU entre as capturas
echo "Uso médio de CPU durante o experimento: $initial_cpu_usage%"
