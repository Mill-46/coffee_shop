/** @format */

// ==========================================
// KAFE LATTE - LOGIN PAGE ENHANCED JAVASCRIPT
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
  // ==========================================
  // PASSWORD TOGGLE ENHANCED
  // ==========================================
  window.togglePassword = function () {
    const passwordInput = document.getElementById("password");
    const toggleBtn = document.querySelector(".toggle-password i");
    const toggleButton = document.querySelector(".toggle-password");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      toggleBtn.classList.remove("fa-eye");
      toggleBtn.classList.add("fa-eye-slash");
      toggleButton.style.color = "var(--secondary-color)";

      // Add animation
      toggleButton.style.transform = "scale(1.1)";
      setTimeout(() => {
        toggleButton.style.transform = "scale(1)";
      }, 200);
    } else {
      passwordInput.type = "password";
      toggleBtn.classList.remove("fa-eye-slash");
      toggleBtn.classList.add("fa-eye");
      toggleButton.style.color = "var(--text-light)";

      // Add animation
      toggleButton.style.transform = "scale(1.1)";
      setTimeout(() => {
        toggleButton.style.transform = "scale(1)";
      }, 200);
    }
  };

  // ==========================================
  // FORM VALIDATION
  // ==========================================
  const form = document.querySelector(".auth-form");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  // Email validation
  if (emailInput) {
    emailInput.addEventListener("input", function () {
      validateEmail(this);
    });

    emailInput.addEventListener("blur", function () {
      validateEmail(this);
    });
  }

  // Password validation
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      validatePassword(this);
    });

    passwordInput.addEventListener("blur", function () {
      validatePassword(this);
    });
  }

  function validateEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Remove previous validation messages
    removeValidationMessage(input);

    if (email === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    if (emailRegex.test(email)) {
      input.classList.remove("invalid");
      input.classList.add("valid");
      showValidationMessage(input, "Email valid", "success");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Format email tidak valid", "error");
    }
  }

  function validatePassword(input) {
    const password = input.value;

    // Remove previous validation messages
    removeValidationMessage(input);

    if (password === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    if (password.length >= 6) {
      input.classList.remove("invalid");
      input.classList.add("valid");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Password minimal 6 karakter", "error");
    }
  }

  function showValidationMessage(input, message, type) {
    const formGroup = input.closest(".form-group");
    const messageDiv = document.createElement("div");
    messageDiv.className =
      type === "success" ? "success-message" : "error-message";

    const icon =
      type === "success"
        ? '<i class="fas fa-check-circle"></i>'
        : '<i class="fas fa-exclamation-circle"></i>';

    messageDiv.innerHTML = `${icon} <span>${message}</span>`;
    formGroup.appendChild(messageDiv);
  }

  function removeValidationMessage(input) {
    const formGroup = input.closest(".form-group");
    const existingMessage = formGroup.querySelector(
      ".error-message, .success-message"
    );
    if (existingMessage) {
      existingMessage.remove();
    }
  }

  // ==========================================
  // FORM SUBMIT WITH LOADING STATE
  // ==========================================
  if (form) {
    form.addEventListener("submit", function (e) {
      const submitBtn = form.querySelector(".btn-submit");
      const email = emailInput.value.trim();
      const password = passwordInput.value;

      // Basic validation
      if (email === "" || password === "") {
        e.preventDefault();
        showToast("Mohon isi semua field", "error");

        if (email === "") emailInput.focus();
        else if (password === "") passwordInput.focus();

        return false;
      }

      // Email format validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        showToast("Format email tidak valid", "error");
        emailInput.focus();
        return false;
      }

      // Show loading state
      submitBtn.classList.add("loading");
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Memproses...';
      submitBtn.disabled = true;

      // Form will submit naturally if validation passes
    });
  }

  // ==========================================
  // INPUT ANIMATIONS
  // ==========================================
  const inputs = document.querySelectorAll(".form-group input");

  inputs.forEach((input) => {
    // Focus animation
    input.addEventListener("focus", function () {
      const label = this.closest(".form-group").querySelector("label");
      if (label) {
        label.style.color = "var(--secondary-color)";
        label.style.transform = "translateY(-2px)";
      }
    });

    // Blur animation
    input.addEventListener("blur", function () {
      const label = this.closest(".form-group").querySelector("label");
      if (label) {
        label.style.color = "var(--primary-color)";
        label.style.transform = "translateY(0)";
      }
    });

    // Typing animation
    input.addEventListener("input", function () {
      this.style.transform = "scale(1.01)";
      setTimeout(() => {
        this.style.transform = "scale(1)";
      }, 100);
    });
  });

  // ==========================================
  // ALERT AUTO DISMISS
  // ==========================================
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    // Add close button
    const closeBtn = document.createElement("button");
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.cssText = `
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            font-size: 1.2rem;
            padding: 0.5rem;
        `;
    alert.style.position = "relative";
    alert.appendChild(closeBtn);

    closeBtn.addEventListener("mouseenter", function () {
      this.style.opacity = "1";
    });

    closeBtn.addEventListener("mouseleave", function () {
      this.style.opacity = "0.7";
    });

    closeBtn.addEventListener("click", function () {
      alert.style.animation = "slideOutRight 0.5s ease-out";
      setTimeout(() => {
        alert.remove();
      }, 500);
    });

    // Auto dismiss after 5 seconds
    setTimeout(() => {
      if (alert.parentElement) {
        alert.style.animation = "slideOutRight 0.5s ease-out";
        setTimeout(() => {
          alert.remove();
        }, 500);
      }
    }, 5000);
  });

  // Add slideOutRight animation
  const style = document.createElement("style");
  style.textContent = `
        @keyframes slideOutRight {
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
    `;
  document.head.appendChild(style);

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

    const icons = {
      success: "fa-check-circle",
      error: "fa-exclamation-circle",
      warning: "fa-exclamation-triangle",
      info: "fa-info-circle",
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
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 90%;
        `;

    toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.bottom = "30px";
    }, 100);

    setTimeout(() => {
      toast.style.bottom = "-100px";
      setTimeout(() => toast.remove(), 300);
    }, 3000);

    // Click to dismiss
    toast.addEventListener("click", () => {
      toast.style.bottom = "-100px";
      setTimeout(() => toast.remove(), 300);
    });
  }

  window.showToast = showToast;

  // ==========================================
  // KEYBOARD SHORTCUTS
  // ==========================================
  document.addEventListener("keydown", function (e) {
    // Enter key to submit when inputs are focused
    if (
      e.key === "Enter" &&
      (e.target === emailInput || e.target === passwordInput)
    ) {
      form.dispatchEvent(new Event("submit", { cancelable: true }));
    }
  });

  // ==========================================
  // PREVENT DOUBLE SUBMIT
  // ==========================================
  let isSubmitting = false;

  if (form) {
    form.addEventListener("submit", function (e) {
      if (isSubmitting) {
        e.preventDefault();
        return false;
      }
      isSubmitting = true;
    });
  }

  // ==========================================
  // AUTO-FOCUS FIRST INPUT
  // ==========================================
  if (emailInput && emailInput.value === "") {
    setTimeout(() => {
      emailInput.focus();
    }, 500);
  }

  // ==========================================
  // REMEMBER ME FUNCTIONALITY (Optional)
  // ==========================================
  const rememberCheckbox = document.getElementById("remember");
  if (rememberCheckbox) {
    // Load remembered email
    const rememberedEmail = localStorage.getItem("rememberedEmail");
    if (rememberedEmail && emailInput) {
      emailInput.value = rememberedEmail;
      rememberCheckbox.checked = true;
    }

    // Save email on form submit
    if (form) {
      form.addEventListener("submit", function () {
        if (rememberCheckbox.checked) {
          localStorage.setItem("rememberedEmail", emailInput.value);
        } else {
          localStorage.removeItem("rememberedEmail");
        }
      });
    }
  }

  // ==========================================
  // LOGO ANIMATION
  // ==========================================
  const logoIcon = document.querySelector(".logo-auth i");
  if (logoIcon) {
    let rotationDegree = 0;
    setInterval(() => {
      rotationDegree += 1;
      if (rotationDegree >= 360) rotationDegree = 0;
    }, 50);
  }

  // ==========================================
  // PARALLAX EFFECT ON MOUSE MOVE
  // ==========================================
  const authContainer = document.querySelector(".auth-container");

  if (window.innerWidth > 1024) {
    document.addEventListener("mousemove", function (e) {
      const x = e.clientX / window.innerWidth - 0.5;
      const y = e.clientY / window.innerHeight - 0.5;

      authContainer.style.transform = `
                perspective(1000px) 
                rotateY(${x * 2}deg) 
                rotateX(${-y * 2}deg)
            `;
    });

    authContainer.addEventListener("mouseenter", function () {
      this.style.transition = "transform 0.3s ease";
    });

    authContainer.addEventListener("mouseleave", function () {
      this.style.transition = "transform 0.5s ease";
      this.style.transform = "perspective(1000px) rotateY(0deg) rotateX(0deg)";
    });
  }

  // ==========================================
  // SMOOTH PAGE ENTRANCE
  // ==========================================
  document.body.style.opacity = "0";
  window.addEventListener("load", function () {
    document.body.style.transition = "opacity 0.5s ease";
    document.body.style.opacity = "1";
  });

  // Fallback if images don't load
  setTimeout(() => {
    document.body.style.opacity = "1";
  }, 1000);

});
