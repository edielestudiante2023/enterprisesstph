<?php
/**
 * Patcher: Agregar autoguardado localStorage a los 4 formularios KPI
 * Uso: php patch_autoguardado_kpi.php
 */
if (php_sapi_name() !== 'cli') die('Solo CLI.');

$base = dirname(__DIR__, 2);

$modules = [
    ['kpi-limpieza',      'kpi_limpieza',      'kpiLimpiezaForm'],
    ['kpi-residuos',      'kpi_residuos',      'kpiResiduosForm'],
    ['kpi-plagas',        'kpi_plagas',         'kpiPlagasForm'],
    ['kpi-agua-potable',  'kpi_agua_potable',   'kpiAguaPotableForm'],
];

$ok = 0;
$skip = 0;
$err = 0;

echo "=== PATCH: Autoguardado KPI ===\n\n";

foreach ($modules as $m) {
    $slug = $m[0];
    $keyPrefix = $m[1];
    $formId = $m[2];
    $f = $base . '/app/Views/inspecciones/' . $slug . '/form.php';

    if (!file_exists($f)) {
        echo "[ERR] {$f}\n";
        $err++;
        continue;
    }

    $c = file_get_contents($f);

    if (strpos($c, 'saveToLocal') !== false || strpos($c, 'autoguardado') !== false) {
        echo "[SKIP] {$slug} - ya tiene autoguardado\n";
        $skip++;
        continue;
    }

    // 1. Add storageKey PHP variable after $isEdit/$action block
    $storageKeyLine = '$storageKey = $isEdit ? \'' . $keyPrefix . '_draft_\' . $inspeccion[\'id\'] : \'' . $keyPrefix . '_draft_new\';';
    $actionLine = "'/inspecciones/{$slug}/store';";
    $pos = strpos($c, $actionLine);
    if ($pos === false) {
        echo "[ERR] {$slug} - no se encontró línea de action\n";
        $err++;
        continue;
    }
    $nl = strpos($c, "\n", $pos);
    if ($nl === false) { $err++; echo "[ERR] {$slug} - no newline\n"; continue; }
    $closeTag = strpos($c, '?>', $nl);
    if ($closeTag === false) { $err++; echo "[ERR] {$slug} - no close tag\n"; continue; }
    $c = substr($c, 0, $closeTag) . $storageKeyLine . "\n" . substr($c, $closeTag);

    // 2. Add id to <form> tag
    $formTag = '<form method="post" action="<?= $action ?>" enctype="multipart/form-data">';
    $formTagNew = '<form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="' . $formId . '">';
    $c = str_replace($formTag, $formTagNew, $c);

    // 3. Add autoguardado indicator before buttons section
    $buttonMarker = '<!-- Botones -->';
    $indicatorHtml = '    <div id="autoguardadoIndicador" class="text-center text-muted mb-3" style="font-size: 12px; display: none;">' . "\n"
        . '        <i class="fas fa-save"></i> Guardado local: <span id="autoguardadoHora"></span>' . "\n"
        . '    </div>' . "\n\n    ";
    $c = str_replace($buttonMarker, $indicatorHtml . $buttonMarker, $c);

    // 4. Build autoguardado JS using string concatenation (avoiding heredoc with PHP tags)
    $phpOpen = '<' . '?php';
    $phpEnd = '?' . '>';
    $phpEcho = '<' . '?=';

    $autoguardadoJs = "\n"
        . "    // -- Autoguardado localStorage --\n"
        . "    var STORAGE_KEY = '" . $phpEcho . " \$storageKey " . $phpEnd . "';\n"
        . "\n"
        . "    function collectFormData() {\n"
        . "        return {\n"
        . "            id_cliente: document.querySelector('[name=\"id_cliente\"]').value,\n"
        . "            fecha_inspeccion: document.querySelector('[name=\"fecha_inspeccion\"]').value,\n"
        . "            nombre_responsable: document.querySelector('[name=\"nombre_responsable\"]').value,\n"
        . "            indicador: document.querySelector('[name=\"indicador\"]').value,\n"
        . "            cumplimiento: document.querySelector('[name=\"cumplimiento\"]').value,\n"
        . "            timestamp: Date.now()\n"
        . "        };\n"
        . "    }\n"
        . "\n"
        . "    function saveToLocal() {\n"
        . "        var data = collectFormData();\n"
        . "        if (!data.id_cliente && !data.nombre_responsable) return;\n"
        . "        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));\n"
        . "        var hora = new Date().toLocaleTimeString();\n"
        . "        document.getElementById('autoguardadoHora').textContent = hora;\n"
        . "        document.getElementById('autoguardadoIndicador').style.display = '';\n"
        . "    }\n"
        . "\n"
        . "    function restoreFromLocal() {\n"
        . "        var saved = localStorage.getItem(STORAGE_KEY);\n"
        . "        if (!saved) return;\n"
        . "        try {\n"
        . "            var data = JSON.parse(saved);\n"
        . "            if (Date.now() - data.timestamp > 24 * 60 * 60 * 1000) {\n"
        . "                localStorage.removeItem(STORAGE_KEY);\n"
        . "                return;\n"
        . "            }\n"
        . "            Swal.fire({\n"
        . "                title: 'Borrador encontrado',\n"
        . "                text: 'Se encontró un borrador guardado. ¿Desea restaurar los datos?',\n"
        . "                icon: 'question',\n"
        . "                showCancelButton: true,\n"
        . "                confirmButtonColor: '#bd9751',\n"
        . "                confirmButtonText: 'Sí, restaurar',\n"
        . "                cancelButtonText: 'No, empezar de cero'\n"
        . "            }).then(function(result) {\n"
        . "                if (result.isConfirmed) {\n"
        . "                    if (data.fecha_inspeccion) document.querySelector('[name=\"fecha_inspeccion\"]').value = data.fecha_inspeccion;\n"
        . "                    if (data.nombre_responsable) document.querySelector('[name=\"nombre_responsable\"]').value = data.nombre_responsable;\n"
        . "                    if (data.indicador) document.querySelector('[name=\"indicador\"]').value = data.indicador;\n"
        . "                    if (data.cumplimiento) document.querySelector('[name=\"cumplimiento\"]').value = data.cumplimiento;\n"
        . "                    if (data.id_cliente) window._pendingClientRestore = data.id_cliente;\n"
        . "                } else {\n"
        . "                    localStorage.removeItem(STORAGE_KEY);\n"
        . "                }\n"
        . "            });\n"
        . "        } catch (e) {\n"
        . "            localStorage.removeItem(STORAGE_KEY);\n"
        . "        }\n"
        . "    }\n"
        . "\n"
        . "    " . $phpOpen . " if (!\$isEdit): " . $phpEnd . "\n"
        . "    restoreFromLocal();\n"
        . "    " . $phpOpen . " endif; " . $phpEnd . "\n"
        . "\n"
        . "    setInterval(saveToLocal, 30000);\n"
        . "    var debounceTimer;\n"
        . "    document.getElementById('" . $formId . "').addEventListener('input', function() {\n"
        . "        clearTimeout(debounceTimer);\n"
        . "        debounceTimer = setTimeout(saveToLocal, 2000);\n"
        . "    });\n"
        . "\n"
        . "    document.getElementById('" . $formId . "').addEventListener('submit', function() {\n"
        . "        localStorage.removeItem(STORAGE_KEY);\n"
        . "    });\n";

    // Insert before the closing });
    $closingJs = "});\n\nfunction openPhoto";
    $c = str_replace($closingJs, $autoguardadoJs . "});\n\nfunction openPhoto", $c);

    file_put_contents($f, $c);
    echo "[OK] {$slug}\n";
    $ok++;
}

echo "\n=== RESUMEN ===\n";
echo "OK: {$ok}\n";
echo "Skip: {$skip}\n";
echo "Errores: {$err}\n";
echo ($err === 0) ? "COMPLETADO SIN ERRORES.\n" : "HAY ERRORES.\n";
