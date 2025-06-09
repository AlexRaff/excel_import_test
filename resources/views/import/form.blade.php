<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Импорт XLSX с WebSocket</title>
    @vite('resources/js/app.js')
    <style>
        body { font-family: sans-serif; margin: 1rem; }
        #progress { font-weight: bold; }
        #imported-list { max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 0.5rem; list-style: none; }
        #imported-list li { padding: 0.3rem 0; border-bottom: 1px solid #eee; }
        button { margin-bottom: 1rem; }
    </style>
</head>
<body>
<h1>Импорт XLSX через WebSocket (Reverb)</h1>

<form id="uploadForm" method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".xlsx" required>
    <button type="submit">Запустить импорт</button>
</form>

<p>Прогресс: <span id="progress">0</span> строк</p>

<h2>Импортированные строки:</h2>
<ul id="imported-list">
    <li>Ожидание данных...</li>
</ul>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const progressElem = document.getElementById('progress');
        const list = document.getElementById('imported-list');
        const form = document.getElementById('uploadForm');

        // Убрать плейсхолдер
        list.innerHTML = '';

        // Подписка на события от сервера
        window.EchoAddListener('import-items', '.ImportItemProcessed', (e) => {
            // Добавляем строку в список
            const li = document.createElement('li');
            li.textContent = `ID: ${e.id}, Name: ${e.name}, Date: ${e.date}`;
            list.appendChild(li);

            // Обновляем прогресс
            const currentCount = parseInt(progressElem.textContent) || 0;
            progressElem.textContent = currentCount + 1;
        });

        // Отправка формы через AJAX, чтобы не перезагружать страницу
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            progressElem.textContent = '0';
            list.innerHTML = '';

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });

                if (!response.ok) throw new Error('Ошибка сети');

                const data = await response.json();

                alert('Импорт запущен');
            } catch (error) {
                alert('Ошибка при запуске импорта: ' + error.message);
            }
        });
    });
</script>

</body>
</html>
