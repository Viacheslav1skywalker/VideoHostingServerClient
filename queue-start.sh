#!/bin/bash

NUM_WORKERS=9                   # Количество воркеров
QUEUE_NAMES="video-processing,move-to-s3-worker,video-splicing"     # Имена очередей через запятую                 # Время перед повтором (сек)
TIMEOUT=0                   
SLEEP=3                         

echo "Запускаем $NUM_WORKERS воркеров для очередей: $QUEUE_NAMES"

# Функция для запуска воркера
start_worker() {
    local queue_name=$1
    local worker_num=$2
    
    nohup php artisan queue:work \
        --queue="$queue_name" \
        --timeout="$TIMEOUT" \
}


for queue in "${queues[@]}"; do
    queue=$(echo "$queue" | xargs)  # Удаляем лишние пробелы
    
    for (( i=1; i<=NUM_WORKERS; i++ )); do
        start_worker "$queue" "$i"
        echo "  ▶ Воркер #$i для очереди '$queue' (PID: $!)"
        sleep "$SLEEP"  # Небольшая пауза между запусками
    done
done

echo "✅ Все воркеры успешно запущены"
echo "📝 Логи пишутся в: $LOG_DIR/worker_*.log"