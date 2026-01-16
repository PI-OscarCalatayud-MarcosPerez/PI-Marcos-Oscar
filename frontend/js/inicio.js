// frontend/js/inicio.js

document.addEventListener("DOMContentLoaded", async () => {
    try {
      const response = await fetch("/backend/api/products.php");
      
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status} - API no disponible`);
      }
      
      const data = await response.json();
      const products = data.products ? data.products : data;

      // --- AQUÍ ESTÁ LA CLAVE PARA EL TAB ---
      const createCard = (product) => {
        const precio = parseFloat(product.preu).toFixed(2);
        const descripcion = product.descripcio ? product.descripcio.substring(0, 80) + '...' : '';

        return `
            <div class="item" 
                 onclick="window.location.href='product.html?id=${product.id}'"
                 tabindex="0" 
                 role="button"
                 aria-label="Juego ${product.nom}, precio ${precio} euros"
                 onkeydown="if(event.key === 'Enter' || event.key === ' ') window.location.href='product.html?id=${product.id}'">
                
                <div class="item-imagen">
                    <img src="${product.img}" alt="Carátula de ${product.nom}" loading="lazy" />
                </div>
                <div class="item-info">
                    <p class="item-titulo">${product.nom}</p>
                    <p class="item-descripcion-hover">${descripcion}</p>
                    <p class="price">${precio}€</p>
                </div>
            </div>
        `;
      };

      const renderCarousel = (categoryId, containerId) => {
        const container = document.getElementById(containerId);
        if (!container) return;

        const filtered = products.filter((p) => p.categoria === categoryId);
        
        if (filtered.length > 0) {
            container.innerHTML = filtered.map(createCard).join("");
        } else {
            container.innerHTML = "<p style='padding:20px; color: #333; text-align: center; width: 100%;'>No hay productos disponibles.</p>";
        }
      };

      renderCarousel("comprados", "lista-comprados");
      renderCarousel("ofertas", "lista-ofertas");
      renderCarousel("nuevos", "lista-nuevos");

    } catch (error) {
      console.error("Error cargando productos:", error);
    }
});