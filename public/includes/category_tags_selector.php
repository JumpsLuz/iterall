<!-- Multi-Category & Tags Selector Component -->
<style>
.multi-select-wrapper {
    position: relative;
}

.multi-select-checkboxes {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 10px;
    background: var(--bg-hover);
}

.checkbox-item {
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.checkbox-item:hover {
    background: rgba(59, 130, 246, 0.1);
}

.checkbox-item input[type="checkbox"] {
    margin-right: 10px;
    cursor: pointer;
}

.tag-input-container {
    position: relative;
}

.tag-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
    min-height: 36px;
}

.tag-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--primary);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
}

.tag-pill .remove-tag {
    cursor: pointer;
    font-weight: bold;
    opacity: 0.8;
}

.tag-pill .remove-tag:hover {
    opacity: 1;
}

.tag-autocomplete {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    margin-top: 4px;
    display: none;
}

.tag-autocomplete.active {
    display: block;
}

.tag-suggestion {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
}

.tag-suggestion:hover {
    background: var(--bg-hover);
}

.tag-suggestion:last-child {
    border-bottom: none;
}
</style>

<!-- Categories Selection (pass $categorias array from controller) -->
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

<!-- Tags Input with Autocomplete -->
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
    if (!selectedTags.includes(tag) && tag !== '#@#_no_mini_proyecto_#@#') {
        selectedTags.push(tag);
        renderTags();
        updateHiddenInput();
    }
}

function removeTag(tag) {
    selectedTags = selectedTags.filter(t => t !== tag);
    renderTags();
    updateHiddenInput();
}

function renderTags() {
    tagPills.innerHTML = selectedTags.map(tag => `
        <div class="tag-pill">
            <span>${tag}</span>
            <span class="remove-tag" onclick="removeTag('${tag}')">×</span>
        </div>
    `).join('');
}

function updateHiddenInput() {
    tagsHidden.value = JSON.stringify(selectedTags);
}

// Close autocomplete when clicking outside
document.addEventListener('click', function(e) {
    if (!tagInput.contains(e.target) && !tagAutocomplete.contains(e.target)) {
        tagAutocomplete.classList.remove('active');
    }
});
</script>
