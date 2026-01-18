const uploadZone = document.getElementById('uploadZone');
const inputImagenes = document.getElementById('inputImagenes');
const previewContainer = document.getElementById('previewContainer');
const btnSubmit = document.getElementById('btnSubmit');
const imagenPrincipalIndex = document.getElementById('imagenPrincipalIndex');
const ordenImagenes = document.getElementById('ordenImagenes');
const maxImagenes = parseInt(document.querySelector('[name="espacio_disponible"]')?.value || 20);

let selectedFiles = [];
let draggedIndex = null;

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
        return;
    }

    previewContainer.style.display = 'grid';
    previewContainer.className = 'preview-container';

    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'preview-item' + (index === 0 ? ' principal' : '');
            div.draggable = true;
            div.dataset.index = index;
            
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                ${index === 0 ? '<div class="star-principal">★</div>' : ''}
                ${index === 0 ? '<div class="principal-badge">PRINCIPAL</div>' : ''}
                <div class="order-number ${index === 0 ? 'first' : ''}">${index + 1}</div>
                <button type="button" class="remove-btn" onclick="event.stopPropagation(); eliminarImagen(${index})">×</button>
            `;
            
            div.addEventListener('dragstart', handleDragStart);
            div.addEventListener('dragend', handleDragEnd);
            div.addEventListener('dragover', handleDragOver);
            div.addEventListener('drop', handleDrop);
            div.addEventListener('dragenter', handleDragEnter);
            div.addEventListener('dragleave', handleDragLeave);
            
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    
    actualizarOrdenImagenes();
}

function handleDragStart(e) {
    draggedIndex = parseInt(this.dataset.index);
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.preview-item').forEach(item => {
        item.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    const dropIndex = parseInt(this.dataset.index);
    
    if (draggedIndex !== null && draggedIndex !== dropIndex) {
        const draggedFile = selectedFiles[draggedIndex];
        const targetFile = selectedFiles[dropIndex];
        
        selectedFiles[dropIndex] = draggedFile;
        selectedFiles[draggedIndex] = targetFile;
        
        actualizarPrevisualizacion();
    }
    
    draggedIndex = null;
    return false;
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