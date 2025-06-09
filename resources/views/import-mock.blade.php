<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Импорт - прослушивание событий</title>
    @vite('resources/js/app.js')
</head>
<body>
<h1>Импорт (прослушивание событий)</h1>

<button id="startImport">Запустить импорт (мок)</button>

<p>Прогресс: <span id="progress">0</span> из <span id="total">0</span></p>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Добавляем слушатель события через нашу обёртку
        window.EchoAddListener('import-progress', '.ImportProgressUpdated', (data) => {
            console.log('Событие получено:', data.message);

            const progressElem = document.getElementById('progress');
            if (progressElem && data.processed !== undefined && data.total !== undefined) {
                progressElem.innerText = `${data.processed} из ${data.total}`;
            }
        });

        // Кнопка для теста отправки запроса на сервер
        document.getElementById('startImport').addEventListener('click', () => {
            fetch('/import-mock/start', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => console.log('Импорт запущен:', data))
                .catch(err => console.error('Ошибка запуска импорта:', err));
        });
    });
</script>
</body>
</html>
