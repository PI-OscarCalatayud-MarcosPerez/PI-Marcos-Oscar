<?php
// Mostramos todos los errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. DEFINICIÓN DE RUTAS
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('DATA_DIR', __DIR__ . '/../data/');
define('JSON_FILE', DATA_DIR . 'products.json');

// Definimos las columnas que ESPERAMOS encontrar
$expectedHeaders = [
    'id', 'nombre', 'descripcion', 'precio', 'img', 'estoc', 'categoria'
];

// Arrays para la comunicación con el usuario
$messages = [];
$errors = [];
$importedCount = 0;
$rowErrors = [];

// 3. GESTIÓN DE LA PETICIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- PASO 1: Validar permisos (sin cambios) ---
    if (!is_dir(UPLOAD_DIR) || !is_writable(UPLOAD_DIR)) {
        $errors[] = "Error: La carpeta 'uploads' ('" . UPLOAD_DIR . "') no existe o no tiene permisos de escritura.";
    }
    if (!is_dir(DATA_DIR) || !is_writable(DATA_DIR)) {
        $errors[] = "Error: La carpeta 'data' ('" . DATA_DIR . "') no existe o no tiene permisos de escritura.";
    }

    // --- PASO 2: Recibir y guardar el fichero (sin cambios) ---
    if (count($errors) == 0) {
        if (!isset($_FILES['arxiuCsv'])) {
            $errors[] = "Error: No se ha recibido ningún fichero. Esto puede deberse a que el fichero es demasiado grande (supera 'post_max_size' de PHP).";
        } elseif ($_FILES['arxiuCsv']['error'] != UPLOAD_ERR_OK) {
            $errors[] = "Error en la subida del fichero. Código de error PHP: " . $_FILES['arxiuCsv']['error'];
        } else {
            $fileTmpPath = $_FILES['arxiuCsv']['tmp_name'];
            $nomOriginal = basename($_FILES['arxiuCsv']['name']);
            $extensio = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

            if ($extensio != 'csv') {
                $errors[] = "Error: El fichero debe ser de tipo .csv. Has subido un '." . $extensio . "'";
            } else {
                $nomFitxerUnic = 'import_' . time() . '.csv';
                $uploadFilePath = UPLOAD_DIR . $nomFitxerUnic;

                if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                    $messages[] = "Fichero '{$nomOriginal}' subido y guardado como '{$nomFitxerUnic}'.";
                } else {
                    $errors[] = "No se ha podido mover el fichero subido a 'uploads'.";
                }
            }
        }
    }

    // --- PASO 3, 4, 5: Procesar el fichero (LÓGICA CSV) ---
    if (count($errors) == 0) {
        $productes = [];
        $formatoCorregido = false; // Variable para saber si el CSV está mal
        
        if (($handle = fopen($uploadFilePath, "r")) !== FALSE) {
            
            // --- Validar cabeceras ---
            $headerRow = fgetcsv($handle, 1000, ","); 
            
            if ($headerRow === FALSE) {
                $errors[] = "No se ha podido leer la cabecera del CSV o el fichero está vacío.";
            } else {
                
                // !! MEJORA "ANTI-COMILLAS" !!
                // Comprobamos si fgetcsv solo ha leído 1 columna (porque todo estaba entre "...")
                if (count($headerRow) === 1) {
                    $formatoCorregido = true; // Marcamos que el CSV está mal
                    // Quitamos las comillas del principio y final de la línea
                    $lineaLimpia = trim($headerRow[0], '"');
                    // Volvemos a procesar esa línea limpia como un CSV
                    $headerRow = str_getcsv($lineaLimpia, ",");
                }
                
                // MEJORA: Limpiamos el BOM (caracter invisible) de la primera columna
                $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerRow[0]);

                // Limpiamos y mapeamos las cabeceras
                $headers = array_map('strtolower', array_map('trim', $headerRow));
                
                $headerMap = [];
                $columnaIndex = 0;
                foreach ($headers as $headerName) {
                    $headerMap[$headerName] = $columnaIndex;
                    $columnaIndex++;
                }

                $missing = [];
                foreach ($expectedHeaders as $expected) {
                    if (!isset($headerMap[$expected])) {
                        $missing[] = $expected;
                    }
                }

                if (count($missing) > 0) {
                    $errors[] = "Cabeceras incorrectas. Faltan columnas: " . implode(', ', $missing);
                } else {
                    
                    // --- PASO 4: Validar Datos (Leemos el resto de filas) ---
                    $rowNum = 1; 
                    
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $rowNum++;

                        // !! MEJORA "ANTI-COMILLAS" !!
                        // Si detectamos el formato erróneo, lo arreglamos en cada fila
                        if ($formatoCorregido && count($row) === 1) {
                            $lineaLimpia = trim($row[0], '"');
                            $row = str_getcsv($lineaLimpia, ",");
                            
                            // fgetcsv maneja las comillas dobles ("") de las descripciones,
                            // pero str_getcsv no lo hace igual. Las arreglamos:
                            foreach ($row as $k => $v) {
                                $row[$k] = str_replace('""', '"', $v);
                            }
                        }

                        if (implode('', $row) == '') {
                            continue;
                        }

                        // Comprobamos que el número de columnas coincida (evita errores)
                        if (count($row) != count($headers)) {
                            $rowErrors[] = "Fila $rowNum: El número de columnas no coincide con la cabecera. Fila ignorada.";
                            continue;
                        }

                        // Leemos todas las dadas
                        $id = (int)$row[$headerMap['id']];
                        $nom = trim($row[$headerMap['nombre']]);
                        $preu_raw = $row[$headerMap['precio']];
                        $preu = (float)str_replace(',', '.', $preu_raw);
                        $estoc_raw = $row[$headerMap['estoc']];
                        $categoria = trim($row[$headerMap['categoria']]);

                        
                        $errorDeFila = false;
                        if (empty($nom)) {
                            $rowErrors[] = "Fila $rowNum: 'nombre' está vacío. Fila ignorada.";
                            $errorDeFila = true;
                        }
                        if ($id <= 0 && $errorDeFila == false) {
                            $rowErrors[] = "Fila $rowNum (Nombre: $nom): El ID '{$id}' es inválido. Fila ignorada.";
                            $errorDeFila = true;
                        }
                        if ($preu <= 0 && $errorDeFila == false) {
                            $rowErrors[] = "Fila $rowNum (Nombre: $nom): El precio '{$preu_raw}' es inválido. Fila ignorada.";
                            $errorDeFila = true;
                        }
                        
                        if (!is_numeric($estoc_raw) || (int)$estoc_raw < 0) {
                            $rowErrors[] = "Fila $rowNum (Nombre: $nom): El 'estoc' ('{$estoc_raw}') no es un número válido o es negativo. Fila ignorada.";
                            $errorDeFila = true;
                        }
                        $estoc = (int)$estoc_raw;

                        if (empty($categoria) && $errorDeFila == false) {
                            $rowErrors[] = "Fila $rowNum (Nombre: $nom): La 'categoria' está vacía. Fila ignorada.";
                            $errorDeFila = true;
                        }

                        if ($errorDeFila == false) {
                            $producte = [];
                            $producte['id'] = $id;
                            $producte['sku'] = 'JUEGO-' . $id;
                            $producte['nom'] = $nom;
                            $producte['descripcio'] = trim($row[$headerMap['descripcion']]);
                            $producte['img'] = trim($row[$headerMap['img']]);
                            $producte['preu'] = $preu;
                            $producte['estoc'] = $estoc;
                            $producte['categoria'] = $categoria;
                            
                            $productes[] = $producte;
                        }
                    } 
                    fclose($handle);
                } 
            } 
        } else {
            $errors[] = "No se ha podido abrir el fichero CSV subido.";
        }

        // --- PASO 5: Generar JSON ---
        if (count($errors) == 0 && $handle !== FALSE && count($missing) == 0) {
            $jsonData = ['productes' => $productes];
            $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 
            
            if (file_put_contents(JSON_FILE, $jsonString) === false) {
                $errors[] = "Error crítico: No se ha podido escribir el fichero 'products.json' en '" . DATA_DIR . "'.";
            } else {
                $importedCount = count($productes);
                $messages[] = "PASO 5: Fichero 'products.json' generado (sobrescrito) correctamente.";
                $messages[] = "(El JSON Server debería actualizarse automáticamente.)";
                $messages[] = "✅ ¡Importación completada con éxito!";
                $messages[] = "Total de productos importados: {$importedCount}";
            }
        }
    } 
} 

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>C1 - Importación de Productos (CSV)</title>
    <style>
        body { font-family: sans-serif; margin: 2em; line-height: 1.6; background-color: #f4f4f4; }
        h1 { color: #333; }
        form { background: #fff; border: 1px solid #ccc; padding: 25px; border-radius: 8px; }
        input[type="submit"] { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background: #0056b3; }
        .summary { padding: 15px; margin-top: 20px; border-radius: 5px; }
        .success { background: #e6ffed; border: 1px solid #b7ebc9; color: #25603d; }
        .error { background: #ffebeb; border: 1px solid #f5c6cb; color: #721c24; }
        .error ul, .success ul { margin-top: 10px; padding-left: 20px; }
        .post-import { background: #fff; border: 1px solid #ccc; padding: 25px; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>

    <h1>Importar Productos desde CSV</h1>
    <p>Este script subirá un fichero CSV, lo procesará y generará el <strong>/data/products.json</strong> para el JSON Server.</p>

    <?php if (count($messages) > 0): ?>
        <div class="summary success">
            <strong>Resumen de la Importación:</strong>
            <ul>
                <?php foreach ($messages as $msg): ?>
                    <li><?php echo htmlspecialchars($msg); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (count($rowErrors) > 0): ?>
        <div class="summary error">
            <strong>Errores Detectados (Filas ignoradas):</strong>
            <ul>
                <?php foreach ($rowErrors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (count($errors) > 0): // Muestra errores fatals ?>
        <div class="summary error">
            <strong>Errores Fatales:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="arxiuCsv"><strong>Selecciona el fichero CSV (.csv):</strong></label>
        <br><br>
        <p>El fichero debe contener las columnas (en cualquier orden):<br>
           <strong><?php echo implode(', ', $expectedHeaders); ?></strong>
        </srp>
        
        <input type="file" name="arxiuCsv" id="arxiuCsv" accept=".csv" required>
        <br><br>
        <input type="submit" value="Subir y Procesar">
    </form>

    <?php if ($importedCount > 0): ?>
    <div class="post-import">
        <h3>Verificación en el JSON Server</h3>
        <p>Se han importado <strong><?php echo $importedCount; ?></strong> productos. Puedes verificarlos en los enlaces:</p>
        <ul>
            <li><a href="http://localhost:3002/productes" target="_blank">Ver todos los productos (http://localhost:3002/productes)</a></li>
            <li><a href="http://localhost:3002/productes/?id=1" target="_blank">Ver el producto con ID 1 (si existe)</a></li>
        </ul>
    </div>
    <?php endif; ?>

</body>
</html>