const API_URL = '../backend/api/comentarios.php';

// 1. Cargar comentarios y verificar estado de sesión
async function loadComments(productId) {
    document.getElementById('product_id_hidden').value = productId;
    
    const listContainer = document.getElementById('comments-list');
    const formContainer = document.getElementById('comment-form-container');
    const loginMessage = document.getElementById('login-required-message');

    listContainer.innerHTML = '<p style="color:#aaa; text-align:center;">Cargando opiniones...</p>';

    try {
        // Llamada GET a la API
        const response = await fetch(`${API_URL}?action=get&product_id=${productId}`);
        const data = await response.json(); 

        // A. Control de Visibilidad (Formulario vs Mensaje Login)
        if (data.is_logged_in) {
            if(formContainer) formContainer.style.display = 'block';
            if(loginMessage) loginMessage.style.display = 'none';
        } else {
            if(formContainer) formContainer.style.display = 'none';
            if(loginMessage) loginMessage.style.display = 'block';
        }

        // B. Renderizar la lista de comentarios
        renderComments(data.comments);
        updateStats(data.comments);

    } catch (error) {
        console.error('Error:', error);
        listContainer.innerHTML = '<p style="color:red; text-align:center;">Error cargando comentarios.</p>';
    }
}

// 2. Dibujar HTML de los comentarios
function renderComments(comments) {
    const listContainer = document.getElementById('comments-list');
    listContainer.innerHTML = '';

    if (!comments || comments.length === 0) {
        listContainer.innerHTML = '<p style="color:#888; font-style:italic; text-align:center;">Aún no hay valoraciones para este juego. ¡Sé el primero!</p>';
        return;
    }

    comments.forEach(c => {
        const stars = '★'.repeat(c.rating) + '☆'.repeat(5 - c.rating);
        const date = new Date(c.timestamp).toLocaleDateString('es-ES'); // Formato fecha español
        
        // Botón de borrar (solo si tiene permiso)
        const deleteBtn = c.can_delete ? 
            `<button onclick="deleteComment('${c.id}')" class="btn-delete" title="Eliminar comentario">🗑️ Borrar</button>` : '';
        
        // Clase para indicar si ya le di like
        const likeClass = c.liked_by_me ? 'liked' : '';

        const html = `
            <div class="comment-item" id="${c.id}">
                <div class="comment-header">
                    <div>
                        <span class="user-name">${c.username}</span>
                        <span class="comment-stars" style="color:#ffc700; margin-left:5px;">${stars}</span>
                    </div>
                    <span class="comment-date">${date}</span>
                </div>
                <div class="comment-body">
                    ${c.comment}
                </div>
                <div class="comment-footer">
                    <button class="btn-like ${likeClass}" onclick="toggleLike('${c.id}')">
                        👍 <span class="like-text">Me gusta</span> <span class="like-count">(${c.likes_count})</span>
                    </button>
                    ${deleteBtn}
                </div>
            </div>
        `;
        listContainer.innerHTML += html;
    });
}

function updateStats(comments) {
    const total = comments ? comments.length : 0;
    
    // 1. Actualizar el número total de opiniones
    const totalEl = document.getElementById('total-reviews');
    if(totalEl) totalEl.textContent = total;
    
    // 2. Calcular la media numérica
    const avgEl = document.getElementById('average-rating');
    let average = 0;

    if (total > 0) {
        const sum = comments.reduce((acc, c) => acc + c.rating, 0);
        average = (sum / total);
        if(avgEl) avgEl.textContent = average.toFixed(1);
    } else {
        if(avgEl) avgEl.textContent = '--';
    }

    // 3. PINTAR LAS ESTRELLAS EN EL RESUMEN (¡Esta es la parte nueva!)
    const starsContainer = document.querySelector('.rating-stars');
    if (starsContainer) {
        let starsHtml = '';
        // Creamos 5 estrellas
        for (let i = 1; i <= 5; i++) {
            // Si el índice es menor o igual a la media redondeada, es dorada
            if (i <= Math.round(average)) {
                starsHtml += '<span class="filled">★</span>';
            } else {
                // Si no, es normal (gris por CSS)
                starsHtml += '<span>★</span>'; 
            }
        }
        starsContainer.innerHTML = starsHtml;
    }
}

// 4. Lógica para enviar comentario
const form = document.getElementById('form-comentari');
if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const productId = document.getElementById('product_id_hidden').value;
        const text = document.getElementById('comment-text').value;
        const ratingEl = document.querySelector('input[name="rate"]:checked');
        const rating = ratingEl ? ratingEl.value : 0;

        if(rating == 0) {
            alert("Por favor, selecciona una puntuación de estrellas.");
            return;
        }

        const data = {
            product_id: productId,
            comment: text,
            rating: rating
        };

        try {
            const response = await fetch(`${API_URL}?action=add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Limpiar formulario
                document.getElementById('comment-text').value = '';
                if(ratingEl) ratingEl.checked = false;
                // Recargar comentarios para ver el nuevo
                loadComments(productId); 
            } else {
                alert(result.message || 'Error al enviar.');
                // Si el error es por no estar logueado, redirigir
                if(response.status === 401) window.location.href = 'login.html';
            }
        } catch (error) {
            console.error(error);
            alert("Hubo un error de conexión.");
        }
    });
}

// 5. Lógica de Me Gusta
async function toggleLike(commentId) {
    try {
        const response = await fetch(`${API_URL}?action=like`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });

        const result = await response.json();
        
        if (result.success) {
            const btn = document.querySelector(`#${commentId} .btn-like`);
            if(btn) {
                const countSpan = btn.querySelector('.like-count');
                // Actualizar número entre paréntesis
                countSpan.textContent = `(${result.likes})`;
                
                if (result.liked) btn.classList.add('liked');
                else btn.classList.remove('liked');
            }
        } else {
            if(response.status === 401) alert("Inicia sesión para dar 'Me gusta'.");
        }
    } catch (error) {
        console.error(error);
    }
}

// 6. Lógica de Borrar
async function deleteComment(commentId) {
    if(!confirm("¿Estás seguro de que quieres borrar este comentario?")) return;

    try {
        const response = await fetch(`${API_URL}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });
        
        const result = await response.json();
        if (result.success) {
            // Eliminar elemento del DOM visualmente
            const el = document.getElementById(commentId);
            if(el) el.remove();
            
            // Actualizar contador visualmente (-1)
            const totalEl = document.getElementById('total-reviews');
            if(totalEl) totalEl.textContent = Math.max(0, parseInt(totalEl.textContent) - 1);
        } else {
            alert(result.message || "No se pudo borrar.");
        }
    } catch(e) { console.error(e); }
}