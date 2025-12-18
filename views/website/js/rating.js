const stars = document.querySelectorAll(".star");
const starRating = document.querySelector(".star-rating");

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
