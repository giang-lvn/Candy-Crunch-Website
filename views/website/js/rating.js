// ===== RATING POPUP FOR ORDER DETAIL PAGE =====

document.addEventListener('DOMContentLoaded', function () {
    // ===== STAR RATING FUNCTIONALITY =====
    const stars = document.querySelectorAll('.star-rating .star');
    const starRating = document.querySelector('.star-rating');
    let currentRating = 0;

    if (stars.length > 0 && starRating) {
        stars.forEach((star) => {
            // Click to select rating
            star.addEventListener('click', () => {
                currentRating = parseInt(star.dataset.value);
                starRating.dataset.rating = currentRating;
                updateStars(currentRating);
            });

            // Hover effect
            star.addEventListener('mouseenter', () => {
                updateStars(parseInt(star.dataset.value));
            });
        });

        // Reset to selected rating when mouse leaves
        starRating.addEventListener('mouseleave', () => {
            updateStars(currentRating);
        });
    }

    function updateStars(rating) {
        stars.forEach((star) => {
            if (parseInt(star.dataset.value) <= rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    // ===== OPEN POPUP =====
    const writeReviewBtn = document.querySelector('.btn-write-review');
    if (writeReviewBtn) {
        writeReviewBtn.addEventListener('click', function () {
            openRatingPopup();
        });
    }

    function openRatingPopup() {
        const overlay = document.getElementById('rating-overlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Reset form
            currentRating = 0;
            updateStars(0);
            if (starRating) {
                starRating.dataset.rating = 0;
            }
            const reviewText = document.getElementById('rating-review-text');
            if (reviewText) {
                reviewText.value = '';
            }
        }
    }

    // ===== CLOSE POPUP =====
    function closeRatingPopup() {
        const overlay = document.getElementById('rating-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Close button (X)
    const closeBtn = document.getElementById('closeRatingPopup');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            closeRatingPopup();
        });
    }

    // Click outside popup to close
    const ratingOverlay = document.getElementById('rating-overlay');
    if (ratingOverlay) {
        ratingOverlay.addEventListener('click', function (e) {
            if (e.target.id === 'rating-overlay') {
                closeRatingPopup();
            }
        });
    }

    // ===== SUBMIT RATING =====
    const submitBtn = document.getElementById('submitRating');
    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            const rating = currentRating;
            const reviewText = document.getElementById('rating-review-text');
            const comment = reviewText ? reviewText.value.trim() : '';
            const productSelect = document.getElementById('rating-product-select');
            const skuID = productSelect ? productSelect.value : '';

            // Validate
            if (rating === 0) {
                alert('Please select a rating!');
                return;
            }

            if (!skuID) {
                alert('Please select a product!');
                return;
            }

            // Disable button during submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            // Send AJAX request
            fetch('/Candy-Crunch-Website/index.php?controller=rating&action=submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `sku_id=${encodeURIComponent(skuID)}&rating=${rating}&comment=${encodeURIComponent(comment)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closeRatingPopup();
                        // Reset form
                        currentRating = 0;
                        updateStars(0);
                        if (reviewText) {
                            reviewText.value = '';
                        }
                    } else {
                        alert(data.message || 'Failed to submit review. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit';
                });
        });
    }
});
