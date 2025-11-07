<?php
if (isset($_POST['btnSubir']) && $_POST['btnSubir'] == 'Subir') {
    if (is_uploaded_file($_FILES['arxiuExcel']['tmp_name'])) {
        $nombre = $_FILES['arxiuExcel']['name'];
        
        $ruta_destino = "/var/www/uploads/{$nombre}"; 

        if (move_uploaded_file($_FILES['arxiuExcel']['tmp_name'], $ruta_destino)) {
            echo "<p>Arxiu $nombre pujat amb èxit (via Docker)</p>";
        } else {
            echo "<p>Error: No s'ha pogut moure l'arxiu a $ruta_destino</p>";
        }
    }
}
?>