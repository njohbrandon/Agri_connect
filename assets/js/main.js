// Main JavaScript file for Agri-Connect

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Load featured products on homepage
    if (document.getElementById('featured-products')) {
        loadFeaturedProducts();
    }

    // Initialize form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});

// Function to load featured products
function loadFeaturedProducts() {
    fetch('includes/get_featured_products.php')
        .then(response => response.json())
        .then(products => {
            const container = document.getElementById('featured-products');
            products.forEach(product => {
                container.appendChild(createProductCard(product));
            });
        })
        .catch(error => console.error('Error loading featured products:', error));
}

// Function to create product card
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'col-md-4 mb-4';
    card.innerHTML = `
        <div class="product-card">
            <img src="${product.image || 'assets/images/default-product.jpg'}" 
                 alt="${product.name}" 
                 class="card-img-top">
            <div class="card-body">
                <h5 class="card-title">${product.name}</h5>
                <p class="card-text">${product.description}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h5 mb-0">$${product.price}/${product.unit}</span>
                    <a href="product-details.php?id=${product.id}" 
                       class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
    `;
    return card;
}

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
} 