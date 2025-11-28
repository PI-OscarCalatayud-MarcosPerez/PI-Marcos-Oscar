// frontend/js/inicio.js

const API_URL = 'http://localhost:3002/products';

// Ejecutar cuando cargue la página
document.addEventListener('DOMContentLoaded', () => {
    cargarOfertas();
});

function cargarOfertas() {
    fetch(API_URL)
        .then(res => res.json())
        .then(data => {
            // Aseguramos si es un array directo o viene dentro de { products: [...] }
            const productos = Array.isArray(data) ? data : (data.products || []);
            const contenedor = document.getElementById('lista-ofertas');
            
            contenedor.innerHTML = ''; // Borramos el texto "Cargando..."

            productos.forEach(prod => {
                // Creamos un enlace <a> que envuelve todo el item
                // Apunta a "producto.html" pasando el ID en la URL
                const enlace = document.createElement('a');
                enlace.href = `producto.html?id=${prod.id}`;
                enlace.className = 'item'; // Usamos tu clase CSS original
                enlace.style.textDecoration = 'none'; 
                enlace.style.color = 'inherit';

                enlace.innerHTML = `
                    <div class="item-imagen">
                        <img src="${prod.img || 'img/placeholder.png'}" alt="${prod.nom}">
                    </div>
                    <div class="item-info">
                        <p class="item-titulo">${prod.nom}</p>
                        <p class="item-descripcion-hover">${(prod.descripcio || '').substring(0, 50)}...</p>
                        <p class="price">${prod.preu}€</p>
                    </div>
                `;

                contenedor.appendChild(enlace);
            });
        })
        .catch(err => {
            console.error(err);
            const contenedor = document.getElementById('lista-ofertas');
            if(contenedor) contenedor.innerHTML = '<p>Error al cargar ofertas.</p>';
        });
}