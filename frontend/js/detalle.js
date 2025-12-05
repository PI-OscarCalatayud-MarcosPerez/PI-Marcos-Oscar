// frontend/js/detalle.js

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Obtener ID de la URL
    const params = new URLSearchParams(window.location.search);
    const productId = parseInt(params.get('id')); 

    if (!productId) {
        document.querySelector('.product-detail-container').innerHTML = 
            "<h2>Producto no especificado. <a href='index.html'>Volver al inicio</a></h2>";
        return;
    }

    try {
        // 2. Pedir datos a la API PHP
        const response = await fetch('/backend/api/products.php');
        
        if (!response.ok) {
             throw new Error("Error de conexión con la API");
        }

        const data = await response.json();
        
        // 3. Detectar estructura
        const products = data.products ? data.products : data;
        
        // 4. Buscar producto
        const product = products.find(p => p.id === productId);

        if (product) {
            // 5. Rellenar HTML
            document.title = product.nom + " | MOKeys";
            document.getElementById('p-title').textContent = product.nom;
            
            const skuElement = document.getElementById('p-sku');
            if(skuElement) skuElement.textContent = "REF: " + (product.sku || 'N/A');
            
            document.getElementById('p-desc').textContent = product.descripcio;
            document.getElementById('p-price').textContent = parseFloat(product.preu).toFixed(2) + "€";
            
            const stockElement = document.getElementById('p-stock');
            if(stockElement) stockElement.textContent = "Stock disponible: " + (product.estoc || 0);
            
            document.getElementById('p-image').src = product.img;
            document.getElementById('p-image').alt = product.nom;
            
            // 6. Cargar comentarios (usa funcion global de comentarios.js)
            if (typeof loadComments === 'function') {
                loadComments(productId.toString());
            }
        } else {
            document.querySelector('.product-detail-container').innerHTML = 
                "<h2>Producto no encontrado. <a href='index.html'>Volver al catálogo</a></h2>";
        }
    } catch (error) {
        console.error("Error:", error);
        document.querySelector('.product-detail-container').innerHTML = 
            `<h2>Error cargando los detalles del producto.</h2><p>${error.message}</p>`;
    }
});