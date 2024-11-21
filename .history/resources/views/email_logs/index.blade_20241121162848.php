<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Logs</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Panel de Logs</h1>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs">
        <li class="active"><a href="#logs-email" data-toggle="tab">Logs Email</a></li>
        <li><a href="#logs-busqueda" data-toggle="tab">Logs Búsqueda</a></li>
        <li><a href="#buscador-dmg" data-toggle="tab">Buscador DMG</a></li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Logs Email -->
        <div id="logs-email" class="tab-pane fade in active">
            <h2 class="mt-4">Logs de Emails</h2>
            <table id="logsTable" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>ID Auto</th>
                    <th>Marca</th>
                    <th>VIN</th>
                    <th>Fecha Ingreso</th>
                    <th>Status</th>
                    <th>Modelo</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($logs as $index => $log)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $log->id_auto }}</td>
                        <td>{{ $log->id_marca }}</td>
                        <td>{{ $log->vin }}</td>
                        <td>{{ $log->f_ingreso }}</td>
                        <td>Recibido</td>
                        <td>{{ $log->modelo }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Logs Búsqueda -->
        <div id="logs-busqueda" class="tab-pane fade">
            <h2 class="mt-4">Logs de Búsqueda</h2>
            <p>Aquí puedes agregar una tabla o contenido relacionado con los logs de búsqueda.</p>
            <!-- Ejemplo de tabla vacía -->
            <table id="logsBusquedaTable" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($logsBusqueda as $index => $log)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $log->vin }}</td>
                        <td>{{ $log->fecha_busqueda }}</td>
                        <td>{{ $log->origen }}</td>
                        <td>{{ $log->usuario }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Buscador DMG -->
        <div id="buscador-dmg" class="tab-pane fade">
            <h2 class="mt-4">Buscador DMG</h2>
            <form id="dmgSearchForm" class="form-inline">
                <div class="form-group">
                    <label for="dmgCode">Código DMG:</label>
                    <input type="text" id="dmgCode" class="form-control" placeholder="Ingrese código">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
            <div id="dmgResults" class="mt-4">
                <p>Resultados aparecerán aquí.</p>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap JS -->
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        // Inicializar tablas
        $('#logsTable').DataTable();
        $('#logsBusquedaTable').DataTable();

        // Buscador DMG
        $('#dmgSearchForm').on('submit', function (e) {
            e.preventDefault();
            const dmgCode = $('#dmgCode').val();
            // Aquí puedes agregar la lógica para buscar el DMG
            $('#dmgResults').html('<p>Resultados para: ' + dmgCode + '</p>');
        });
    });
</script>
</body>
</html>