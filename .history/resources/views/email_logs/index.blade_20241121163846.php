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
                        @foreach ($logsBusqueda as $index1 => $logB)
                        <tr>
                            <td>{{ $index1 + 1 }}</td>
                            <td>{{ $logB->vin }}</td>
                            <td>{{ $logB->fecha_busqueda }}</td>
                            <td>{{ $logB->origen }}</td>
                            <td>{{ $logB->usuario }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Buscador DMG -->
            <div id="buscador-dmg" class="tab-pane fade">
    <h2 class="mt-4">Buscador DMG</h2>
    <form id="dmgSearchForm" class="mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="vin" class="form-label">Buscar VIN:</label>
            </div>
            <div class="col-auto">
                <input type="text" id="vin" name="vin" class="form-control" placeholder="Ingrese VIN">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </div>
    </form>

    <!-- Área para mostrar resultados -->
    <div id="dmgResults">
        <!-- Resultados se cargarán aquí dinámicamente -->
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Manejar el envío del formulario
        $('#dmgSearchForm').on('submit', function (e) {
            e.preventDefault(); // Evitar recarga de la página

            // Obtener el valor del VIN
            const vin = $('#vin').val();

            if (!vin) {
                $('#dmgResults').html('<div class="alert alert-danger">Por favor, ingrese un VIN.</div>');
                return;
            }

            // Realizar la solicitud AJAX
            $.ajax({
                url: "{{ route('email.logs.index') }}", // Ruta del servidor
                method: "GET",
                data: { vin: vin },
                beforeSend: function () {
                    $('#dmgResults').html('<div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div>');
                },
                success: function (response) {
                    if (response.error) {
                        $('#dmgResults').html(`<div class="alert alert-danger">${response.error}</div>`);
                    } else if (response.dmgDetalles && response.dmgDetalles.length > 0) {
                        let table = `
                            <h2 class="mt-4">Resultados para el VIN: ${vin}</h2>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">ID DMG</th>
                                            <th scope="col">Comentario</th>
                                            <th scope="col">Código DMG</th>
                                            <th scope="col">Creado</th>
                                            <th scope="col">Actualizado</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                        response.dmgDetalles.forEach(dmg => {
                            table += `
                                <tr>
                                    <td>${dmg.id}</td>
                                    <td>${dmg.comentario}</td>
                                    <td>${dmg.dmg_codigo}</td>
                                    <td>${dmg.created_at}</td>
                                    <td>${dmg.updated_at}</td>
                                </tr>`;
                        });
                        table += `
                                    </tbody>
                                </table>
                            </div>`;
                        $('#dmgResults').html(table);
                    } else {
                        $('#dmgResults').html(`<div class="alert alert-info">No se encontraron detalles de daño para el VIN: ${vin}</div>`);
                    }
                },
                error: function () {
                    $('#dmgResults').html('<div class="alert alert-danger">Ocurrió un error al realizar la búsqueda. Intente nuevamente.</div>');
                }
            });
        });
    });
</script>
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
    $(document).ready(function() {
        $('#logsTable').DataTable();
    });
    $(document).ready(function() {
        $('#logsBusquedaTable').DataTable();
    });
    $(document).ready(function() {



        // Buscador DMG
        $('#dmgSearchForm').on('submit', function(e) {
            e.preventDefault();
            const dmgCode = $('#dmgCode').val();
            // Aquí puedes agregar la lógica para buscar el DMG
            $('#dmgResults').html('<p>Resultados para: ' + dmgCode + '</p>');
        });
    });
    </script>
</body>

</html>
