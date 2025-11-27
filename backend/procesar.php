<?php
// --- LÓGICA PHP MODIFICADA ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Rutas
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('DATA_DIR', __DIR__ . '/data/');
define('JSON_FILE', DATA_DIR . 'products.json');

// Crear carpetas si no existen
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0777, true);

$expectedHeaders = ['id', 'nombre', 'descripcion', 'precio', 'img', 'estoc', 'categoria'];
$messages = [];
$errors = [];
$importedCount = 0;
$rowErrors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Validar permisos
    if (!is_writable(UPLOAD_DIR)) $errors[] = "Error: La carpeta 'uploads' no tiene permisos de escritura.";
    if (!is_writable(DATA_DIR)) $errors[] = "Error: La carpeta 'data' no tiene permisos de escritura.";

    // 2. Subir fichero
    if (empty($errors)) {
        if (!isset($_FILES['arxiuCsv']) || $_FILES['arxiuCsv']['error'] != UPLOAD_ERR_OK) {
            $errors[] = "Error al subir el archivo. Código: " . ($_FILES['arxiuCsv']['error'] ?? 'Desconocido');
        } else {
            $fileTmpPath = $_FILES['arxiuCsv']['tmp_name'];
            $nomOriginal = basename($_FILES['arxiuCsv']['name']);
            $extensio = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

            if ($extensio != 'csv') {
                $errors[] = "Error: Solo se admiten archivos .csv";
            } else {
                $uploadFilePath = UPLOAD_DIR . 'import_' . time() . '.csv';
                if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                    $messages[] = "Archivo subido correctamente.";
                } else {
                    $errors[] = "No se pudo guardar el archivo en uploads.";
                }
            }
        }
    }

    // 3. Procesar CSV
    if (empty($errors) && isset($uploadFilePath)) {
        
        // A) CARGAR PRODUCTOS EXISTENTES PARA EVITAR DUPLICADOS
        $productes = [];
        $idsExistentes = [];

        if (file_exists(JSON_FILE)) {
            $jsonContent = file_get_contents(JSON_FILE);
            $decoded = json_decode($jsonContent, true);
            if (isset($decoded['products']) && is_array($decoded['products'])) {
                $productes = $decoded['products'];
                // Crear mapa de IDs existentes
                foreach ($productes as $p) {
                    $idsExistentes[] = $p['id'];
                }
            }
        }

        if (($handle = fopen($uploadFilePath, "r")) !== FALSE) {
            $headerRow = fgetcsv($handle, 1000, ",");

            if ($headerRow) {
                // Limpieza BOM y comillas
                $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerRow[0]);
                if (count($headerRow) === 1)
                    $headerRow = str_getcsv(trim($headerRow[0], '"'), ",");

                $headers = array_map('strtolower', array_map('trim', $headerRow));
                $headerMap = array_flip($headers);

                // Validar columnas
                $missing = array_diff($expectedHeaders, $headers);
                if (!empty($missing)) {
                    $errors[] = "Faltan columnas: " . implode(', ', $missing);
                } else {
                    $rowNum = 1;
                    $nuevosAgregados = 0;

                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $rowNum++;
                        // Fix filas con formato incorrecto
                        if (count($row) === 1) {
                            $row = str_getcsv(trim($row[0], '"'), ",");
                            foreach ($row as $k => $v)
                                $row[$k] = str_replace('""', '"', $v);
                        }

                        if (implode('', $row) == '') continue; // Saltar vacías

                        if (count($row) != count($headers)) {
                            $rowErrors[] = "Fila $rowNum ignorada: Columnas insuficientes.";
                            continue;
                        }

                        // Mapear datos
                        $id = (int) $row[$headerMap['id']];
                        $nom = trim($row[$headerMap['nombre']]);
                        $preu = (float) str_replace(',', '.', $row[$headerMap['precio']]);
                        $estoc = (int) $row[$headerMap['estoc']];
                        $categoria = trim($row[$headerMap['categoria']]);

                        // B) VALIDAR DUPLICADOS
                        if (in_array($id, $idsExistentes)) {
                            $rowErrors[] = "Fila $rowNum ignorada: El ID $id ya existe en el catálogo.";
                            continue;
                        }

                        // Validaciones de datos
                        if (empty($nom) || $preu <= 0 || $estoc < 0) {
                            $rowErrors[] = "Fila $rowNum ignorada: Datos inválidos ($nom).";
                            continue;
                        }

                        // Agregar producto nuevo
                        $productes[] = [
                            'id' => $id,
                            'sku' => 'JUEGO-' . $id,
                            'nom' => $nom,
                            'descripcio' => trim($row[$headerMap['descripcion']]),
                            'img' => trim($row[$headerMap['img']]),
                            'preu' => $preu,
                            'estoc' => $estoc,
                            'categoria' => $categoria
                        ];
                        
                        $idsExistentes[] = $id; // Actualizar lista de IDs locales
                        $nuevosAgregados++;
                    }
                    fclose($handle);
                }
            } else {
                $errors[] = "El archivo CSV está vacío o no se puede leer.";
            }
        }

        // 4. Guardar JSON (Solo si hubo cambios)
        if (empty($errors)) {
            $jsonData = ['products' => $productes];
            if (file_put_contents(JSON_FILE, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $importedCount = isset($nuevosAgregados) ? $nuevosAgregados : 0;
                $totalCount = count($productes);
                $messages[] = "✅ ¡Proceso completado! Nuevos añadidos: $importedCount. Total en catálogo: $totalCount.";
            } else {
                $errors[] = "Error al escribir en products.json";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV - MOKeys</title>

    <link rel="stylesheet" href="/css/estilos.css">
    <link rel="stylesheet" href="/css/formulario.css">

    <link rel="icon" type="img/png" href="/img/icono.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* Estilos específicos para los mensajes de este proceso */
        .resumen-caja {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .resumen-exito {
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
            color: #2e7d32;
        }

        .resumen-error {
            background-color: #ffebee;
            border: 1px solid #ef9a9a;
            color: #c62828;
        }

        .resumen-caja ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }

        .info-columnas {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 10px;
        }

        .input-fichero {
            background: #f9f9f9;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="pagina-contacto">

    <div class="aviso-slider">
        <div class="aviso-slider-content">
            <div>Clave de juegos con un 70% de descuento</div>
            <div>¡Nuevas ofertas cada día!</div>
            <div>¡Las claves más baratas de la web!</div>
            <div>Clave de juegos con un 70% de descuento</div>
        </div>
    </div>

    <header>
        <img src="/img/imagencolor.webp" alt="Logo" />

        <a href="#" class="users"></a>
        <a href="#" class="carro"></a>

        <nav>
            <ul class="enlaces_navegacion">
                <li><a href="/index.html">Inicio</a></li>
                <li><a href="#">Comprar</a></li>
                <li><a href="#">Vender</a></li>
                <li><a href="/contacto.html">Contacto</a></li>
            </ul>
        </nav>
        <form class="busqueda" action="#" method="get">
            <input type="text" placeholder="Buscar producto..." name="q" />
        </form>
    </header>

    <main class="contenedor-formulario-principal">
        <div class="caja-formulario" style="max-width: 700px;">
            <h1>Importación de Productos</h1>
            <p class="subtitulo-form">Actualiza el catálogo subiendo tu archivo CSV.</p>

            <?php if (!empty($messages)): ?>
                <div class="resumen-caja resumen-exito">
                    <strong>Estado:</strong>
                    <ul>
                        <?php foreach ($messages as $msg)
                            echo "<li>" . htmlspecialchars($msg) . "</li>"; ?>
                    </ul>
                    <?php if ($importedCount > 0): ?>
                        <p style="margin-top:10px;">
                            <a href="http://localhost:3002/products" target="_blank" style="color:#2e7d32; font-weight:bold;">
                                Ver JSON generado ➜
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="resumen-caja resumen-error">
                    <strong>⚠️ Errores Críticos:</strong>
                    <ul>
                        <?php foreach ($errors as $err)
                            echo "<li>" . htmlspecialchars($err) . "</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($rowErrors)): ?>
                <div class="resumen-caja resumen-error"
                    style="background-color: #fff3e0; border-color: #ffcc80; color: #e65100;">
                    <strong>⚠️ Advertencias (Filas ignoradas):</strong>
                    <ul style="max-height: 100px; overflow-y: auto;">
                        <?php foreach ($rowErrors as $err)
                            echo "<li>" . htmlspecialchars($err) . "</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="importar_excel.php" method="POST" enctype="multipart/form-data">

                <div class="grupo-input">
                    <label for="arxiuCsv">Seleccionar archivo (.csv):</label>
                    <p class="info-columnas">Columnas requeridas: <?php echo implode(', ', $expectedHeaders); ?></p>

                    <input type="file" name="arxiuCsv" id="arxiuCsv" required accept=".csv" class="input-fichero">
                </div>

                <button type="submit" name="btnSubir" value="Subir" class="btn-enviar">Importar Catálogo</button>
            </form>

        </div>
    </main>

    <footer>
        <div class="footer-contenedor">
            <div class="footer-logo">
                <img src="/img/imagensincolor.png" alt="MOKeys" />
            </div>

            <div class="footer-suscripcion">
                <input type="email" placeholder="Insert your email address here" />
                <button>Comprar ahora</button>
            </div>

            <div class="footer-enlaces">
                <div>
                    <h4>Help</h4>
                    <a href="#">FAQ</a><br />
                    <a href="#">Customer service</a><br />
                    <a href="#">How to guides</a>
                </div>
                <div>
                    <h4>Other</h4>
                    <a href="#">Privacy Policy</a><br />
                    <a href="#">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>