<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Импорт XLSX через WebSocket</title>
    @vite('resources/js/app.js')
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
<div class="max-w-3xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-bold text-blue-700 mb-6">📥 Импорт XLSX через WebSocket (Reverb)</h1>

    <form id="uploadForm" method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data"
          class="bg-white shadow-md rounded-lg p-6 mb-6">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Выберите XLSX-файл:</label>
            <input type="file" name="file" accept=".xlsx" required
                   class="block w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">🚀 Запустить импорт</button>
    </form>
    <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-600 mb-1">📡 Статус импорта:</p>
        <p id="status" class="text-sm font-medium text-gray-500 italic">Ожидание запуска...</p>
    </div>
    <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-600 mb-1">Прогресс импорта:</p>
        <p class="text-lg font-semibold text-green-600"><span id="progress">0</span> строк</p>
    </div>

    <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-600 mb-1">⏱️ Время выполнения:</p>
        <p id="duration" class="text-lg font-semibold text-indigo-600">-</p>
    </div>

    <div class="bg-white shadow-sm rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">✅ Импортированные строки:</h2>
        <ul id="imported-list" class="max-h-80 overflow-y-auto text-sm space-y-2 list-none">
            <li class="text-gray-400">Ожидание данных...</li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const progressElem = document.getElementById('progress');
        const durationElem = document.getElementById('duration');
        const list = document.getElementById('imported-list');
        const form = document.getElementById('uploadForm');

        const statusElem = document.getElementById('status');
        let lastUpdate = Date.now();
        let statusInterval;

        // Устанавливаем текст статуса и обновляем метку времени
        function setStatus(text) {
            statusElem.textContent = text;
            lastUpdate = Date.now();
        }

        // Проверка активности: если нет событий > 5 сек — пишем, что ждём
        statusInterval = setInterval(() => {
            const now = Date.now();
            if (now - lastUpdate > 5000) {
                statusElem.textContent = '⌛ Ожидание новых данных...';
            }
        }, 3000);

        list.innerHTML = '';

        window.EchoAddListener('import-items', '.ImportItemProcessed', (e) => {
            if (Array.isArray(e.items)) {
                e.items.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = `🟢 ID: ${item.id}, Name: ${item.name}, Date: ${item.date}`;
                    li.className = 'border-b pb-1';
                    list.appendChild(li);
                });

                const currentCount = parseInt(progressElem.textContent, 10) || 0;
                progressElem.textContent = currentCount + e.items.length;

                setStatus(`⏳ Импорт в процессе... (${progressElem.textContent} строк)`);
            }
        });

        window.EchoAddListener('import-items', '.ImportFinished', (e) => {
            durationElem.textContent = `${e.durationInSeconds} сек.`;
            setStatus('✅ Импорт завершён');

            clearInterval(statusInterval); // остановим проверку "зависания"
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            progressElem.textContent = '0';
            durationElem.textContent = '-';
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

                if (!response.ok) {
                    throw new Error(`Ошибка сервера: ${response.status}`);
                }

                // Не ждём json, просто сообщаем о запуске
                alert('✅ Импорт запущен');
            } catch (error) {
                alert('❌ Ошибка при запуске импорта: ' + error.message);
            }
        });
    });
</script>
</body>
</html>
