/** @format */

// ==========================================
// KAFE LATTE - CART PAGE ENHANCED JAVASCRIPT
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
  // ==========================================
  // QUANTITY CHANGE WITH ANIMATION
  // ==========================================
  window.changeQuantity = function (btn, change) {
    const form = btn.closest(".quantity-form");
    const input = form.querySelector(".qty-input");
    const currentValue = parseInt(input.value);
    const newValue = currentValue + change;
    const max = parseInt(input.max);

    if (newValue >= 1 && newValue <= max) {
      // Animate the input
      input.style.transform = "scale(1.2)";
      input.style.color = "var(--accent-color)";

      setTimeout(() => {
        input.value = newValue;
        input.style.transform = "scale(1)";
        input.style.color = "var(--primary-color)";
      }, 150);

      // Show loading state
      showLoadingOnButton(btn);

      // Submit form with delay for animation
      setTimeout(() => {
        form.submit();
      }, 300);
    } else {
      // Shake animation if limit reached
      input.style.animation = "shake 0.5s ease";
      setTimeout(() => {
        input.style.animation = "";
      }, 500);
    }
  };

  // Add shake animation
  const style = document.createElement("style");
  style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
  document.head.appendChild(style);

  // ==========================================
  // LOADING STATE FOR BUTTONS
  // ==========================================
  function showLoadingOnButton(btn) {
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    btn.style.pointerEvents = "none";
  }

  // ==========================================
  // REMOVE ITEM WITH ANIMATION
  // ==========================================
  const removeForms = document.querySelectorAll(".item-remove form");
  removeForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const cartItem = this.closest(".cart-item");
      const productName =
        cartItem.querySelector(".item-details h3").textContent;

      // Custom confirm dialog
      showConfirmDialog(
        "Hapus Item",
        `Yakin ingin menghapus "${productName}" dari keranjang?`,
        () => {
          // Animate removal
          cartItem.style.transition = "all 0.4s ease";
          cartItem.style.opacity = "0";
          cartItem.style.transform = "translateX(-100%)";

          setTimeout(() => {
            form.submit();
          }, 400);
        }
      );
    });
  });

  // ==========================================
  // CLEAR CART WITH ANIMATION
  // ==========================================
  const clearCartForm = document.querySelector(".cart-actions form");
  if (clearCartForm) {
    clearCartForm.addEventListener("submit", function (e) {
      e.preventDefault();

      showConfirmDialog(
        "Kosongkan Keranjang",
        "Yakin ingin mengosongkan semua item dari keranjang?",
        () => {
          // Animate all cart items
          const cartItems = document.querySelectorAll(".cart-item");
          cartItems.forEach((item, index) => {
            setTimeout(() => {
              item.style.transition = "all 0.4s ease";
              item.style.opacity = "0";
              item.style.transform = "scale(0.8)";
            }, index * 100);
          });

          setTimeout(() => {
            this.submit();
          }, cartItems.length * 100 + 400);
        }
      );
    });
  }

  // ==========================================
  // CUSTOM CONFIRM DIALOG
  // ==========================================
  function showConfirmDialog(title, message, onConfirm) {
    // Create overlay
    const overlay = document.createElement("div");
    overlay.className = "confirm-overlay";
    overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;

    // Create dialog
    const dialog = document.createElement("div");
    dialog.className = "confirm-dialog";
    dialog.style.cssText = `
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            transform: scale(0.8);
            transition: transform 0.3s ease;
        `;

    dialog.innerHTML = `
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #FFE5E5, #FFB3B3); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-exclamation-triangle" style="color: #F44336; font-size: 1.8rem;"></i>
                </div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">${title}</h3>
                <p style="color: var(--text-light); margin-bottom: 2rem; line-height: 1.6;">${message}</p>
                <div style="display: flex; gap: 1rem;">
                    <button class="dialog-btn cancel-btn" style="flex: 1; padding: 12px; border: 2px solid var(--primary-color); background: white; color: var(--primary-color); border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                        Batal
                    </button>
                    <button class="dialog-btn confirm-btn" style="flex: 1; padding: 12px; border: none; background: linear-gradient(135deg, #F44336, #E53935); color: white; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        `;

    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    // Animate in
    setTimeout(() => {
      overlay.style.opacity = "1";
      dialog.style.transform = "scale(1)";
    }, 10);

    // Button hover effects
    const buttons = dialog.querySelectorAll(".dialog-btn");
    buttons.forEach((btn) => {
      btn.addEventListener("mouseenter", function () {
        this.style.transform = "translateY(-2px)";
        if (this.classList.contains("confirm-btn")) {
          this.style.boxShadow = "0 6px 20px rgba(244, 67, 54, 0.4)";
        }
      });
      btn.addEventListener("mouseleave", function () {
        this.style.transform = "translateY(0)";
        if (this.classList.contains("confirm-btn")) {
          this.style.boxShadow = "0 4px 15px rgba(244, 67, 54, 0.3)";
        }
      });
    });

    // Cancel button
    dialog.querySelector(".cancel-btn").addEventListener("click", () => {
      closeDialog(overlay, dialog);
    });

    // Confirm button
    dialog.querySelector(".confirm-btn").addEventListener("click", () => {
      closeDialog(overlay, dialog);
      onConfirm();
    });

    // Close on overlay click
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        closeDialog(overlay, dialog);
      }
    });

    // Close on ESC key
    const escHandler = (e) => {
      if (e.key === "Escape") {
        closeDialog(overlay, dialog);
        document.removeEventListener("keydown", escHandler);
      }
    };
    document.addEventListener("keydown", escHandler);
  }

  function closeDialog(overlay, dialog) {
    overlay.style.opacity = "0";
    dialog.style.transform = "scale(0.8)";
    setTimeout(() => {
      overlay.remove();
    }, 300);
  }

  // ==========================================
  // CART SUMMARY STICKY ANIMATION
  // ==========================================
  const cartSummary = document.querySelector(".cart-summary");
  if (cartSummary) {
    window.addEventListener("scroll", () => {
      const scrollPosition = window.pageYOffset;

      if (scrollPosition > 200) {
        cartSummary.style.boxShadow = "0 15px 50px rgba(139, 69, 19, 0.2)";
      } else {
        cartSummary.style.boxShadow = "0 5px 20px rgba(139, 69, 19, 0.1)";
      }
    });
  }

  // ==========================================
  // HOVER EFFECTS FOR CART ITEMS
  // ==========================================
  const cartItems = document.querySelectorAll(".cart-item");
  cartItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      this.style.background = "white";
    });

    item.addEventListener("mouseleave", function () {
      this.style.background = "var(--bg-light)";
    });
  });

  // ==========================================
  // ANIMATE CHECKOUT BUTTON
  // ==========================================
  const checkoutBtn = document.querySelector(".btn-checkout");
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", function (e) {
      if (this.href && this.href.includes("checkout.php")) {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        this.style.pointerEvents = "none";
      }
    });
  }

  // ==========================================
  // QUANTITY INPUT VALIDATION
  // ==========================================
  const qtyInputs = document.querySelectorAll(".qty-input");
  qtyInputs.forEach((input) => {
    input.addEventListener("input", function () {
      const value = parseInt(this.value);
      const max = parseInt(this.max);
      const min = parseInt(this.min);

      if (value > max) {
        this.value = max;
        showToast("Maksimal pembelian: " + max + " item", "warning");
      } else if (value < min) {
        this.value = min;
      }
    });

    // Prevent manual typing of invalid characters
    input.addEventListener("keypress", function (e) {
      const char = String.fromCharCode(e.which);
      if (!/[0-9]/.test(char)) {
        e.preventDefault();
      }
    });
  });

  // ==========================================
  // TOAST NOTIFICATION
  // ==========================================
  function showToast(message, type = "info") {
    const toast = document.createElement("div");
    toast.className = "toast-notification";

    const colors = {
      success: "#4CAF50",
      error: "#F44336",
      warning: "#FF9800",
      info: "#2196F3",
    };

    toast.style.cssText = `
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            color: ${colors[type] || colors.info};
            padding: 1rem 2rem;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            font-weight: 600;
            transition: bottom 0.3s ease;
            border-left: 4px solid ${colors[type] || colors.info};
        `;

    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.bottom = "30px";
    }, 100);

    setTimeout(() => {
      toast.style.bottom = "-100px";
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // ==========================================
  // ANIMATE NUMBERS ON LOAD
  // ==========================================
  function animateNumber(element) {
    const text = element.textContent;
    const number = parseInt(text.replace(/\D/g, ""));

    if (!isNaN(number) && number > 0) {
      let current = 0;
      const increment = number / 50;
      const timer = setInterval(() => {
        current += increment;
        if (current >= number) {
          element.textContent = text;
          clearInterval(timer);
        } else {
          element.textContent = text.replace(
            number,
            Math.floor(current).toLocaleString("id-ID")
          );
        }
      }, 20);
    }
  }

  // Animate prices on load
  const prices = document.querySelectorAll(
    ".subtotal-price, .summary-row span:last-child"
  );
  prices.forEach((price) => {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateNumber(entry.target);
          observer.unobserve(entry.target);
        }
      });
    });
    observer.observe(price);
  });

  // ==========================================
  // MOBILE MENU TOGGLE
  // ==========================================
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");

  if (hamburger) {
    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("active");

      const spans = hamburger.querySelectorAll("span");
      if (navMenu.classList.contains("active")) {
        spans[0].style.transform = "rotate(45deg) translate(5px, 5px)";
        spans[1].style.opacity = "0";
        spans[2].style.transform = "rotate(-45deg) translate(7px, -6px)";
      } else {
        spans[0].style.transform = "";
        spans[1].style.opacity = "1";
        spans[2].style.transform = "";
      }
    });
  }

  // ==========================================
  // SCROLL TO TOP ON PAGE LOAD
  // ==========================================
  window.scrollTo({ top: 0, behavior: "smooth" });

  // ==========================================
  // LOG INITIALIZATION
  // ==========================================
  console.log("🛒 Cart Page Initialized Successfully!");
});
