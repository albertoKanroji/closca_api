<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Emails</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Logs de Emails</h1>

    <!-- Tabla de logs -->
    <table id="logsTable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>No.</th>
            <th>ID Auto</th>
            <th>VIN</th>
            <th>Fecha Ingreso</th>
            <th>Fecha Salida</th>
            <th>Modelo</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($logs as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $log->id_auto }}</td>
                <td>{{ $log->vin }}</td>
                <td>{{ $log->f_ingreso }}</td>
                <td>{{ $log->f_salida }} </td>
                <td>{{ $log->modelo }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap JS -->
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        $('#logsTable').DataTable();
    });
</script>
</body>
</html>
