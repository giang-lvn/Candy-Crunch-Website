const stars = document.querySelectorAll(".star");
const starRating = document.querySelector(".star-rating");
const skuID = document.getElementById("skuID").value;

let currentRating = 0;

stars.forEach((star) => {
    star.addEventListener("click", () => {
        currentRating = star.dataset.value;
        starRating.dataset.rating = currentRating;
        updateStars(currentRating);
    });

    star.addEventListener("mouseenter", () => {
        updateStars(star.dataset.value);
    });
});

starRating.addEventListener("mouseleave", () => {
    updateStars(currentRating);
});

function updateStars(rating) {
    stars.forEach((star) => {
        if (star.dataset.value <= rating) {
            star.classList.add("active");
            } 
        else {
            star.classList.remove("active");
        }
    });
}

// ===== OPEN POPUP =====
function openRatingPopup() {
    const overlay = document.getElementById("rating-overlay");
    if (overlay) {
        overlay.classList.remove("hidden");
        document.body.style.overflow = "hidden"; // khóa scroll nền
    }
}

// ===== CLOSE POPUP =====
function closeRatingPopup() {
    const overlay = document.getElementById("rating-overlay");
    if (overlay) {
        overlay.classList.add("hidden");
        document.body.style.overflow = ""; // mở lại scroll
    }
}

// ===== CLOSE BUTTON (X) =====
document.addEventListener("click", function (e) {
    if (e.target.id === "cancelPopupClose") {
        closeRatingPopup();
    }
});

// ===== CLICK RA NGOÀI POPUP ĐỂ ĐÓNG =====
document.getElementById("rating-overlay").addEventListener("click", function (e) {
    if (e.target.id === "rating-overlay") {
        closeRatingPopup();
    }
});

// ===== SUBMIT RATING =====
document.querySelector(".btn-primary-medium").addEventListener("click", function() {
    const rating = currentRating;
    const comment = document.querySelector(".input-field input").value.trim();
    const skuID = "SKU001"; // TODO: Lấy từ data attribute hoặc hidden input

    // Validate
    if (rating === 0) {
        alert("Please select a rating!");
        return;
    }

    // Gửi AJAX
    fetch('../../controllers/website/RatingController.php?action=submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `sku_id=${skuID}&rating=${rating}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeRatingPopup();
            // Reset form
            currentRating = 0;
            updateStars(0);
            document.querySelector(".input-field input").value = "";
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

