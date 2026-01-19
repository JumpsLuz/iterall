<link rel="stylesheet" href="css/category-tags.css">

<div class="form-group">
    <label class="form-label">Categorías * <span class="form-hint">(selecciona todas las que apliquen)</span></label>
    <div class="multi-select-wrapper">
        <div class="multi-select-checkboxes">
            <?php foreach ($categorias as $cat): ?>
                <label class="checkbox-item">
                    <input type="checkbox" 
                           name="categorias[]" 
                           value="<?php echo $cat['id']; ?>"
                           <?php echo (isset($selectedCategories) && in_array($cat['id'], $selectedCategories)) ? 'checked' : ''; ?>>
                    <span><?php echo htmlspecialchars($cat['nombre_categoria']); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="form-label">Etiquetas <span class="form-hint">(opcional, como hashtags)</span></label>
    <div class="tag-input-container">
        <input type="text" 
               id="tagInput" 
               class="form-control" 
               placeholder="Escribe para buscar o crear etiquetas..."
               autocomplete="off">
        <div id="tagAutocomplete" class="tag-autocomplete"></div>
    </div>
    <div id="tagPills" class="tag-pills"></div>
    <input type="hidden" id="tagsHidden" name="etiquetas" value="">
</div>

<script>
const tagInput = document.getElementById('tagInput');
const tagAutocomplete = document.getElementById('tagAutocomplete');
const tagPills = document.getElementById('tagPills');
const tagsHidden = document.getElementById('tagsHidden');
let selectedTags = [];
let searchTimeout;

tagInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 1) {
        tagAutocomplete.classList.remove('active');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`api/buscar_etiquetas.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(tags => {
                if (tags.length > 0) {
                    tagAutocomplete.innerHTML = tags.map(tag => 
                        `<div class="tag-suggestion" data-tag="${tag.nombre_etiqueta}">${tag.nombre_etiqueta}</div>`
                    ).join('');
                    tagAutocomplete.classList.add('active');
                } else {
                    tagAutocomplete.innerHTML = `<div class="tag-suggestion" data-tag="${query}">Crear: "${query}"</div>`;
                    tagAutocomplete.classList.add('active');
                }
            });
    }, 300);
});

tagAutocomplete.addEventListener('click', function(e) {
    if (e.target.classList.contains('tag-suggestion')) {
        const tag = e.target.dataset.tag;
        addTag(tag);
        tagInput.value = '';
        tagAutocomplete.classList.remove('active');
    }
});

tagInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const tag = this.value.trim();
        if (tag) {
            addTag(tag);
            this.value = '';
            tagAutocomplete.classList.remove('active');
        }
    }
});

function addTag(tag) {
    const lowerTag = tag.toLowerCase();
    if (!selectedTags.includes(tag) && tag !== '#@#_no_mini_proyecto_#@#' && lowerTag !== 'destacado') {
        selectedTags.push(tag);
        renderTags();
        updateHiddenInput();
    } else if (lowerTag === 'destacado') {
        alert('La etiqueta "destacado" está reservada por el sistema.');
    }
}

function renderTags() {
    tagPills.innerHTML = selectedTags.map((tag, index) => `
        <div class="tag-pill">
            <span>${tag}</span>
            <span class="remove-tag" data-index="${index}" onclick="removeTagByIndex(${index})">×</span>
        </div>
    `).join('');
}

function removeTagByIndex(index) {
    selectedTags.splice(index, 1);
    renderTags();
    updateHiddenInput();
}

function removeTag(tag) {
    selectedTags = selectedTags.filter(t => t !== tag);
    renderTags();
    updateHiddenInput();
}

function updateHiddenInput() {
    tagsHidden.value = JSON.stringify(selectedTags);
}

document.addEventListener('click', function(e) {
    if (!tagInput.contains(e.target) && !tagAutocomplete.contains(e.target)) {
        tagAutocomplete.classList.remove('active');
    }
});
</script>
