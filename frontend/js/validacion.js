window.onload = function() {
    
    // Capturamos el formulario
    var form = document.getElementById('formulario');

    form.onsubmit = function(e) {
        var errores = false;

        // Limpiar errores previos
        var spans = document.getElementsByClassName('error');
        for (var i = 0; i < spans.length; i++) {
            spans[i].innerHTML = "";
        }

        // Validar Nombre
        var nombre = document.getElementById('nombre').value;
        if (nombre == "") {
            document.getElementById('error-nom').innerHTML = "Pon tu nombre.";
            errores = true;
        }

        // Validar Correo
        var correo = document.getElementById('correo').value;
        if (correo == "" || correo.indexOf('@') == -1) {
            document.getElementById('error-correu').innerHTML = "Correo mal.";
            errores = true;
        }

        // Validar Asunto
        var asunto = document.getElementById('asunto').value;
        if (asunto == "") {
            document.getElementById('error-cicle').innerHTML = "Elige asunto.";
            errores = true;
        }

        // Validar Telefono (solo si hay algo escrito)
        var telefono = document.getElementById('Telefono').value;
        if (telefono != "" && isNaN(telefono)) {
            document.getElementById('error-telefono').innerHTML = "Solo números.";
            errores = true;
        }

        // Validar Checkbox
        var check = document.getElementById('consentimiento');
        if (!check.checked) {
            document.getElementById('error-consentimiento').innerHTML = "Acepta los datos.";
            errores = true;
        }

        // Si hay error, paramos el envío
        if (errores) {
            e.preventDefault();
            return false;
        }
    };
};