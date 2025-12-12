function formatMoney(amount, currency) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(amount);
}

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#proposal-items');
    const grandTotalEl = document.querySelector('#grand-total');

    if (!tableBody) return; // Not on proposal form page

    // Initialize with existing items if available, otherwise one empty row
    if (window.existingItems && window.existingItems.length > 0) {
        window.existingItems.forEach(item => addRow(item));
        calculateTotal();
    } else if (tableBody.children.length === 0) {
        addRow();
    }

    document.querySelector('#add-row-btn').addEventListener('click', () => addRow());

    // Event delegation for inputs
    tableBody.addEventListener('change', (e) => {
        if (e.target.matches('.product-select')) {
            renderAllRowOptions(); // Re-render first to ensure constraints
            calculateRow(e.target.closest('tr'));
            calculateTotal();
        } else if (e.target.matches('.qty-input')) {
            calculateRow(e.target.closest('tr'));
            calculateTotal();
        }
    });

    tableBody.addEventListener('click', (e) => {
        if (e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
            calculateTotal();
            renderAllRowOptions();
        }
    });

    // Initial update
    renderAllRowOptions();
});

function addRow(data = null) {
    const tableBody = document.querySelector('#proposal-items');

    // Initial population (full list) to establish selection
    let options = '<option value="">Ürün Seçin</option>';
    window.productsData.forEach(p => {
        const type = p.type ? `[${p.type}] ` : '';
        const selected = (data && data.product_id === p.id) ? 'selected' : '';
        options += `<option value="${p.id}" data-price="${p.price}" data-currency="${p.currency}" ${selected}>${type}${p.name} (${p.price} ${p.currency})</option>`;
    });

    const quantity = data ? data.quantity : 1;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td style="width: 40%">
            <select name="items[product_id][]" class="form-control product-select" required>
                ${options}
            </select>
            <input type="hidden" name="items[product_name][]" class="product-name-input">
            <input type="hidden" name="items[price][]" class="price-input">
            <input type="hidden" name="items[currency][]" class="currency-input">
        </td>
        <td style="width: 20%">
            <input type="number" name="items[quantity][]" class="form-control qty-input" min="1" value="${quantity}" required>
        </td>
        <td style="width: 30%; vertical-align: middle;">
            <span class="line-total">0.00</span> <span class="line-currency"></span>
            <input type="hidden" name="items[total][]" class="total-input">
        </td>
        <td style="width: 10%; text-align: right;">
            <button type="button" class="btn btn-danger remove-row" style="padding: 0.5rem;">X</button>
        </td>
    `;
    tableBody.appendChild(tr);

    // Calculate row based on initial data
    calculateRow(tr);

    // Clean up options (remove duplicates from others)
    renderAllRowOptions();
}

// Completely rebuilds options for all selects to strictly enforce "hide selected"
function renderAllRowOptions() {
    const selects = document.querySelectorAll('.product-select');
    // Capture current selections
    const currentSelections = Array.from(selects).map(s => s.value);
    const globallySelected = currentSelections.filter(v => v);

    selects.forEach((select, index) => {
        const myValue = currentSelections[index];
        let html = '<option value="">Ürün Seçin</option>';

        window.productsData.forEach(p => {
            // Include if it's the currently selected item for THIS row, OR if it's not selected anywhere else
            const isSelectedHere = (p.id == myValue);
            const isSelectedElsewhere = globallySelected.includes(p.id) && !isSelectedHere;

            if (!isSelectedElsewhere) {
                const type = p.type ? `[${p.type}] ` : '';
                const selectedAttr = isSelectedHere ? 'selected' : '';
                html += `<option value="${p.id}" data-price="${p.price}" data-currency="${p.currency}" ${selectedAttr}>${type}${p.name} (${p.price} ${p.currency})</option>`;
            }
        });

        // Only update DOM if necessary to prevent focus loss issues (though usually fine on 'change')
        if (select.innerHTML !== html) {
            select.innerHTML = html;
            select.value = myValue; // Restore value ensuring sync
        }
    });
}

function calculateRow(tr) {
    const select = tr.querySelector('.product-select');
    const qtyInput = tr.querySelector('.qty-input');
    const selectedOption = select.options[select.selectedIndex];

    if (!selectedOption.value) return;

    const price = parseFloat(selectedOption.dataset.price);
    const currency = selectedOption.dataset.currency;
    const qty = parseFloat(qtyInput.value) || 0;
    const total = price * qty;

    // Update hidden inputs
    // data-name attribute or innerText parsing? Previously used split.
    // Ideally we should have data-name on option, but let's stick to previous working logic regarding text parsing
    // But be careful if text contains parentheses other than price.
    // The previous logic: tr.querySelector('.product-name-input').value = selectedOption.text.split(' (')[0];
    tr.querySelector('.product-name-input').value = selectedOption.text.replace(/\s\([^)]+\)$/, '');
    tr.querySelector('.price-input').value = price;
    tr.querySelector('.currency-input').value = currency;
    tr.querySelector('.total-input').value = total;

    // Update UI
    tr.querySelector('.line-total').textContent = total.toFixed(2);
    tr.querySelector('.line-currency').textContent = currency;
}

function calculateTotal() {
    let totals = {};

    document.querySelectorAll('#proposal-items tr').forEach(tr => {
        const currency = tr.querySelector('.currency-input').value;
        const total = parseFloat(tr.querySelector('.total-input').value) || 0;

        if (currency) {
            if (!totals[currency]) totals[currency] = 0;
            totals[currency] += total;
        }
    });

    const grandTotalEl = document.querySelector('#grand-total');
    let totalText = '';
    for (const [curr, amount] of Object.entries(totals)) {
        totalText += `${amount.toFixed(2)} ${curr} + `;
    }

    // Remove last ' + '
    grandTotalEl.textContent = totalText.slice(0, -3) || '0.00';
}
