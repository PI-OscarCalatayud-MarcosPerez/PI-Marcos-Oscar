<?php
/*
 * ==========================================================
 * C1 - SCRIPT D'IMPORTACIÓ (FLUX CORRECTE PER A DOCKER)
 * ==========================================================
 * PAS 1-4: Rep i valida l'Excel.
 * PAS 5: Genera l'arxiu /data/products.json.
 * (El 'jsonserver' el detectarà automàticament gràcies a '--watch')
 * PAS 6 (Eliminat): El cURL era redundant.
 * PAS 7: Mostra el resultat.
 */

// Configuració inicial i gestió d'errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. SETUP I DEPENDÈNCIES
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// 2. DEFINICIÓ DE RUTES
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('DATA_DIR', __DIR__ . '/../data/');
define('JSON_FILE', DATA_DIR . 'products.json');

// Definim les columnes que ESPEREM trobar
$expectedHeaders = array('id', 'sku', 'nom', 'descripcio', 'img', 'preu', 'estoc');

// Arrays per a la comunicació
$messages = array();
$errors = array();
$importedCount = 0;
$rowErrors = array();

// 3. GESTIÓ DE LA PETICIÓ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- PAS 1: Validar permisos ---
    if (!is_dir(UPLOAD_DIR) || !is_writable(UPLOAD_DIR)) {
        $errors[] = "Error: La carpeta 'uploads' ('" . UPLOAD_DIR . "') no existeix o no té permisos d'escriptura.";
    }
    if (!is_dir(DATA_DIR) || !is_writable(DATA_DIR)) {
        $errors[] = "Error: La carpeta 'data' ('" . DATA_DIR . "') no existeix o no té permisos d'escriptura.";
    }

    // --- PAS 2: Rebre i desar el fitxer (Amb Nom Únic) ---
    if (count($errors) == 0) {
        if (!isset($_FILES['arxiuExcel']) || $_FILES['arxiuExcel']['error'] != UPLOAD_ERR_OK) {
            $errors[] = "Error en la pujada del fitxer. Codi d'error: " . $_FILES['arxiuExcel']['error'];
        } else {
            $fileTmpPath = $_FILES['arxiuExcel']['tmp_name'];
            $nomOriginal = basename($_FILES['arxiuExcel']['name']);
            
            $extensio = '';
            $partsNom = explode('.', $nomOriginal);
            if (count($partsNom) > 1) {
                $extensio = $partsNom[count($partsNom) - 1];
            }
            $nomFitxerUnic = 'import_' . time() . '.' . $extensio;
            $uploadFilePath = UPLOAD_DIR . $nomFitxerUnic;

            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                $messages[] = "Fitxer '{$nomOriginal}' pujat i desat com '{$nomFitxerUnic}'.";
            } else {
                $errors[] = "No s'ha pogut moure el fitxer pujat a 'uploads'.";
            }
        }
    }

    // --- PAS 3, 4, 5: Processar el fitxer ---
    if (count($errors) == 0) {
        
        // --- PAS 3: Llegeix l’Excel ---
        $spreadsheet = IOFactory::load($uploadFilePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, false, false); 

        if (empty($data)) {
            $errors[] = "L'arxiu Excel està buit.";
        } else {
            
            // --- Validar capçaleres (manera arcaica) ---
            $headerRow = $data[0]; 
            unset($data[0]); 
            $headers = array();
            foreach ($headerRow as $headerCell) {
                $headers[] = strtolower(trim($headerCell));
            }
            $headerMap = array();
            $columnaIndex = 0;
            foreach ($headers as $headerName) {
                $headerMap[$headerName] = $columnaIndex;
                $columnaIndex++;
            }
            $missing = array();
            foreach ($expectedHeaders as $expected) {
                if (!isset($headerMap[$expected])) {
                    $missing[] = $expected;
                }
            }

            if (count($missing) > 0) {
                $errors[] = "Capçaleres incorrectes. Falten columnes: " . implode(', ', $missing);
            } else {
                
                // --- PAS 4: Validar Dades ---
                $productes = array();
                $rowNum = 1; 
                
                foreach ($data as $row) {
                    $rowNum++; 
                    $filaBuida = true;
                    for ($i = 0; $i < count($row); $i++) {
                        if ($row[$i] != null && $row[$i] != '') { $filaBuida = false; break; }
                    }
                    if ($filaBuida) { continue; }

                    $sku = trim($row[$headerMap['sku']]);
                    $nom = trim($row[$headerMap['nom']]);
                    $preu_raw = $row[$headerMap['preu']];
                    $preu = (float)str_replace(',', '.', $preu_raw);
                    $estoc = (int)$row[$headerMap['estoc']];
                    $id = (int)$row[$headerMap['id']];

                    $errorDeFila = false;
                    if (empty($sku) || empty($nom)) {
                        $rowErrors[] = "Fila $rowNum: SKU o Nom estan buits. Fila ignorada.";
                        $errorDeFila = true;
                    }
                    if ($preu <= 0 && $errorDeFila == false) {
                        $rowErrors[] = "Fila $rowNum (SKU: $sku): El preu '{$preu_raw}' és invàlid. Fila ignorada.";
                        $errorDeFila = true;
                    }
                    if ($id <= 0 && $errorDeFila == false) {
                         $rowErrors[] = "Fila $rowNum (SKU: $sku): L'ID '{$id}' és invàlid. Fila ignorada.";
                         $errorDeFila = true;
                    }

                    if ($errorDeFila == false) {
                        $producte = array();
                        $producte['id'] = $id;
                        $producte['sku'] = $sku;
                        $producte['nom'] = $nom;
                        $producte['descripcio'] = trim($row[$headerMap['descripcio']]);
                        $producte['img'] = trim($row[$headerMap['img']]);
                        $producte['preu'] = $preu;
                        $producte['estoc'] = $estoc;
                        
                        $productes[] = $producte;
                    }
                } // Fi del 'foreach' de dades

                // --- PAS 5: Generar l'arxiu JSON ---
                $jsonData = array('productes' => $productes);
                $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 
                
                // Escriure el nou fitxer JSON (sobreescrivint directament)
                if (file_put_contents(JSON_FILE, $jsonString) === false) {
                    $errors[] = "Error crític: No s'ha pogut escriure el fitxer 'products.json' a '" . DATA_DIR . "'.";
                } else {
                    $importedCount = count($productes);
                    
                    // --- PAS 6: ELIMINAT (cURL) ---
                    // El servidor 'jsonserver' s'actualitza sol gràcies a '--watch'

                    // --- PAS 7: Missatges d'èxit final ---
                    $messages[] = "PAS 5: Fitxer 'products.json' generat (sobreescrit) correctament.";
                    $messages[] = "(El JSON Server s'hauria d'actualitzar automàticament.)";
                    $messages[] = "✅ Importació completada amb èxit!";
                    $messages[] = "Total de productes importats: {$importedCount}";
                }
            } 
        } 
    } 
} 

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>C1 - Importació de Productes (Flux Correcte)</title>
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

    <h1>Importar Productes des d'Excel</h1>
    <p>Aquest script pujarà un fitxer Excel, el processarà i generarà el <strong>/data/products.json</strong> per al JSON Server.</p>

    <?php if (count($messages) > 0): ?>
        <div class="summary success">
            <strong>Resum de la Importació:</strong>
            <ul>
                <?php foreach ($messages as $msg): ?>
                    <li><?php echo htmlspecialchars($msg); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (count($rowErrors) > 0): ?>
        <div class="summary error">
            <strong>Errors Detectats (Files ignorades):</strong>
            <ul>
                <?php foreach ($rowErrors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (count($errors) > 0 && count($rowErrors) == 0): // Mostra errors fatals ?>
        <div class="summary error">
            <strong>Errors Fatals:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="arxiuExcel"><strong>Selecciona el fitxer Excel (.xlsx, .xls):</strong></label>
        <br><br>
        <p>El fitxer ha de contenir les columnes (en qualsevol ordre):<br>
           <strong><?php echo implode(', ', $expectedHeaders); ?></strong>
        </p>
        
        <input type="file" name="arxiuExcel" id="arxiuExcel" accept=".xlsx,.xls,.csv" required>
        <br><br>
        <input type="submit" value="Pujar i Processar">
    </form>

    <?php if ($importedCount > 0): ?>
    <div class="post-import">
        <h3>Verificació al JSON Server</h3>
        <p>S'han importat <strong><?php echo $importedCount; ?></strong> productes. Pots verificar-los als enllaços:</p>
        <ul>
            <li><a href="http://localhost:3000/productes" target="_blank">Veure tots els productes (http://localhost:3000/productes)</a></li>
            <li><a href="http://localhost:3000/productes/1" target="_blank">Veure el producte amb ID 1 (si existeix)</a></li>
        </ul>
    </div>
    <?php endif; ?>

</body>
</html>