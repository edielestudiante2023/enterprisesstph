<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Reporte</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
  <nav style="background-color: white; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div class="container">
      <a href="<?= base_url('/reportList') ?>" class="btn btn-secondary">Volver a la lista</a>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h2>Editar Reporte</h2>
      </div>
      <div class="card-body">
        <?php if (session()->getFlashdata('msg')) : ?>
          <div class="alert alert-warning">
            <?= session()->getFlashdata('msg') ?>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('/editReportPost/' . $report['id_reporte']) ?>" method="post" enctype="multipart/form-data">
          <!-- Título del Reporte -->
          <div class="form-group">
            <label for="titulo_reporte">Título del Reporte:</label>
            <input type="text" class="form-control" id="titulo_reporte" name="titulo_reporte" value="<?= $report['titulo_reporte'] ?>" required>
          </div>

          <!-- Tipo de Documento -->
          <div class="form-group">
            <label for="search_detailreport">Buscar Tipo de Documento:</label>
            <input type="text" class="form-control search-field" id="search_detailreport" data-target="#id_detailreport" placeholder="Escribe para buscar...">
          </div>
          <div class="form-group">
            <label for="id_detailreport">Tipo de Documento:</label>
            <select class="form-control" id="id_detailreport" name="id_detailreport" required>
              <?php foreach ($details as $detail) : ?>
                <option value="<?= $detail['id_detailreport'] ?>" <?= $detail['id_detailreport'] == $report['id_detailreport'] ? 'selected' : '' ?>>
                  <?= $detail['detail_report'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tipo de Reporte -->
          <div class="form-group">
            <label for="search_report_type">Buscar Tipo de Reporte:</label>
            <input type="text" class="form-control search-field" id="search_report_type" data-target="#id_report_type" placeholder="Escribe para buscar...">
          </div>
          <div class="form-group">
            <label for="id_report_type">Tipo de Reporte:</label>
            <select class="form-control" id="id_report_type" name="id_report_type" required>
              <?php foreach ($reportTypes as $type) : ?>
                <option value="<?= $type['id_report_type'] ?>" <?= $type['id_report_type'] == $report['id_report_type'] ? 'selected' : '' ?>>
                  <?= $type['report_type'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Archivo Actual -->
          <div class="form-group">
            <label>Archivo Actual:</label>
            <a href="<?= $report['enlace'] ?>" target="_blank" class="btn btn-info">Ver Archivo</a>
          </div>

          <!-- Subir Nuevo Archivo -->
          <div class="form-group">
            <label for="archivo">Subir Nuevo Archivo (Opcional):</label>
            <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
          </div>

          <!-- Estado -->
          <div class="form-group">
            <label for="search_estado">Buscar Estado:</label>
            <input type="text" class="form-control search-field" id="search_estado" data-target="#estado" placeholder="Escribe para buscar...">
          </div>
          <div class="form-group">
            <label for="estado">Estado:</label>
            <select class="form-control" id="estado" name="estado" required>
              <option value="ABIERTO" <?= $report['estado'] == 'ABIERTO' ? 'selected' : '' ?>>ABIERTO</option>
              <option value="GESTIONANDO" <?= $report['estado'] == 'GESTIONANDO' ? 'selected' : '' ?>>GESTIONANDO</option>
              <option value="CERRADO" <?= $report['estado'] == 'CERRADO' ? 'selected' : '' ?>>CERRADO</option>
            </select>
          </div>

          <!-- Observaciones -->
          <div class="form-group">
            <label for="observaciones">Observaciones:</label>
            <textarea class="form-control" id="observaciones" name="observaciones"><?= $report['observaciones'] ?></textarea>
          </div>

          <!-- Cliente -->
          <div class="form-group">
            <label for="search_cliente">Buscar Cliente:</label>
            <input type="text" class="form-control search-field" id="search_cliente" data-target="#id_cliente" placeholder="Escribe para buscar...">
          </div>
          <div class="form-group">
            <label for="id_cliente">Cliente:</label>
            <select class="form-control" id="id_cliente" name="id_cliente" required>
              <?php foreach ($clients as $client) : ?>
                <option value="<?= $client['id_cliente'] ?>" <?= $client['id_cliente'] == $report['id_cliente'] ? 'selected' : '' ?>>
                  <?= $client['nombre_cliente'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Actualizar Reporte</button>
        </form>
      </div>
    </div>
  </div>

  <footer class="text-center mt-5">
    <p>© 2025 Cycloid Talent SAS - Todos los derechos reservados</p>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.search-field').on('input', function () {
        var term = $(this).val().toLowerCase();
        var targetSelect = $($(this).data('target'));
        targetSelect.find('option').each(function () {
          var text = $(this).text().toLowerCase();
          if (text.indexOf(term) === -1) {
            $(this).hide();
          } else {
            $(this).show();
          }
        });
      });
    });
  </script>
</body>

</html>
