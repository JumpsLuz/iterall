const uploadZone = document.getElementById('uploadZone');
const inputImagenes = document.getElementById('inputImagenes');
const previewContainer = document.getElementById('previewContainer');
const btnSubmit = document.getElementById('btnSubmit');
const imagenPrincipalIndex = document.getElementById('imagenPrincipalIndex');
const ordenImagenes = document.getElementById('ordenImagenes');
const placeholderText = document.getElementById('placeholderText');
const maxImagenes = parseInt(document.querySelector('[name="espacio_disponible"]')?.value || 20);

let selectedFiles = [];

uploadZone?.addEventListener('click', () => inputImagenes?.click());

uploadZone?.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone?.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone?.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    
    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    agregarArchivos(files);
});

inputImagenes?.addEventListener('change', (e) => {
    const files = Array.from(e.target.files);
    agregarArchivos(files);
});

function agregarArchivos(newFiles) {
    const espacioDisponible = maxImagenes - selectedFiles.length;
    
    if (newFiles.length > espacioDisponible) {
        alert(`Solo puedes agregar ${espacioDisponible} imagen(es) más. Límite: ${maxImagenes} imágenes por iteración.`);
        newFiles = newFiles.slice(0, espacioDisponible);
    }

    selectedFiles = [...selectedFiles, ...newFiles];
    actualizarPrevisualizacion();
    actualizarBotonSubmit();
}

function actualizarPrevisualizacion() {
    if (!previewContainer) return;
    
    previewContainer.innerHTML = '';
    
    if (selectedFiles.length === 0) {
        previewContainer.style.display = 'none';
        uploadZone.classList.remove('has-images');
        return;
    }

    previewContainer.style.display = 'grid';
    uploadZone.classList.add('has-images');
    previewContainer.className = 'preview-container';

    selectedFiles.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'preview-item' + (index === 0 ? ' principal' : '');
        div.dataset.index = index;
        
        const isPrincipal = index === 0;
        const isFirst = index === 0;
        const isLast = index === selectedFiles.length - 1;
        const totalImages = selectedFiles.length;

        const imageUrl = URL.createObjectURL(file);
        
        div.innerHTML = `
            <img src="${imageUrl}" alt="Preview ${index + 1}">
            ${isPrincipal ? '<div class="star-principal">★</div>' : ''}
            ${isPrincipal ? '<div class="principal-badge">PRINCIPAL</div>' : ''}
            <div class="order-number ${isPrincipal ? 'first' : ''}">${index + 1}</div>
            
            <div class="order-controls">
                <button type="button" class="order-btn order-up ${isFirst ? 'disabled' : ''}" 
                        onclick="event.stopPropagation(); moverImagen(${index}, -1)" 
                        ${isFirst ? 'disabled' : ''} title="Mover arriba">
                    <i class="fas fa-chevron-up"></i>
                </button>
                <button type="button" class="order-btn order-down ${isLast ? 'disabled' : ''}" 
                        onclick="event.stopPropagation(); moverImagen(${index}, 1)" 
                        ${isLast ? 'disabled' : ''} title="Mover abajo">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            
            <button type="button" class="remove-btn" onclick="event.stopPropagation(); eliminarImagen(${index})">×</button>
            
            ${totalImages > 1 && !isPrincipal ? `
                <button type="button" class="make-principal-btn" 
                        onclick="event.stopPropagation(); hacerPrincipal(${index})" 
                        title="Hacer principal">
                    <i class="far fa-star"></i>
                </button>
            ` : ''}
        `;
        
        previewContainer.appendChild(div);
    });
    
    actualizarOrdenImagenes();
    actualizarInputFile();
}

function moverImagen(index, direction) {
    const newIndex = index + direction;
    
    if (newIndex < 0 || newIndex >= selectedFiles.length) return;
    
    const temp = selectedFiles[index];
    selectedFiles[index] = selectedFiles[newIndex];
    selectedFiles[newIndex] = temp;
    
    actualizarPrevisualizacion();
}

function hacerPrincipal(index) {
    if (index === 0) return;
    
    const file = selectedFiles[index];
    selectedFiles.splice(index, 1);
    selectedFiles.unshift(file);
    
    actualizarPrevisualizacion();
}

function eliminarImagen(index) {
    selectedFiles.splice(index, 1);
    actualizarPrevisualizacion();
    actualizarBotonSubmit();
    actualizarInputFile();
}

function actualizarBotonSubmit() {
    if (btnSubmit) {
        btnSubmit.disabled = selectedFiles.length === 0;
    }
}

function actualizarInputFile() {
    if (!inputImagenes) return;
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    inputImagenes.files = dataTransfer.files;
}

function actualizarOrdenImagenes() {
    imagenPrincipalIndex.value = 0;
    ordenImagenes.value = selectedFiles.map((_, i) => i).join(',');
}

document.getElementById('formIteracion')?.addEventListener('submit', function(e) {
    if (selectedFiles.length === 0) {
        e.preventDefault();
        alert('Debes seleccionar al menos una imagen');
        return false;
    }
    if (selectedFiles.length > maxImagenes) {
        e.preventDefault();
        alert(`Máximo ${maxImagenes} imágenes permitidas`);
        return false;
    }
});
