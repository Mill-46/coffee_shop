/** @format */

// Slider functionality
let currentSlideIndex = 0;
const slides = document.querySelectorAll(".slide");
const dots = document.querySelectorAll(".dot");

function showSlide(index) {
  slides.forEach((slide) => slide.classList.remove("active"));
  dots.forEach((dot) => dot.classList.remove("active"));

  if (index >= slides.length) currentSlideIndex = 0;
  if (index < 0) currentSlideIndex = slides.length - 1;

  slides[currentSlideIndex].classList.add("active");
  dots[currentSlideIndex].classList.add("active");
}

function currentSlide(index) {
  currentSlideIndex = index;
  showSlide(currentSlideIndex);
}

function nextSlide() {
  currentSlideIndex++;
  showSlide(currentSlideIndex);
}

// Auto slide every 5 seconds
setInterval(nextSlide, 5000);

// Shopping Cart functionality
let cart = [];

function loadCart() {
  const savedCart = localStorage.getItem("cart");
  if (savedCart) {
    cart = JSON.parse(savedCart);
    updateCartCount();
  }
}

function saveCart() {
  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();
}

function updateCartCount() {
  const cartCountElement = document.getElementById("cartCount");
  if (cartCountElement) {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCountElement.textContent = totalItems;
  }
}

function addToCart(productId, productName, price) {
  const existingItem = cart.find((item) => item.productId === productId);

  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({
      productId: productId,
      name: productName,
      price: price,
      quantity: 1,
    });
  }

  saveCart();
  showNotification(`${productName} added to cart!`, "success");
}

function removeFromCart(productId) {
  cart = cart.filter((item) => item.productId !== productId);
  saveCart();
  if (typeof displayCart === "function") {
    displayCart();
  }
}

function updateQuantity(productId, change) {
  const item = cart.find((item) => item.productId === productId);
  if (item) {
    item.quantity += change;
    if (item.quantity <= 0) {
      removeFromCart(productId);
    } else {
      saveCart();
      if (typeof displayCart === "function") {
        displayCart();
      }
    }
  }
}

function clearCart() {
  cart = [];
  saveCart();
  if (typeof displayCart === "function") {
    displayCart();
  }
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 1rem 2rem;
        background: ${
          type === "success"
            ? "#28a745"
            : type === "error"
            ? "#dc3545"
            : "#17a2b8"
        };
        color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease-out";
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Form validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function validatePassword(password) {
  return password.length >= 6;
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Load cart on page load
document.addEventListener("DOMContentLoaded", loadCart);

// Add CSS animations
const style = document.createElement("style");
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
