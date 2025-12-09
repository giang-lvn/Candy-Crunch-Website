//Tạo hiệu ứng vòng lặp cho phần timeline
document.addEventListener("DOMContentLoaded", () => {
    const wrapper = document.querySelector('.timeline-wrapper');
    const scroller = document.querySelector('.timeline-scroll');

    if (!scroller) return;

    // nhân đôi nội dung để tạo loop mượt
    scroller.innerHTML = scroller.innerHTML + scroller.innerHTML;

    // speed (px per frame), chỉnh nhỏ cho mượt: 0.4 ~ 1.2 tuỳ mong muốn
    let speed = 0.6;
    let paused = false;

    // hover để pause (tốt cho desktop)
    wrapper.addEventListener('mouseenter', () => paused = true);
    wrapper.addEventListener('mouseleave', () => paused = false);
    // thêm cho touch: khi người chạm, tạm pause
    scroller.addEventListener('touchstart', () => paused = true, {passive: true});
    scroller.addEventListener('touchend', () => paused = false);

    // đảm bảo scrollLeft ban đầu = 0
    scroller.scrollLeft = 0;

    function step() {
        if (!paused) {
            scroller.scrollLeft += speed;
            // Khi chạy đến 1 nửa (kích thước của nội dung gốc), reset về 0
            if (scroller.scrollLeft >= scroller.scrollWidth / 2) {
                // đặt về ngay lập tức (không animation) để seamless
                scroller.scrollLeft = 0;
            }
        }
        requestAnimationFrame(step);
    }

    // Start loop
    requestAnimationFrame(step);
});

//Tạo hiệu ứng đóng mở cho phần team
const cards = document.querySelectorAll(".team-card");

cards.forEach(card => {
    card.addEventListener("click", () => {

        // Nếu card đang mở → không làm gì (giữ nguyên 1 thẻ mở)
        if (card.classList.contains("open")) return;

        // Đóng tất cả thẻ
        cards.forEach(c => c.classList.remove("open"));

        // Mở thẻ được click
        card.classList.add("open");
    });
});
