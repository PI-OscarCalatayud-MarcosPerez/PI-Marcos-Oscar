// frontend/js/inicio.js

document.addEventListener("DOMContentLoaded", async () => {
    try {
      // Pedimos los datos a la API PHP
      const response = await fetch("/backend/api/products.php");
      
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status} - API no disponible`);
      }
      
      const data = await response.json();
      
      // Detectar estructura del JSON
      const products = data.products ? data.products : data;

      // Función para crear la tarjeta HTML
      const createCard = (product) => {
        return `
            <div class="item" onclick="window.location.href='product.html?id=${product.id}'">
                <div class="item-imagen">
                    <img src="${product.img}" alt="${product.nom}" loading="lazy" />
                </div>
                <div class="item-info">
                    <p class="item-titulo">${product.nom}</p>
                    <p class="item-descripcion-hover">${product.descripcio.substring(0, 80)}...</p>
                    <p class="price">${parseFloat(product.preu).toFixed(2)}€</p>
                </div>
            </div>
        `;
      };

      // Función de renderizado
      const renderCarousel = (categoryId, containerId) => {
        const container = document.getElementById(containerId);
        if (!container) return;

        // Filtrar por categoria
        const filtered = products.filter((p) => p.categoria === categoryId);
        
        if (filtered.length > 0) {
            container.innerHTML = filtered.map(createCard).join("");
        } else {
            container.innerHTML = "<p style='padding:20px; color: white; text-align: center; width: 100%;'>No hay productos disponibles.</p>";
        }
      };

      // Cargar los carruseles
      renderCarousel("comprados", "lista-comprados");
      renderCarousel("ofertas", "lista-ofertas");
      renderCarousel("nuevos", "lista-nuevos");

    } catch (error) {
      console.error("Error cargando productos:", error);
    }
});