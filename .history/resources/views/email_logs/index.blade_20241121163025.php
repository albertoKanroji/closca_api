<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Logs</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Para el manejo de tabs con Tailwind */
        .tab-active {
            @apply border-b-2 border-blue-500 text-blue-500;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6 text-center">Panel de Logs</h1>

    <!-- Nav Tabs -->
    <div class="flex space-x-4 border-b">
        <button id="tab-logs-email" class="tab-button px-4 py-2 text-gray-600 hover:text-blue-500 tab-active">Logs Email</button>
        <button id="tab-logs-busqueda" class="tab-button px-4 py-2 text-gray-600 hover:text-blue-500">Logs Búsqueda</button>
        <button id="tab-buscador-dmg" class="tab-button px-4 py-2 text-gray-600 hover:text-blue-500">Buscador DMG</button>
    </div>

    <!-- Tab Content -->
    <div id="content-logs-email" class="tab-content mt-4">
        <h2 class="text-xl font-semibold mb-4">Logs de Emails</h2>
        <table class="table-auto w-full bg-white shadow-md rounded border">
            <thead class="bg-blue-100">
            <tr>
                <th class="px-4 py-2">No.</th>
                <th class="px-4 py-2">ID Auto</th>
                <th class="px-4 py-2">Marca</th>
                <th class="px-4 py-2">VIN</th>
                <th class="px-4 py-2">Fecha Ingreso</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Modelo</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($logs as $index => $log)
                <tr class="border-b hover:bg-blue-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $log->id_auto }}</td>
                    <td class="px-4 py-2">{{ $log->id_marca }}</td>
                    <td class="px-4 py-2">{{ $log->vin }}</td>
                    <td class="px-4 py-2">{{ $log->f_ingreso }}</td>
                    <td class="px-4 py-2">Recibido</td>
                    <td class="px-4 py-2">{{ $log->modelo }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div id="content-logs-busqueda" class="tab-content mt-4 hidden">
        <h2 class="text-xl font-semibold mb-4">Logs de Búsqueda</h2>
        <p>Aquí puedes agregar contenido o una tabla para los logs de búsqueda.</p>
        <table class="table-auto w-full bg-white shadow-md rounded border">
            <thead class="bg-blue-100">
            <tr>
                <th class="px-4 py-2">No.</th>
                <th class="px-4 py-2">Descripción</th>
                <th class="px-4 py-2">Fecha</th>
                <th class="px-4 py-2">Usuario</th>
            </tr>
            </thead>
            <tbody>
            <!-- Contenido dinámico aquí -->
            </tbody>
        </table>
    </div>

    <div id="content-buscador-dmg" class="tab-content mt-4 hidden">
        <h2 class="text-xl font-semibold mb-4">Buscador DMG</h2>
        <form id="dmgSearchForm" class="flex items-center space-x-4">
            <label for="dmgCode" class="text-gray-700 font-medium">Código DMG:</label>
            <input type="text" id="dmgCode" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ingrese código">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Buscar</button>
        </form>
        <div id="dmgResults" class="mt-4 text-gray-700">
            <p>Resultados aparecerán aquí.</p>
        </div>
    </div>
</div>

<!-- Script para manejar tabs -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Quitar clase activa de todas las tabs y esconder contenido
                tabs.forEach(t => t.classList.remove('tab-active'));
                contents.forEach(c => c.classList.add('hidden'));

                // Activar la tab actual y mostrar su contenido
                tab.classList.add('tab-active');
                const target = tab.id.replace('tab-', 'content-');
                document.getElementById(target).classList.remove('hidden');
            });
        });

        // Buscador DMG
        const dmgSearchForm = document.getElementById('dmgSearchForm');
        dmgSearchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const dmgCode = document.getElementById('dmgCode').value;
            const results = document.getElementById('dmgResults');
            results.innerHTML = `<p>Resultados para: <strong>${dmgCode}</strong></p>`;
        });
    });
</script>
</body>
</html>
