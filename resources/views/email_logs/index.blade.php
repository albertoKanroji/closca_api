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
            <th>Correo del remitente</th>
            <th>Fecha de recepción</th>
            <th>Nombre del archivo</th>
            <th>Tamaño del archivo</th>
            <th>Clave</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($logs as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $log->sender_email }}</td>
                <td>{{ $log->received_at }}</td>
                <td>{{ $log->file_name }}</td>
                <td>{{ number_format($log->file_size / 1024, 2) }} KB</td>
                <td>{{ $log->key }}</td>
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
