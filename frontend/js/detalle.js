// Obtenemos el ID de la URL (ej: producto.html?id=1)
const params = new URLSearchParams(window.location.search);
const idProducto = params.get('id');

// URLs
const API_PROD = `http://localhost:3002/products/${idProducto}`; // JSON Server busca por ID
const API_REVIEWS = '/backend/api/reviews.php';
const API_STATUS = '/backend/api/status.php';

let usuarioLogueado = false;
let valoracionUsuario = 0;

// AL CARGAR LA PÁGINA
window.onload = function() {
    if (!idProducto) {
        document.body.innerHTML = "<h1>Error: No se ha especificado producto</h1>";
        return;
    }
    
    comprobarSesion();
    cargarInfoProducto();
    cargarReviews();
};

// 1. CARGAR INFO DEL PRODUCTO
async function cargarInfoProducto() {
    try {
        const res = await fetch(API_PROD);
        if(!res.ok) throw new Error("Producto no encontrado");
        const prod = await res.json();

        const html = `
            <div class="col-izq">
                <img src="${prod.img || 'img/placeholder.png'}" alt="Foto">
            </div>
            <div class="col-der">
                <h1 class="titulo-producto">${prod.nom}</h1>
                <p class="sku">REF: ${prod.sku || prod.id}</p>
                <p class="descripcion-producto">${prod.descripcio || 'Sin descripción'}</p>
                <p class="precio-producto">${prod.preu}€</p>
                
                <button class="btn-compra">
                    <img src="img/carrito.png" alt="Carro">
                    COMPRAR AHORA
                </button>
            </div>
        `;
        document.getElementById('ficha-producto').innerHTML = html;
    } catch(e) {
        document.getElementById('ficha-producto').innerHTML = "<h3>Producto no encontrado :(</h3>";
    }
}

// 2. CARGAR REVIEWS
async function cargarReviews() {
    const res = await fetch(`${API_REVIEWS}?product_id=${idProducto}`);
    const reviews = await res.json();
    const div = document.getElementById('lista-reviews');
    
    div.innerHTML = "";
    if(reviews.length == 0) {
        div.innerHTML = "<p>No hay comentarios aún.</p>";
    } else {
        reviews.reverse().forEach(r => {
            // Generar estrellitas simples
            let stars = "";
            for(let i=0; i<r.rating; i++) stars += "★";
            
            div.innerHTML += `
                <div class="comentario">
                    <span class="usuario-review">${r.user}</span> 
                    <span class="estrellas-review">${stars}</span>
                    <p class="texto-review">${r.comment}</p>
                    <small class="fecha-review">${r.date}</small>
                </div>
            `;
        });
    }
}

// 3. ENVIAR REVIEW
async function enviarReview() {
    if(!usuarioLogueado) {
        alert("Debes iniciar sesión primero.");
        window.location.href = "login.html";
        return;
    }

    const texto = document.getElementById('texto').value;

    if (valoracionUsuario === 0) {
        alert('¡Elige una puntuación!'); 
        return;
    }

    if(!texto) return alert("Escribe algo!");

    const datos = {
        productId: idProducto,
        rating: valoracionUsuario,
        comment: texto
    };

    await fetch(API_REVIEWS, {
        method: 'POST',
        body: JSON.stringify(datos)
    });

    // Limpiar y recargar
    document.getElementById('texto').value = "";
    pintarEstrellasFormulario(0);
    valoracionUsuario = 0;
    cargarReviews();
    alert("¡Comentario guardado!");
}

// 4. COMPROBAR SESIÓN (Para mostrar/ocultar formulario)
async function comprobarSesion() {
    const res = await fetch(API_STATUS);
    const data = await res.json();
    usuarioLogueado = data.isLoggedIn;
    
    if(!usuarioLogueado) {
        document.getElementById('form-reviews').style.display = 'none';
        document.getElementById('aviso-login').style.display = 'block';
    } else {
        document.getElementById('form-reviews').style.display = 'block';
        document.getElementById('aviso-login').style.display = 'none';
    }
}

function seleccionarEstrella(n) {
    valoracionUsuario = n;
    pintarEstrellasFormulario(n);
}

function pintarEstrellasFormulario(n) {
    const stars = document.querySelectorAll('.estrellas-input span');
    stars.forEach((s, index) => {
        if (index < n) s.classList.add('selected');
        else s.classList.remove('selected');
    });
}