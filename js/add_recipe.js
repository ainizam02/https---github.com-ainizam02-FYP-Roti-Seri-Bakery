function addIngredient() {
    const container = document.getElementById('ingredients-container');
    const newRow = document.createElement('div');
    newRow.className = 'ingredient-row';
    newRow.innerHTML = `
        <div class="form-group">
            <label>Ingredient Name</label>
            <input type="text" name="ingredient_name[]" required>
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" 
                   name="ingredient_quantity[]" 
                   step="0.01" 
                   min="0.01" 
                   required 
                   oninput="validateMinimumValue(this, 'Quantity')"
            >
        </div>
        <div class="form-group">
            <label>Unit</label>
            <select name="ingredient_unit[]" required>
                <option value="">Select Unit</option>
                <option value="kg">Kilograms</option>
                <option value="g">Grams</option>
                <option value="l">Liters</option>
                <option value="ml">Milliliters</option>
                <option value="pcs">Pieces</option>
            </select>
        </div>
        <button type="button" class="remove-ingredient" onclick="removeIngredient(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newRow);
}

function removeIngredient(button) {
    const row = button.parentElement;
    if (document.querySelectorAll('.ingredient-row').length > 1) {
        row.remove();
    }
}

// Image Upload Section
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file && (file.type === 'image/jpeg' || file.type === 'image/png')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bread-image-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        alert('Please upload a valid image (JPEG or PNG).');
        event.target.value = '';
    }
}

// Add validation function for quantity
function validateQuantity(input) {
    const value = parseFloat(input.value);
    const minValue = 0.01;
    
    if (isNaN(value) || value < minValue) {
        input.setCustomValidity(`Quantity cannot be less than ${minValue}`);
        input.reportValidity();
        
        // Optional: Add visual feedback
        input.style.borderColor = 'red';
        
        // Create or update error message
        let errorMsg = input.parentElement.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('small');
            errorMsg.className = 'error-message';
            input.parentElement.appendChild(errorMsg);
        }
        errorMsg.textContent = `Quantity cannot be less than ${minValue}`;
        errorMsg.style.color = 'red';
    } else {
        input.setCustomValidity('');
        input.style.borderColor = '';
        
        // Remove error message if exists
        const errorMsg = input.parentElement.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
} 

function validateMinimumValue(input, fieldName) {
    const minValue = 0.01;
    const value = parseFloat(input.value);
    
    if (value < minValue) {
        input.setCustomValidity(`${fieldName} cannot be less than ${minValue}`);
    } else {
        input.setCustomValidity('');
    }
} 

// Add validation function for batch size
function validateBatchSize(input) {
    const value = parseFloat(input.value);
    const minValue = 0.01;
    
    if (isNaN(value) || value < minValue) {
        input.setCustomValidity(`Batch size cannot be less than ${minValue}`);
        input.reportValidity();
        
        // Add visual feedback
        input.style.borderColor = 'red';
        
        // Create or update error message
        let errorMsg = input.parentElement.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('small');
            errorMsg.className = 'error-message';
            input.parentElement.appendChild(errorMsg);
        }
        errorMsg.textContent = `Batch size cannot be less than ${minValue}`;
        errorMsg.style.color = 'red';
    } else {
        input.setCustomValidity('');
        input.style.borderColor = '';
        
        // Remove error message if exists
        const errorMsg = input.parentElement.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
} 
