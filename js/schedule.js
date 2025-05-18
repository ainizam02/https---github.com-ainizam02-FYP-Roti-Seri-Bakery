function deleteSchedule(scheduleId) {
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        fetch('delete_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                schedule_id: scheduleId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                const row = document.querySelector(`tr[data-schedule-id="${scheduleId}"]`);
                if (row) {
                    row.remove();
                    
                    // Check if there are any schedules left
                    const remainingRows = document.querySelectorAll('.schedule-table tbody tr');
                    if (remainingRows.length === 0) {
                        // Show "no schedules" message
                        const tableBody = document.querySelector('.schedule-table tbody');
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="no-records">No schedules found</td>
                            </tr>
                        `;
                    }
                }
                alert('Schedule deleted successfully');
            } else {
                alert('Error deleting schedule: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting schedule');
        });
    }
}

function checkEquipmentAvailability() {
    const date = document.getElementById('schedule_date').value;

    if (date) {
        fetch(`add_schedule.php?date_equipment=${date}`)
            .then(response => response.json())
            .then(data => {
                const equipmentContainer = document.getElementById('equipment-selection');
                equipmentContainer.innerHTML = ''; // Clear existing content

                data.forEach(equipment => {
                    const equipmentLabel = document.createElement('label');
                    equipmentLabel.classList.add('equipment-checkbox');

                    const equipmentCheckbox = equipment.availability_status === 'Available'
                        ? `<input type="checkbox" name="equipment[]" value="${equipment.equipment_id}">`
                        : '';

                    const equipmentStatus = `
                        <span class="status ${equipment.availability_status.toLowerCase()}">
                            ${equipment.availability_status}
                        </span>
                    `;

                    equipmentLabel.innerHTML = `
                        ${equipmentCheckbox}
                        ${equipment.equipment_name} ${equipmentStatus}
                    `;

                    equipmentContainer.appendChild(equipmentLabel);
                });
            })
            .catch(err => {
                console.error('Error fetching equipment availability:', err);
            });
    }
}


function checkUserAvailability() {
    const date = document.getElementById('schedule_date').value;

    if (date) {
        fetch(`add_schedule.php?date=${date}`)
            .then(response => response.json())
            .then(data => {
                const userContainer = document.getElementById('user-availability');
                userContainer.innerHTML = '';

                data.forEach(user => {
                    const userLabel = document.createElement('label');
                    userLabel.classList.add('user-checkbox');

                    const userCheckbox = user.availability_status === 'Available'
                        ? `<input type="checkbox" name="assigned_users[]" value="${user.user_id}">`
                        : '';

                    const userStatus = `
                        <span class="status ${user.availability_status.toLowerCase()}">
                            ${user.availability_status}
                        </span>
                    `;

                    userLabel.innerHTML = `
                        ${userCheckbox}
                        ${user.user_fullName} ${userStatus}
                    `;

                    userContainer.appendChild(userLabel);
                });
            })
            .catch(err => {
                console.error('Error fetching user availability:', err);
            });
    }
}

document.getElementById('schedule_date').addEventListener('change', () => {
    checkEquipmentAvailability();
    checkUserAvailability();
});

document.addEventListener('DOMContentLoaded', function() {
    const calculateBtn = document.getElementById('calculateBtn');
    const recipeSelect = document.getElementById('recipe_id');
    const orderVolume = document.getElementById('schedule_orderVolumn');

    // Initially disable the calculate button
    calculateBtn.disabled = true;
    calculateBtn.style.opacity = '0.5';
    calculateBtn.style.cursor = 'not-allowed';

    // Enable/disable calculate button based on recipe selection
    recipeSelect.addEventListener('change', function() {
        if (this.value) {
            calculateBtn.disabled = false;
            calculateBtn.style.opacity = '1';
            calculateBtn.style.cursor = 'pointer';
        } else {
            calculateBtn.disabled = true;
            calculateBtn.style.opacity = '0.5';
            calculateBtn.style.cursor = 'not-allowed';
        }
    });

    function calculateBatchAndQuantity() {
        const orderVolumeValue = orderVolume.value;
        const selectedOption = recipeSelect.options[recipeSelect.selectedIndex];

        // Clear previous calculation info
        document.getElementById('calculation-info').innerHTML = '';
        document.getElementById('batch-calculation').innerHTML = '';
        document.getElementById('quantity-calculation').innerHTML = '';

        // Check if recipe is selected
        if (!recipeSelect.value) {
            alert('Please select a recipe first.');
            return;
        }

        // Check if order volume is filled
        if (!orderVolumeValue || orderVolumeValue <= 0) {
            alert('Please enter a valid order volume (must be greater than 0).');
            orderVolume.focus();
            return;
        }

        try {
            const batchSize = parseFloat(selectedOption.getAttribute('data-batch-size'));
            const recipeName = selectedOption.text;

            // Calculate number of batches (rounded up)
            const rawBatches = orderVolumeValue / batchSize;
            const numBatches = Math.ceil(rawBatches);

            // Calculate actual quantity to produce
            const quantity = numBatches * batchSize;

            // Update the form fields
            document.getElementById('schedule_batchNum').value = numBatches;
            document.getElementById('quantity').value = quantity;

            // Show calculation details
            document.getElementById('calculation-info').innerHTML =
                `Selected recipe: ${recipeName} (Batch size: ${batchSize} units)`;

            document.getElementById('batch-calculation').innerHTML =
                `${orderVolumeValue} units ÷ ${batchSize} units per batch = ${rawBatches.toFixed(2)} → Rounded up to ${numBatches} batches`;

            document.getElementById('quantity-calculation').innerHTML =
                `${numBatches} batches × ${batchSize} units per batch = ${quantity} units total`;

        } catch (error) {
            console.error('Calculation error:', error);
            alert('An error occurred during calculation. Please check your inputs and try again.');
        }
    }

    // Add event listeners
    calculateBtn.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent form submission
        calculateBatchAndQuantity();
    });

    // Also calculate when Enter is pressed in the order volume field
    orderVolume.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            calculateBatchAndQuantity();
        }
    });
});



