<?php
/**
 * @var array $registros
 */
?>
<div class="container-fluid px-3 mt-2">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas fa-hard-hat"></i> Proveedores de Servicio</h6>
        <a href="<?= base_url('/inspecciones/proveedor-servicio/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;">
            <i class="fas fa-plus"></i> Nuevo
        </a>
    </div>

    <div class="table-responsive">
    <table id="tablaProveedores" class="table table-sm table-hover" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Razón Social</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $i => $r):
            $tipo = $r['tipo_servicio'] === 'Otro' ? ($r['tipo_servicio_otro'] ?: 'Otro') : $r['tipo_servicio'];
        ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($r['nombre_cliente'] ?? '') ?></td>
            <td><?= esc($tipo) ?></td>
            <td><?= esc($r['razon_social']) ?></td>
            <td>
                <span class="badge toggle-estado" data-id="<?= $r['id'] ?>"
                    style="cursor:pointer; background:<?= $r['estado'] === 'activo' ? '#28a745' : '#6c757d' ?>;">
                    <?= $r['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                </span>
            </td>
            <td>
                <a href="<?= base_url('/inspecciones/proveedor-servicio/edit/' . $r['id']) ?>"
                    class="btn btn-xs btn-outline-dark" style="padding:2px 7px;font-size:12px;" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <button class="btn btn-xs btn-outline-danger btn-del"
                    data-id="<?= $r['id'] ?>" data-nombre="<?= esc($r['razon_social']) ?>"
                    style="padding:2px 7px;font-size:12px;">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#tablaProveedores').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
        pageLength: 25,
        order: [[4, 'asc'], [1, 'asc']],
        columnDefs: [{ orderable: false, targets: [0, 5] }],
    });

    // Toggle estado (AJAX)
    $('#tablaProveedores').on('click', '.toggle-estado', function() {
        var badge = this;
        var id = badge.dataset.id;
        Swal.fire({
            title: 'Cambiar estado',
            text: '¿Cambiar estado del proveedor?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1c2437',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= base_url('/inspecciones/proveedor-servicio/toggle/') ?>' + id, {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function(resp) {
                if (resp.success) {
                    badge.textContent = resp.estado === 'activo' ? 'Activo' : 'Inactivo';
                    badge.style.background = resp.estado === 'activo' ? '#28a745' : '#6c757d';
                }
            }, 'json');
        });
    });

    // Eliminar
    $('#tablaProveedores').on('click', '.btn-del', function() {
        var id = this.dataset.id, nombre = this.dataset.nombre;
        confirmarEliminar('<?= base_url('/inspecciones/proveedor-servicio/delete/') ?>' + id, nombre);
    });
});

function confirmarEliminar(url, nombre) {
    var ops = ['+', '-', 'x'];
    var op = ops[Math.floor(Math.random() * ops.length)];
    var a, b, respuesta;
    if (op === '+') { a = Math.floor(Math.random()*20)+1; b = Math.floor(Math.random()*20)+1; respuesta = a+b; }
    else if (op === '-') { a = Math.floor(Math.random()*20)+10; b = Math.floor(Math.random()*a); respuesta = a-b; }
    else { a = Math.floor(Math.random()*9)+2; b = Math.floor(Math.random()*9)+2; respuesta = a*b; }
    Swal.fire({
        title: 'Eliminar proveedor',
        html: '<p style="color:#666;font-size:14px;">Se eliminará <strong>' + nombre + '</strong>.<br>Para confirmar, resuelve:</p>' +
              '<div style="font-size:24px;font-weight:700;color:#1c2437;margin:10px 0;">' + a + ' ' + op + ' ' + b + ' = ?</div>',
        input: 'number', inputPlaceholder: 'Tu respuesta', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#dc3545',
        confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar',
        inputValidator: function(value) {
            if (!value && value !== '0') return 'Debes ingresar un número';
            if (parseInt(value) !== respuesta) return 'Respuesta incorrecta.';
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(url, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' }, function() {
                window.location.href = '<?= base_url('/inspecciones/proveedor-servicio') ?>';
            });
        }
    });
}
</script>
