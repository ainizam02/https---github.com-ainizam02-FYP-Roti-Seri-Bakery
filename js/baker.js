document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const bakerCards = document.querySelectorAll('.baker-card');
            
            bakerCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const email = card.querySelector('.contact-info p:nth-child(1)').textContent.toLowerCase();
                const contact = card.querySelector('.contact-info p:nth-child(2)').textContent.toLowerCase();
                const address = card.querySelector('.contact-info p:nth-child(3)').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || 
                    email.includes(searchTerm) || 
                    contact.includes(searchTerm) ||
                    address.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

function deleteBaker(userId) {
    if (confirm('Are you sure you want to delete this baker? This action cannot be undone.')) {
        // Add delete functionality here
        console.log('Deleting baker with ID:', userId);
    }
} 