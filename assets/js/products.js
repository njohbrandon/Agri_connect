// Product Favorites Management
class FavoritesManager {
    constructor() {
        this.favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        this.updateFavoritesCount();
    }

    toggleFavorite(productId, productName) {
        const index = this.favorites.findIndex(f => f.id === productId);
        
        if (index === -1) {
            this.favorites.push({
                id: productId,
                name: productName,
                dateAdded: new Date().toISOString()
            });
            this.showToast(`${productName} added to favorites`);
        } else {
            this.favorites.splice(index, 1);
            this.showToast(`${productName} removed from favorites`);
        }

        localStorage.setItem('favorites', JSON.stringify(this.favorites));
        this.updateFavoritesCount();
        this.updateFavoriteButtons();
    }

    isFavorite(productId) {
        return this.favorites.some(f => f.id === productId);
    }

    updateFavoritesCount() {
        const count = this.favorites.length;
        const badge = document.getElementById('favorites-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    }

    updateFavoriteButtons() {
        document.querySelectorAll('[data-favorite-id]').forEach(btn => {
            const productId = parseInt(btn.dataset.favoriteId);
            const isFavorite = this.isFavorite(productId);
            
            btn.innerHTML = `<i class="bi bi-heart${isFavorite ? '-fill' : ''}"></i>`;
            btn.classList.toggle('btn-outline-success', !isFavorite);
            btn.classList.toggle('btn-success', isFavorite);
        });
    }

    showToast(message) {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }

        const toastHtml = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const toastElement = new DOMParser().parseFromString(toastHtml, 'text/html').body.firstChild;
        document.getElementById('toast-container').appendChild(toastElement);
        
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}

// Product Comparison Management
class ComparisonManager {
    constructor() {
        this.comparisonList = JSON.parse(localStorage.getItem('comparison')) || [];
        this.maxItems = 4;
    }

    toggleComparison(productId, productData) {
        const index = this.comparisonList.findIndex(p => p.id === productId);
        
        if (index === -1) {
            if (this.comparisonList.length >= this.maxItems) {
                this.showToast('Maximum 4 products can be compared at once', 'warning');
                return false;
            }
            this.comparisonList.push(productData);
            this.showToast(`${productData.name} added to comparison`);
        } else {
            this.comparisonList.splice(index, 1);
            this.showToast(`${productData.name} removed from comparison`);
        }

        localStorage.setItem('comparison', JSON.stringify(this.comparisonList));
        this.updateComparisonCount();
        this.updateComparisonButtons();
        return true;
    }

    isInComparison(productId) {
        return this.comparisonList.some(p => p.id === productId);
    }

    updateComparisonCount() {
        const count = this.comparisonList.length;
        const badge = document.getElementById('comparison-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }

        const compareBtn = document.getElementById('compare-products-btn');
        if (compareBtn) {
            compareBtn.disabled = count < 2;
        }
    }

    updateComparisonButtons() {
        document.querySelectorAll('[data-compare-id]').forEach(btn => {
            const productId = parseInt(btn.dataset.compareId);
            const isComparing = this.isInComparison(productId);
            
            btn.innerHTML = `<i class="bi bi-${isComparing ? 'check-square' : 'square'}"></i>`;
            btn.classList.toggle('btn-outline-success', !isComparing);
            btn.classList.toggle('btn-success', isComparing);
        });
    }

    showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }

        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const toastElement = new DOMParser().parseFromString(toastHtml, 'text/html').body.firstChild;
        document.getElementById('toast-container').appendChild(toastElement);
        
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    openComparisonModal() {
        if (this.comparisonList.length < 2) {
            this.showToast('Select at least 2 products to compare', 'warning');
            return;
        }

        const modalHtml = `
            <div class="modal fade" id="comparisonModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Product Comparison</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Feature</th>
                                            ${this.comparisonList.map(product => `
                                                <th class="text-center">
                                                    <img src="${product.image || 'assets/images/default-product.jpg'}" 
                                                         class="img-fluid mb-2" style="max-height: 100px; object-fit: cover;">
                                                    <div>${product.name}</div>
                                                </th>
                                            `).join('')}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Price</td>
                                            ${this.comparisonList.map(product => `
                                                <td class="text-center">${product.price} XAF/${product.unit}</td>
                                            `).join('')}
                                        </tr>
                                        <tr>
                                            <td>Category</td>
                                            ${this.comparisonList.map(product => `
                                                <td class="text-center">${product.category}</td>
                                            `).join('')}
                                        </tr>
                                        <tr>
                                            <td>Quantity Available</td>
                                            ${this.comparisonList.map(product => `
                                                <td class="text-center">${product.quantity} ${product.unit}</td>
                                            `).join('')}
                                        </tr>
                                        <tr>
                                            <td>Location</td>
                                            ${this.comparisonList.map(product => `
                                                <td class="text-center">${product.location}</td>
                                            `).join('')}
                                        </tr>
                                        <tr>
                                            <td>Seller</td>
                                            ${this.comparisonList.map(product => `
                                                <td class="text-center">${product.farmer_name}</td>
                                            `).join('')}
                                        </tr>
                                        <tr>
                                            <td>Action</td>
                                            ${this.comparisonList.map(product => `
                                                <td class="text-center">
                                                    <a href="product_details.php?id=${product.id}" 
                                                       class="btn btn-success btn-sm">View Details</a>
                                                </td>
                                            `).join('')}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const modalElement = new DOMParser().parseFromString(modalHtml, 'text/html').body.firstChild;
        document.body.appendChild(modalElement);
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
        });
    }
}

// Initialize managers when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.favoritesManager = new FavoritesManager();
    window.comparisonManager = new ComparisonManager();

    // Update UI elements
    favoritesManager.updateFavoriteButtons();
    comparisonManager.updateComparisonButtons();
}); 