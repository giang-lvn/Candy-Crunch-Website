document.getElementById("cancelOrderBtn").addEventListener("click", function () {
    openCancelOrderPopup();
  });
  

// Mở popup
function openCancelOrderPopup() {
    document
      .getElementById("cancel-order-overlay")
      .classList.remove("hidden");
  }
  
// Đóng popup
function closeCancelOrderPopup() {
    document
      .getElementById("cancel-order-overlay")
      .classList.add("hidden");
}
  
  // Gắn sự kiện sau khi popup load
  document.addEventListener("click", function (e) {
    if (e.target.id === "cancelPopupClose") {
      closeCancelOrderPopup();
    }
  
    if (e.target.id === "cancel-order-overlay") {
      closeCancelOrderPopup();
    }
  
    if (e.target.id === "submitCancelOrder") {
      const reason = document.getElementById("cancelReason").value;
  
      if (!reason) {
        alert("Please select a reason to cancel your order.");
        return;
      }
  
      // TODO: Gửi request lên backend
      console.log("Cancel reason:", reason);
  
      alert("Your cancel request has been submitted.");
      closeCancelOrderPopup();
    }
  });

